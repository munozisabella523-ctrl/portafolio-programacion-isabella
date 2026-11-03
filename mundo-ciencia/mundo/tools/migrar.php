<?php
/*
 * ARCHIVO: tools/migrar.php
 * PARA QUÉ SIRVE: Ejecuta la migración de la base desde la terminal.
 * DÓNDE SE UTILIZA: No se muestra en la web: herramienta de instalación y mantenimiento.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Distingue la ejecución desde terminal de una petición web.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
// Carga las funciones o la conexión compartida que necesita este archivo.
require __DIR__ . '/../config/base_datos.php';
// Prepara $db con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
$db = base_datos();
// Prepara $sql con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
$sql = file_get_contents(__DIR__ . '/../migracion_mundo_ciencia_v2.sql');
$db->multi_query($sql);
do {
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($r = $db->store_result())$r->free();
} while ($db->more_results() && $db->next_result());
// Escribe el resultado o cierra el fragmento HTML que se está construyendo.
echo "Migración v2 terminada. Cuentas y datos conservados.\n";
