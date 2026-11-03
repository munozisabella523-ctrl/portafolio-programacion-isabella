<?php
/* ARCHIVO: controllers/chat.php
 * API DEL ASISTENTE
 * 1. Identificar al visitante o usuario y delimitar sus conversaciones.
 * 2. GET: recuperar historial. POST: validar identidad, token CSRF y mensaje.
 * 3. Guardar la pregunta, consultar Ollama y guardar la respuesta.
 * 4. Liberar el bloqueo y devolver JSON al navegador.
 * CSRF es un token que comprueba que la petición pertenece a la sesión.
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/chat_historial.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
// RESPUESTA HTTP: envía JSON con su código de estado y termina la petición.
function chat_json(array $datosRespuesta, int $estado = 200): never {
    http_response_code($estado);
    echo json_encode($datosRespuesta, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
$identidad = chat_identidad();
[$filtro, $parametros] = chat_filtro_propio($identidad);
$metodo = $_SERVER['REQUEST_METHOD'];
// LECTURA: comprueba propiedad antes de devolver los mensajes de una conversación.
if ($metodo === 'GET') {
    $conversaciones = filas("SELECT id,titulo FROM chat_conversaciones WHERE $filtro ORDER BY id DESC", $parametros);
    $conversacionId = (int) ($_GET['id'] ?? ($conversaciones[0]['id'] ?? 0));
    if ($conversacionId && !fila("SELECT id FROM chat_conversaciones WHERE id=? AND $filtro", [$conversacionId, ...$parametros])) chat_json(['error' => 'Conversación no disponible.'], 404);
    chat_json(['csrf' => csrf(), 'identidad' => $identidad['clave'], 'id' => $conversacionId, 'conversaciones' => $conversaciones, 'mensajes' => $conversacionId ? filas('SELECT rol,contenido FROM chat_mensajes WHERE conversacion_id=? ORDER BY id', [$conversacionId]) : []]);
}
// ESCRITURA: valida método, tamaño, token de sesión y contenido recibido.
if ($metodo !== 'POST') chat_json(['error' => 'Método no permitido.'], 405);
if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 30000) chat_json(['error' => 'Mensaje demasiado largo.'], 413);
$entrada = json_decode(file_get_contents('php://input'), true);
if (!is_array($entrada) || !is_string($entrada['csrf'] ?? null) || !hash_equals(csrf(), $entrada['csrf']) || ($entrada['identidad'] ?? null) !== $identidad['clave']) chat_json(['error' => 'La sesión cambió. Vuelve a abrir el chat.'], 403);
if (($entrada['accion'] ?? '') === 'nueva') chat_json(['id' => chat_crear($identidad)]);
$texto = $entrada['texto'] ?? null;
if (!is_string($texto) || trim($texto) === '' || mb_strlen($texto) > 4000) chat_json(['error' => 'Escribe entre 1 y 4000 caracteres.'], 422);
$texto = trim($texto);
$conversacionId = (int) ($entrada['id'] ?? 0);
if ($conversacionId && !fila("SELECT id FROM chat_conversaciones WHERE id=? AND $filtro", [$conversacionId, ...$parametros])) chat_json(['error' => 'Conversación no disponible.'], 404);
// CONCURRENCIA: un bloqueo por propietario evita respuestas simultáneas mezcladas.
$candado = 'mc_chat_' . hash('sha256', $identidad['clave']);
if ((int) valor('SELECT GET_LOCK(?,0)', [$candado]) !== 1) chat_json(['error' => 'Espera a que termine la respuesta anterior.'], 409);
// Libera la sesión mientras Ollama trabaja; otras páginas pueden seguir cargando.
session_write_close();
ignore_user_abort(true);
$error = null;
try {
    if (!$conversacionId) $conversacionId = chat_crear($identidad);
    if (!(int) valor('SELECT COUNT(*) FROM chat_mensajes WHERE conversacion_id=?', [$conversacionId])) consulta('UPDATE chat_conversaciones SET titulo=? WHERE id=?', [mb_substr($texto, 0, 120), $conversacionId]);
    chat_guardar($conversacionId, 'user', $texto);
    // CONTEXTO: solo los últimos 24 mensajes van al modelo; el historial completo se conserva.
    $mensajes = array_reverse(filas("SELECT rol AS role,contenido AS content FROM chat_mensajes WHERE conversacion_id=? AND rol IN ('user','assistant') ORDER BY id DESC LIMIT 24", [$conversacionId]));
    array_unshift($mensajes, ['role' => 'system', 'content' => 'Eres el asistente educativo de Mundo Ciencia. Responde en español de forma clara y breve sobre ciencia, tecnología y el sitio.']);
    // MODELO LOCAL: prepara la solicitud y limita el tiempo de espera.
    $conexionOllama = curl_init(rtrim(getenv('MC_OLLAMA_URL') ?: 'http://127.0.0.1:11434/v1', '/') . '/chat/completions');
    curl_setopt_array($conexionOllama, [CURLOPT_POST => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POSTFIELDS => json_encode(['model' => getenv('MC_OLLAMA_MODEL') ?: 'llama3', 'messages' => $mensajes, 'max_tokens' => 800, 'stream' => false]), CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 90]);
    $respuesta = curl_exec($conexionOllama);
    $codigoHttp = curl_getinfo($conexionOllama, CURLINFO_HTTP_CODE);
    curl_close($conexionOllama);
    $datosRespuesta = is_string($respuesta) ? json_decode($respuesta, true) : null;
    $contenido = $datosRespuesta['choices'][0]['message']['content'] ?? null;
    // RESULTADO: guarda por separado una respuesta válida o un aviso del servicio.
    if ($codigoHttp !== 200 || !is_string($contenido) || trim($contenido) === '') {
        $error = 'El asistente no está disponible en este momento. Tu pregunta quedó guardada; puedes intentarlo más tarde.';
        chat_guardar($conversacionId, 'error', $error);
    } else {
        chat_guardar($conversacionId, 'assistant', $contenido);
    }
} catch (Throwable $excepcion) {
    error_log('Chat: ' . $excepcion->getMessage());
    $error = 'No se pudo completar la respuesta. Recarga el historial antes de volver a enviar.';
} finally {
    consulta('SELECT RELEASE_LOCK(?)', [$candado]);
}
chat_json(['id' => $conversacionId, 'error' => $error]);

