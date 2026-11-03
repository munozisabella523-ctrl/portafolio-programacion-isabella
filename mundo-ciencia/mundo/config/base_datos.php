<?php
/*
 * ARCHIVO: config/base_datos.php
 * PARA QUÉ SIRVE: Abre y reutiliza la conexión MySQL con codificación UTF-8.
 * DÓNDE SE UTILIZA: Trabajo interno de todas las páginas que consultan o guardan información.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Exige respetar los tipos declarados en los argumentos; es una regla interna de PHP.
declare(strict_types=1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// Abre una única conexión a ciencia, usando variables de entorno si existen, y activa utf8mb4. La usan todas las consultas.
function base_datos(): mysqli
{
    static $conexion;
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if (!$conexion) {
        // Prepara $conexion con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
        $conexion = new mysqli(getenv('MC_DB_HOST') ?: '127.0.0.1', getenv('MC_DB_USER') ?: 'root', getenv('MC_DB_PASS') ?: '', getenv('MC_DB_NAME') ?: 'ciencia', (int) (getenv('MC_DB_PORT') ?: 3306));
        // Configura UTF-8 para conservar tildes y caracteres especiales en la base.
        $conexion->set_charset('utf8mb4');
    }
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $conexion;
}
