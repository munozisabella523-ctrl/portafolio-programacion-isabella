<?php
/*
 * ARCHIVO: tools/promover_super_admin.php
 * PARA QUÉ SIRVE: Promueve una cuenta existente cuando todavía no hay superadministrador activo.
 * DÓNDE SE UTILIZA: No se muestra en la web: herramienta de terminal para la primera configuración.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Distingue la ejecución desde terminal de una petición web.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
// Carga las funciones o la conexión compartida que necesita este archivo.
require __DIR__ . '/../includes/funciones.php';
// Recoge el correo de la cuenta para validarlo o guardarlo.
$correo = $argv[1] ?? '';
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Uso: php tools/promover_super_admin.php correo-de-cuenta-existente\n");
    exit(1);
}
// Inicia una transacción: los cambios de este bloque se confirman juntos.
base_datos()->begin_transaction();
// Consulta usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
filas("SELECT id FROM usuarios WHERE rol='super_administrador' FOR UPDATE");
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if (valor("SELECT COUNT(*) FROM usuarios WHERE rol='super_administrador' AND estado='activo'")) {
    fwrite(STDERR, "Ya hay un super administrador. Usa la gestión de usuarios.\n");
    exit(1);
}
// Consulta usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
$u = fila('SELECT id FROM usuarios WHERE correo=? FOR UPDATE', [$correo]);
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if (!$u) {
    // Genera el hash de la contraseña; no guarda la contraseña original como texto.
    fwrite(STDERR, "Primero registra la cuenta. Su contraseña se almacena con password_hash().\n");
    exit(1);
}
// Actualiza usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
consulta("UPDATE usuarios SET rol='super_administrador',estado='activo' WHERE id=?", [$u['id']]);
// Guarda acciones administrativas y la cuenta afectada, cuando corresponde. Se consultan en el historial administrativo.
registrar_actividad_administrador((int) $u['id'], 'Promoción inicial por CLI', (int) $u['id']);
// Confirma en la base de datos los cambios que han terminado correctamente.
base_datos()->commit();
// Escribe el resultado o cierra el fragmento HTML que se está construyendo.
echo "Cuenta promovida. No se modificó su contraseña.\n";
