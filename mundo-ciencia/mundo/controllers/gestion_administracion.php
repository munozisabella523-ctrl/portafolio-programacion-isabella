<?php
/*
 * ARCHIVO: controllers/gestion_administracion.php
 * PARA QUÉ SIRVE: Procesa cambios de cuentas y protege los permisos y al último superadministrador activo.
 * DÓNDE SE UTILIZA: Guardar cuenta y Desactivar, en Administración > Usuarios.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';
// Exige una petición POST con el token de la sesión. Se ejecuta antes de procesar los formularios.
validar_csrf();
// Prepara $admin con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
$admin = exigir_administrador();
// Lee la acción enviada para decidir qué operación debe ejecutarse.
$accion = $_POST['action'] ?? $_GET['action'] ?? '';
// Agrupa operaciones que pueden fallar para poder informar del problema y recuperar el estado.
try {
    // Inicia una transacción: los cambios de este bloque se confirman juntos.
    base_datos()->begin_transaction();
    // Obtiene el identificador del registro sobre el que se trabajará.
    $id = (int) ($_POST['id'] ?? 0);
    // Bloquea las cuentas privilegiadas durante el cambio para conservar un último superadministrador activo.
    // Consulta usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
    filas("SELECT id FROM usuarios WHERE rol='super_administrador' ORDER BY id FOR UPDATE");
    // Consulta usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
    $actual = $id ? fila('SELECT * FROM usuarios WHERE id=? FOR UPDATE', [$id]) : null;
    // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
    if ($id && !$actual)denegar(404);
    // Reserva esta decisión a los permisos de superadministrador.
    if (!es_super_admin($admin) && ($actual && $actual['rol'] !== 'estudiante'))denegar();
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($accion === 'eliminar_usuario') {
        // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
        if (!$actual || $id === (int) $admin['id'])denegar();
        // La desactivación conserva publicaciones, calificaciones e historial.
        // Determina el rol que se utiliza al gestionar la cuenta.
        $rol = $actual['rol'];
        // Determina el estado que se guardará o se mostrará en esta operación.
        $estado = 'inactivo';
    } elseif ($accion === 'guardar_usuario') {
        // Determina el rol que se utiliza al gestionar la cuenta.
        $rol = opcion('rol', ROLES);
        // Determina el estado que se guardará o se mostrará en esta operación.
        $estado = opcion('estado', ['activo', 'inactivo']);
        // Reserva esta decisión a los permisos de superadministrador.
        if (!es_super_admin($admin) && $rol !== 'estudiante')denegar();
        // Recoge el nombre que se mostrará en la cuenta o contenido.
        $nombre = texto('nombre', 120);
        // Recoge el nombre de usuario de la cuenta.
        $usuario = texto('usuario', 60);
        // Recoge el correo de la cuenta para validarlo o guardarlo.
        $correo = texto('correo', 120);
        // Rechaza datos que no cumplen la validación y prepara un aviso para el usuario.
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL))throw new DomainException('El correo no es válido.');
    } else denegar(400);
    // Selecciona el contenido o la operación que corresponde al rol de esta cuenta.
    if ($actual && $actual['rol'] === 'super_administrador' && ($rol !== 'super_administrador' || $estado !== 'activo')) {
        // Rechaza datos que no cumplen la validación y prepara un aviso para el usuario.
        if ($id === (int) $admin['id'] || (int) valor("SELECT COUNT(*) FROM usuarios WHERE rol='super_administrador' AND estado='activo'") <= 1)throw new DomainException('No puedes desactivar o degradar al último super administrador ni tu propia cuenta.');
    }
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($accion === 'eliminar_usuario')consulta("UPDATE usuarios SET estado='inactivo' WHERE id=?", [$id]);
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    elseif ($id)consulta('UPDATE usuarios SET nombre=?,usuario=?,correo=?,rol=?,estado=? WHERE id=?', [$nombre, $usuario, $correo, $rol, $estado, $id]);
    else {
        // Prepara $pass con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
        $pass = texto('contrasena', 200);
        // Rechaza datos que no cumplen la validación y prepara un aviso para el usuario.
        if (!validar_contrasena($pass))throw new DomainException('La contraseña debe tener mínimo 8 caracteres.');
        // Genera el hash de la contraseña; no guarda la contraseña original como texto.
        consulta('INSERT INTO usuarios(nombre,usuario,correo,contrasena,rol,estado) VALUES(?,?,?,?,?,?)', [$nombre, $usuario, $correo, password_hash($pass, PASSWORD_DEFAULT), $rol, $estado]);
        // Obtiene el identificador del registro sobre el que se trabajará.
        $id = base_datos()->insert_id;
    }
    // Guarda acciones administrativas y la cuenta afectada, cuando corresponde. Se consultan en el historial administrativo.
    registrar_actividad_administrador((int) $admin['id'], 'Cuenta: ' . $rol . ' / ' . $estado, $id);
    // Confirma en la base de datos los cambios que han terminado correctamente.
    base_datos()->commit();
    // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
    mensaje_temporal('success', 'Cuenta actualizada.');
} catch (DomainException $e) {
    // Revierte los cambios de la transacción si la operación no pudo completarse.
    base_datos()->rollback();
    // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
    mensaje_temporal('error', $e->getMessage());
} catch (Throwable $e) {
    // Revierte los cambios de la transacción si la operación no pudo completarse.
    base_datos()->rollback();
    // Registra el error internamente para revisar problemas sin mostrar detalles técnicos al visitante.
    error_log((string) $e);
    // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
    mensaje_temporal('error','No se pudo guardar. Comprueba que el correo y usuario sean únicos.');
}
// Construye una dirección interna y redirige hacia ella. Se usa al terminar formularios o comprobar permisos.
ir('administracion/gestion_usuarios.php');
