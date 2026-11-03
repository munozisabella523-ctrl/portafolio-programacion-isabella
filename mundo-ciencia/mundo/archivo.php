<?php
/*
 * ARCHIVO: archivo.php
 * PARA QUÉ SIRVE: Comprueba permisos y entrega un archivo almacenado en uploads.
 * DÓNDE SE UTILIZA: Imágenes de proyectos, avances, curiosidades y avatares; no tiene una pantalla propia.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Prepara $a con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
$a = (string) ($_GET['archivo'] ?? '');
// Prepara $tipo con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
$tipo = $_GET['tipo'] ?? 'experimento';
// Prepara la cuenta o el identificador que se utiliza en esta operación.
$u = usuario_actual();
// Inicia o actualiza la comprobación de acceso al archivo solicitado.
$permitido = false;
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if ($tipo === 'experimento') {
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach (filas('SELECT e.* FROM experimentos e LEFT JOIN experimento_imagenes i ON i.experimento_id=e.id WHERE i.archivo=? OR e.archivo=?', [$a, $a]) as $e)if (puede_ver_experimento($u, $e))$permitido = true;
} elseif ($tipo === 'avatar')$permitido = (bool) valor('SELECT id FROM usuarios WHERE avatar=? AND estado=?', [$a, 'activo']);
// Comprueba los permisos de administración antes de continuar con esta acción.
elseif ($tipo === 'dato')$permitido = (bool) valor('SELECT id FROM datos_cientificos WHERE imagen=? AND (activo=1 OR ?=1)', [$a, puede_moderar($u) ? 1 : 0]);
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
elseif ($tipo === 'robotica') {
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach (filas('SELECT * FROM proyectos_robotica WHERE portada=?', [$a]) as $p)if ($p['moderacion'] === 'aprobado' || ($u && (puede_moderar($u) || miembro_robotica($u, $p))))$permitido = true;
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach (filas('SELECT a.* FROM avances_robotica a JOIN avances_robotica_imagenes i ON i.avance_id=a.id WHERE i.archivo=?', [$a]) as $av) {
        // Consulta proyectos_robotica. Proyectos del club, sus responsables, portada y estado de revisión.
        $p = fila('SELECT * FROM proyectos_robotica WHERE id=?', [$av['proyecto_id']]);
        // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
        if (ver_avance($av, $p, $u))$permitido = true;
    }
}
// Resuelve la carpeta base para comprobar que la ruta pertenece al proyecto.
$raiz = realpath(__DIR__ . '/uploads');
// Resuelve la ruta del recurso que se necesita utilizar.
$ruta = realpath(__DIR__ . '/uploads/' . $a);
// Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
if (!$permitido || !$ruta || !str_starts_with($ruta, $raiz . DIRECTORY_SEPARATOR) || !is_file($ruta))denegar(404);
// Identifica el tipo real del archivo antes de guardarlo o entregarlo.
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($ruta);
// Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'], true))denegar(404);
header('Content-Type: ' . $mime);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('Content-Disposition: ' . ($mime === 'application/pdf' ? 'attachment' : 'inline') . '; filename="' . basename($ruta) . '"');
// Envía al navegador el contenido del archivo autorizado.
readfile($ruta);
