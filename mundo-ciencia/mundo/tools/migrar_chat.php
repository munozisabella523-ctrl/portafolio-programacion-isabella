<?php
/* ARCHIVO: tools/migrar_chat.php
 * MANTENIMIENTO DESDE TERMINAL
 * Instala las tablas del chat y añade descripciones de los módulos de la base de datos.
 * No es una página pública. Conserva las tablas y registros existentes.
 */
// Añade el chat y describe los módulos sin renombrar ni eliminar tablas utilizadas.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../config/base_datos.php';
$db = base_datos();
$db->multi_query(file_get_contents(__DIR__ . '/../migracion_chat.sql'));
do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());
$grupos = [
 '01 Cuentas y acceso' => ['usuarios'=>'Cuentas, perfiles y roles', 'intentos_login'=>'Protección contra intentos repetidos'],
 '02 Experimentos' => ['experimentos'=>'Proyectos científicos y estado de publicación','experimento_imagenes'=>'Fotos de cada experimento','calificaciones'=>'Evaluaciones del profesor','comentarios'=>'Comentarios de experimentos y avances','favoritos'=>'Proyectos guardados por usuarios','reconocimientos'=>'Distinciones de la feria','moderaciones'=>'Decisiones de revisión'],
 '03 Robótica' => ['proyectos_robotica'=>'Proyectos del club','miembros_robotica'=>'Participantes por proyecto','avances_robotica'=>'Seguimiento de proyectos','avances_robotica_imagenes'=>'Fotos de cada avance'],
 '04 Contenido' => ['datos_cientificos'=>'Curiosidades publicadas'],
 '05 Chat' => ['chat_conversaciones'=>'Propietario, título y fechas','chat_mensajes'=>'Preguntas, respuestas y avisos ordenados'],
 '06 Actividad' => ['historial_admin'=>'Acciones administrativas','historial_experimentos'=>'Cambios de proyectos','historial_usuarios'=>'Actividad de cuentas','notificaciones'=>'Avisos para usuarios'],
 '07 Sistema' => ['configuracion'=>'Opciones del sitio','versiones_esquema'=>'Migraciones instaladas']
];
foreach ($grupos as $grupo=>$tablas) foreach ($tablas as $tabla=>$descripcion) {
    $comentario = $db->real_escape_string($grupo . ' | ' . $descripcion);
    $db->query("ALTER TABLE `$tabla` COMMENT='$comentario'");
}
$db->query("INSERT IGNORE INTO versiones_esquema(version) VALUES('chat_historial_v1')");
echo "Chat instalado; tablas documentadas por módulo. No se eliminan tablas activas.\n";
