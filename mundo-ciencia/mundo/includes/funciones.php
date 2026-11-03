<?php
/*
 * ARCHIVO: includes/funciones.php
 * PARA QUÉ SIRVE: Reúne sesión, mensajes, escape de texto e historial; carga las funciones de plataforma.
 * DÓNDE SE UTILIZA: Trabajo interno compartido: acceso, avisos y contenido seguro en todas las pantallas.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Exige respetar los tipos declarados en los argumentos; es una regla interna de PHP.
declare(strict_types=1);

// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../config/base_datos.php';
// Distingue la ejecución desde terminal de una petición web.
if (PHP_SAPI !== 'cli') {
    ini_set('display_errors', '0');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');
    set_exception_handler(function (Throwable $e): void {
        // Registra el error internamente para revisar problemas sin mostrar detalles técnicos al visitante.
        error_log((string) $e);
        http_response_code(503);
        // Genera HTML para este componente: título principal. Los datos dinámicos se incorporan en esta expresión.
        echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Mundo Ciencia</title><h1>Estamos preparando el laboratorio</h1><p>No se pudo conectar con la plataforma. Comprueba que MySQL esté iniciado y que la migración v2 se haya ejecutado.</p></html>';
    });
}


// Evita iniciar dos veces la misma sesión.
if (session_status() === PHP_SESSION_NONE) {
    // Configura la cookie que el navegador utiliza para la sesión.
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    // Abre la sesión que permite reconocer la cuenta entre páginas.
    session_start();
}

// Escapa caracteres especiales antes de mostrar texto en HTML. Protege títulos, nombres, descripciones y atributos.
function e($value): string
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Construye una ruta del disco dentro del proyecto. Se usa para imágenes y archivos internos; no es una dirección del navegador.
function ruta_base(string $path = ''): string
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return __DIR__ . '/../' . ltrim($path, '/');
}

// Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
function redireccionar(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
function mensaje_temporal(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Recupera y borra el aviso pendiente. El encabezado lo presenta encima del contenido.
function obtener_mensaje_temporal(): ?array
{
    // Prepara $flash con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $flash;
}

// Consulta la cuenta de la sesión y comprueba que siga activa. Determina el menú y los permisos de la página.
function usuario_actual(): ?array
{
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if (empty($_SESSION['user_id'])) {
        // Devuelve el resultado a la página o función que solicitó esta operación.
        return null;
    }

    // Consulta usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
    $stmt = base_datos()->prepare('SELECT id, nombre, usuario, correo, rol, estado, fecha_registro, ultimo_acceso, avatar FROM usuarios WHERE id = ? LIMIT 1');
    // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
    $stmt->bind_param('i', $_SESSION['user_id']);
    // Ejecuta la consulta preparada con sus valores asociados.
    $stmt->execute();
    // Recupera un registro de la consulta como datos identificados por nombre de columna.
    $user = $stmt->get_result()->fetch_assoc();

    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if (!$user || $user['estado'] !== 'activo') {
        // Prepara $_SESSION con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
        $_SESSION = [];
        // Elimina la sesión activa para finalizar el acceso de esta cuenta.
        session_destroy();
        // Devuelve el resultado a la página o función que solicitó esta operación.
        return null;
    }

    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $user;
}

// Conserva una función de compatibilidad vacía; el esquema se actualiza con la herramienta de migración.
function asegurar_esquema_experimentos(): void
{
} // Esquema gestionado por migración CLI.
// Reutiliza la comprobación de acceso. Protege las pantallas que requieren iniciar sesión.
function exigir_sesion(): array
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return requiere_login();
}

// Permite continuar solo a administradores y superadministradores. Se usa en Administración.
function exigir_administrador(): array
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return requiere_rol('administrador', 'super_administrador');
}

// Obtiene la dirección IP que informa el servidor. Se registra en el historial y se utiliza al limitar intentos de acceso.
function ip_cliente(): string
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

// Obtiene una descripción limitada del navegador para los registros de actividad.
function navegador_cliente(): string
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 255);
}

// Guarda acciones de una cuenta. Se consultan en Mi panel e Historial.
function registrar_actividad_usuario(int $userId, string $action): void
{
    // Añade información a historial_usuarios. Acciones realizadas por las cuentas. Se consultan en Historial y Mi actividad.
    $stmt = base_datos()->prepare('INSERT INTO historial_usuarios (usuario_id, accion, ip, navegador) VALUES (?, ?, ?, ?)');
    // Prepara $ip con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $ip = ip_cliente();
    // Prepara $agent con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $agent = navegador_cliente();
    // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
    $stmt->bind_param('isss', $userId, $action, $ip, $agent);
    // Ejecuta la consulta preparada con sus valores asociados.
    $stmt->execute();
}

// Guarda acciones administrativas y la cuenta afectada, cuando corresponde. Se consultan en el historial administrativo.
function registrar_actividad_administrador(int $adminId, string $action, ?int $affectedUserId = null): void
{
    // Añade información a historial_admin. Acciones de administración y cuentas afectadas. Se consultan en el historial administrativo.
    $stmt = base_datos()->prepare('INSERT INTO historial_admin (admin_id, accion, usuario_afectado_id, ip) VALUES (?, ?, ?, ?)');
    // Prepara $ip con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $ip = ip_cliente();
    // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
    $stmt->bind_param('isis', $adminId, $action, $affectedUserId, $ip);
    // Ejecuta la consulta preparada con sus valores asociados.
    $stmt->execute();
}

// Registra cambios realizados sobre un experimento. Alimenta el historial de proyectos.
function registrar_actividad_experimento(int $userId, string $action, ?int $experimentId): void
{
    // Conserva una función de compatibilidad vacía; el esquema se actualiza con la herramienta de migración.
    asegurar_esquema_experimentos();
    // Añade información a historial_experimentos. Acciones realizadas sobre proyectos. Se consultan en el historial de experimentos.
    $stmt = base_datos()->prepare('INSERT INTO historial_experimentos (usuario_id, experimento_id, experimento_referencia_id, accion, ip) VALUES (?, ?, ?, ?, ?)');
    // Prepara $ip con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $ip = ip_cliente();
    // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
    $stmt->bind_param('iiiss', $userId, $experimentId, $experimentId, $action, $ip);
    // Ejecuta la consulta preparada con sus valores asociados.
    $stmt->execute();
}

// Comprueba la longitud mínima de la contraseña antes de registrar o actualizar una cuenta.
function validar_contrasena(string $password): bool
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return strlen($password) >= 8;
}

// Devuelve una clase CSS si la página coincide con la opción del menú. Es una ayuda de presentación.
function clase_activa(string $page, string $current): string
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $page === $current ? ' class="activo"' : '';
}

// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/plataforma.php';
