<?php
/* ARCHIVO: includes/chat_historial.php
 * FUNCIONES DE HISTORIAL Y PERMISOS
 * chat_filtro_panel: conversaciones que el rol puede consultar en su panel.
 * chat_identidad y chat_filtro_propio: propietario del chat que se está utilizando.
 * chat_crear y chat_guardar: persistencia en las dos tablas chat_*.
 */
require_once __DIR__ . '/funciones.php';

// Se aplica tanto a los listados del panel como al detalle: no basta ocultar enlaces.
function chat_filtro_panel(array $usuario): array
{
    if ($usuario['rol'] === 'super_administrador') return ['1=1', []];
    if ($usuario['rol'] === 'administrador') {
        return ["(c.usuario_id=? OR (c.rol_origen NOT IN ('administrador','super_administrador') AND (u.rol IS NULL OR u.rol NOT IN ('administrador','super_administrador'))))", [$usuario['id']]];
    }
    return ['c.usuario_id=?', [$usuario['id']]];
}

// Usa la cuenta iniciada o una cookie aleatoria; la base guarda el hash del visitante.
function chat_identidad(): array
{
    $usuario = usuario_actual();
    if ($usuario) return ['usuario' => $usuario, 'hash' => null, 'clave' => 'usuario:' . $usuario['id']];
    $token = $_COOKIE['mc_chat_visitante'] ?? '';
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)) {
        $token = bin2hex(random_bytes(32));
        setcookie('mc_chat_visitante', $token, ['expires' => time() + 31536000, 'path' => url(), 'httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
    }
    $hash = hash('sha256', $token);
    return ['usuario' => null, 'hash' => $hash, 'clave' => 'visitante:' . $hash];
}

// La ventana del chat permite escribir solo en conversaciones propias, para cualquier rol.
function chat_filtro_propio(array $identidad): array
{
    return $identidad['usuario']
        ? ['usuario_id=?', [$identidad['usuario']['id']]]
        : ["usuario_id IS NULL AND rol_origen='visitante' AND visitante_hash=?", [$identidad['hash']]];
}

// Crea una conversación vacía y devuelve el identificador asignado por MySQL.
function chat_crear(array $identidad): int
{
    consulta('INSERT INTO chat_conversaciones(usuario_id,visitante_hash,rol_origen) VALUES(?,?,?)', [$identidad['usuario']['id'] ?? null, $identidad['hash'], $identidad['usuario']['rol'] ?? 'visitante']);
    return (int) base_datos()->insert_id;
}

// Añade un mensaje y actualiza la fecha usada para ordenar las conversaciones del panel.
function chat_guardar(int $conversacionId, string $rol, string $contenido): void
{
    consulta('INSERT INTO chat_mensajes(conversacion_id,rol,contenido) VALUES(?,?,?)', [$conversacionId, $rol, $contenido]);
    consulta('UPDATE chat_conversaciones SET fecha_actualizacion=NOW() WHERE id=?', [$conversacionId]);
}

