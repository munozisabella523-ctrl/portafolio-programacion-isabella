<?php
/*
 * ARCHIVO: controllers/gestion_sesion.php
 * PARA QUÉ SIRVE: Procesa registro, acceso, salida, cambios de perfil y de contraseña.
 * DÓNDE SE UTILIZA: Formularios de Iniciar sesión, Registrarme y Mi perfil.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Exige respetar los tipos declarados en los argumentos; es una regla interna de PHP.
declare(strict_types=1);

// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';

// Exige una petición POST con el token de la sesión. Se ejecuta antes de procesar los formularios.
validar_csrf();
// Lee la acción solicitada por el formulario de sesión.
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Agrupa operaciones que pueden fallar para poder informar del problema y recuperar el estado.
try {
    // Selecciona la operación solicitada por el formulario o la petición.
    match ($action) {
        'iniciar_sesion' => iniciar_sesion(),
        'registrar_usuario' => registrar_usuario(),
        'cerrar_sesion' => cerrar_sesion(),
        'actualizar_perfil' => actualizar_perfil(),
        'cambiar_contrasena' => cambiar_contrasena(),
        default => redireccionar('../inicio_sesion.php'),
    };
} catch (Throwable $exception) {
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach ($GLOBALS['archivos_nuevos'] ?? [] as $f)@unlink(ruta_base('uploads/' . $f));
    // Registra el error internamente para revisar problemas sin mostrar detalles técnicos al visitante.
    error_log((string) $exception);
    // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
    mensaje_temporal('error', $exception instanceof DomainException ? $exception->getMessage() : 'No se pudo guardar. Revisa los datos e inténtalo nuevamente.');
    // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
    redireccionar('../inicio_sesion.php');
}

// Valida correo y contraseña, limita intentos fallidos y crea la sesión. Después abre el panel del rol correspondiente.
function iniciar_sesion(): never
{
    // Recoge el correo de la cuenta para validarlo o guardarlo.
    $correo = trim($_POST['correo'] ?? '');
    // Lee la contraseña recibida; se valida sin mostrarla en pantalla.
    $password = $_POST['contrasena'] ?? '';

    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $password === '') {
        // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
        mensaje_temporal('error', 'Ingresa un correo valido y tu contrasena.');
        // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
        redireccionar('../inicio_sesion.php');
    }

    // Calcula el identificador que agrupa los intentos de acceso de este correo e IP.
    $clave = hash('sha256', strtolower($correo) . '|' . ip_cliente());
    // Elimina los registros seleccionados de intentos_login. Control temporal de intentos de acceso. Funciona internamente en Iniciar sesión.
    consulta('DELETE FROM intentos_login WHERE desde < NOW() - INTERVAL 15 MINUTE');
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ((int) valor('SELECT intentos FROM intentos_login WHERE clave=?', [$clave]) >= 10) {
        // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
        mensaje_temporal('error', 'Demasiados intentos. Espera 15 minutos antes de volver a intentarlo.');
        // Construye una dirección interna y redirige hacia ella. Se usa al terminar formularios o comprobar permisos.
        ir('inicio_sesion.php');
    }
    // Añade información a intentos_login. Control temporal de intentos de acceso. Funciona internamente en Iniciar sesión.
    consulta('INSERT INTO intentos_login(clave,intentos) VALUES(?,1) ON DUPLICATE KEY UPDATE intentos=intentos+1', [$clave]);
    // Consulta usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
    $stmt = base_datos()->prepare('SELECT id, nombre, correo, contrasena, rol, estado FROM usuarios WHERE correo = ? LIMIT 1');
    // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
    $stmt->bind_param('s', $correo);
    // Ejecuta la consulta preparada con sus valores asociados.
    $stmt->execute();
    // Recupera un registro de la consulta como datos identificados por nombre de columna.
    $user = $stmt->get_result()->fetch_assoc();

    // Comprueba la contraseña contra el hash guardado antes de permitir el cambio o el acceso.
    if (!$user || !password_verify($password, $user['contrasena'])) {
        // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
        mensaje_temporal('error', 'Correo o contrasena incorrectos.');
        // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
        redireccionar('../inicio_sesion.php');
    }

    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($user['estado'] !== 'activo') {
        // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
        mensaje_temporal('error', 'Tu usuario esta inactivo. Contacta al administrador.');
        // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
        redireccionar('../inicio_sesion.php');
    }

    // Elimina los registros seleccionados de intentos_login. Control temporal de intentos de acceso. Funciona internamente en Iniciar sesión.
    consulta('DELETE FROM intentos_login WHERE clave=?', [$clave]);
    // Renueva el identificador de sesión después de validar el acceso.
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_role'] = $user['rol'];

    // Prepara la fecha y hora del último acceso.
    $now = date('Y-m-d H:i:s');
    // Actualiza usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
    $update = base_datos()->prepare('UPDATE usuarios SET ultimo_acceso = ? WHERE id = ?');
    // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
    $update->bind_param('si', $now, $user['id']);
    // Ejecuta la consulta preparada con sus valores asociados.
    $update->execute();
    // Guarda acciones de una cuenta. Se consultan en Mi panel e Historial.
    registrar_actividad_usuario((int) $user['id'], 'Inicio de sesion');

    // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
    redireccionar(in_array($user['rol'], ['administrador', 'super_administrador'], true) ? '../administracion/panel_administrativo.php' : '../area_personal/panel_usuario.php');
}

// Valida los datos, evita usuarios o correos repetidos y guarda una cuenta con contraseña protegida mediante hash.
function registrar_usuario(): never
{
    // Recoge el nombre que se mostrará en la cuenta o contenido.
    $nombre = trim($_POST['nombre'] ?? '');
    // Recoge el nombre de usuario de la cuenta.
    $usuario = trim($_POST['usuario'] ?? '');
    // Recoge el correo de la cuenta para validarlo o guardarlo.
    $correo = trim($_POST['correo'] ?? '');
    // Lee la contraseña recibida; se valida sin mostrarla en pantalla.
    $password = $_POST['contrasena'] ?? '';
    // Lee la confirmación de contraseña para compararla con la nueva.
    $confirm = $_POST['confirmar_contrasena'] ?? '';

    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($nombre === '' || $usuario === '' || mb_strlen($nombre) > 120 || mb_strlen($usuario) > 60 || mb_strlen($correo) > 120 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
        mensaje_temporal('error', 'Completa nombre, usuario y correo valido.');
        // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
        redireccionar('../registro_usuario.php');
    }

    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($password !== $confirm || !validar_contrasena($password)) {
        // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
        mensaje_temporal('error', 'La contrasena debe coincidir y tener minimo 8 caracteres.');
        // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
        redireccionar('../registro_usuario.php');
    }

    // Consulta usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
    $stmt = base_datos()->prepare('SELECT id FROM usuarios WHERE usuario = ? OR correo = ? LIMIT 1');
    // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
    $stmt->bind_param('ss', $usuario, $correo);
    // Ejecuta la consulta preparada con sus valores asociados.
    $stmt->execute();

    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($stmt->get_result()->fetch_assoc()) {
        // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
        mensaje_temporal('error', 'El usuario o correo ya existe.');
        // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
        redireccionar('../registro_usuario.php');
    }

    // Genera el hash de la contraseña; no guarda la contraseña original como texto.
    $hash = password_hash($password, PASSWORD_DEFAULT);
    // Añade información a usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
    $insert = base_datos()->prepare('INSERT INTO usuarios (nombre, usuario, correo, contrasena) VALUES (?, ?, ?, ?)');
    // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
    $insert->bind_param('ssss', $nombre, $usuario, $correo, $hash);
    // Ejecuta la consulta preparada con sus valores asociados.
    $insert->execute();

    // Prepara $userId con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $userId = base_datos()->insert_id;
    // Guarda acciones de una cuenta. Se consultan en Mi panel e Historial.
    registrar_actividad_usuario((int) $userId, 'Registro');
    // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
    mensaje_temporal('success', 'Cuenta creada correctamente. Ya puedes iniciar sesion.');
    // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
    redireccionar('../inicio_sesion.php');
}

// Registra la salida y elimina la sesión y su cookie. Después vuelve al inicio público.
function cerrar_sesion(): never
{
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if (!empty($_SESSION['user_id'])) {
        // Guarda acciones de una cuenta. Se consultan en Mi panel e Historial.
        registrar_actividad_usuario((int) $_SESSION['user_id'], 'Cierre de sesion');
    }

    // Prepara $_SESSION con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $_SESSION = [];
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if (ini_get('session.use_cookies')) {
        // Prepara $params con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    // Elimina la sesión activa para finalizar el acceso de esta cuenta.
    session_destroy();
    // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
    redireccionar('../inicio.php');
}

// Valida nombre, usuario, correo y avatar, guarda los cambios y vuelve a Mi perfil.
function actualizar_perfil(): never
{
    // Prepara $user con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $user = exigir_sesion();
    // Recoge el nombre que se mostrará en la cuenta o contenido.
    $nombre = trim($_POST['nombre'] ?? '');
    // Recoge el nombre de usuario de la cuenta.
    $usuario = trim($_POST['usuario'] ?? '');
    // Recoge el correo de la cuenta para validarlo o guardarlo.
    $correo = trim($_POST['correo'] ?? '');

    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($nombre === '' || $usuario === '' || mb_strlen($nombre) > 120 || mb_strlen($usuario) > 60 || mb_strlen($correo) > 120 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
        mensaje_temporal('error', 'Completa todos los datos del perfil correctamente.');
        // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
        redireccionar('../area_personal/perfil_usuario.php');
    }

    // Consulta usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
    $exists = base_datos()->prepare('SELECT id FROM usuarios WHERE (usuario = ? OR correo = ?) AND id <> ? LIMIT 1');
    // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
    $exists->bind_param('ssi', $usuario, $correo, $user['id']);
    // Ejecuta la consulta preparada con sus valores asociados.
    $exists->execute();
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($exists->get_result()->fetch_assoc()) {
        // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
        mensaje_temporal('error', 'Ese usuario o correo ya esta en uso.');
        // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
        redireccionar('../area_personal/perfil_usuario.php');
    }

    // Recoge las imágenes validadas que se asociarán a este registro.
    $fotos = subir_imagenes('avatar', 'avatares', 1);
    // Actualiza usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
    $stmt = base_datos()->prepare('UPDATE usuarios SET nombre = ?, usuario = ?, correo = ? WHERE id = ?');
    // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
    $stmt->bind_param('sssi', $nombre, $usuario, $correo, $user['id']);
    // Ejecuta la consulta preparada con sus valores asociados.
    $stmt->execute();
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($fotos)consulta('UPDATE usuarios SET avatar=? WHERE id=?', [$fotos[0], $user['id']]);
    // Guarda acciones de una cuenta. Se consultan en Mi panel e Historial.
    registrar_actividad_usuario((int) $user['id'], 'Edicion de perfil');

    // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
    mensaje_temporal('success', 'Perfil actualizado.');
    // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
    redireccionar('../area_personal/perfil_usuario.php');
}

// Comprueba la contraseña actual y la confirmación de la nueva; guarda su hash y vuelve a Mi perfil.
function cambiar_contrasena(): never
{
    // Prepara $user con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $user = exigir_sesion();
    // Recoge el dato actual que debe comprobarse antes de modificarlo.
    $actual = $_POST['actual'] ?? '';
    // Prepara $nueva con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $nueva = $_POST['nueva'] ?? '';
    // Lee la confirmación de contraseña para compararla con la nueva.
    $confirm = $_POST['confirmar'] ?? '';

    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($nueva !== $confirm || !validar_contrasena($nueva)) {
        // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
        mensaje_temporal('error', 'La nueva contrasena debe coincidir y tener minimo 8 caracteres.');
        // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
        redireccionar('../area_personal/perfil_usuario.php');
    }

    // Consulta usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
    $stmt = base_datos()->prepare('SELECT contrasena FROM usuarios WHERE id = ? LIMIT 1');
    // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
    $stmt->bind_param('i', $user['id']);
    // Ejecuta la consulta preparada con sus valores asociados.
    $stmt->execute();
    // Recupera un registro de la consulta como datos identificados por nombre de columna.
    $row = $stmt->get_result()->fetch_assoc();

    // Comprueba la contraseña contra el hash guardado antes de permitir el cambio o el acceso.
    if (!$row || !password_verify($actual, $row['contrasena'])) {
        // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
        mensaje_temporal('error', 'La contrasena actual no es correcta.');
        // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
        redireccionar('../area_personal/perfil_usuario.php');
    }

    // Genera el hash de la contraseña; no guarda la contraseña original como texto.
    $hash = password_hash($nueva, PASSWORD_DEFAULT);
    // Actualiza usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
    $update = base_datos()->prepare('UPDATE usuarios SET contrasena = ? WHERE id = ?');
    // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
    $update->bind_param('si', $hash, $user['id']);
    // Ejecuta la consulta preparada con sus valores asociados.
    $update->execute();
    // Guarda acciones de una cuenta. Se consultan en Mi panel e Historial.
    registrar_actividad_usuario((int) $user['id'], 'Cambio de contrasena');

    // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
    mensaje_temporal('success', 'Contrasena actualizada.');
    // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
    redireccionar('../area_personal/perfil_usuario.php');
}
