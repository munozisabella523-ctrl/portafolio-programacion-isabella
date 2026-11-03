<?php
/*
 * ARCHIVO: dashboard.php
 * PARA QUÉ SIRVE: Consulta indicadores y muestra contenido según el rol de la cuenta.
 * DÓNDE SE UTILIZA: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = requiere_login();
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Hola, ' . $viewer['nombre']  ;
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/includes/encabezado_gestion.php';
// Obtiene el identificador del registro sobre el que se trabajará.
$id = $viewer['id'];
// Selecciona el contenido o la operación que corresponde al rol de esta cuenta.
if ($viewer['rol'] === 'estudiante') {
    // Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
    $stats = ['Mis proyectos' => valor('SELECT COUNT(*) FROM experimentos WHERE usuario_id=?', [$id]), 'Pendientes' => valor("SELECT COUNT(*) FROM experimentos WHERE usuario_id=? AND estado='pendiente'", [$id]), 'Aprobados' => valor("SELECT COUNT(*) FROM experimentos WHERE usuario_id=? AND estado='aprobado'", [$id]), 'Promedio' => valor('SELECT ROUND(AVG(nota),2) FROM calificaciones WHERE estudiante_id=?', [$id]) ?? '—'];
    // Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
    $lista = filas('SELECT e.*,u.nombre autor_nombre FROM experimentos e JOIN usuarios u ON u.id=e.usuario_id WHERE usuario_id=? ORDER BY fecha_actualizacion DESC LIMIT 6', [$id]);
    // Recoge o prepara el título que corresponde a este apartado o registro.
    $titulo = 'Continúa trabajando';
} elseif ($viewer['rol'] === 'profesor') {
    // Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
    $stats = ['Por calificar' => valor("SELECT COUNT(*) FROM experimentos e JOIN usuarios u ON u.id=e.usuario_id WHERE e.estado='aprobado' AND u.rol='estudiante' AND NOT EXISTS(SELECT 1 FROM calificaciones c WHERE c.experimento_id=e.id AND c.profesor_id=?)", [$id]), 'Proyectos calificados' => valor('SELECT COUNT(*) FROM calificaciones WHERE profesor_id=?', [$id]), 'Estudiantes' => valor("SELECT COUNT(*) FROM usuarios WHERE rol='estudiante'"), 'Promedio de mis evaluaciones' => valor('SELECT ROUND(AVG(nota),2) FROM calificaciones WHERE profesor_id=?', [$id]) ?? '—'];
    // Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
    $lista = filas("SELECT e.*,u.nombre autor_nombre FROM experimentos e JOIN usuarios u ON u.id=e.usuario_id WHERE e.estado='aprobado' AND u.rol='estudiante' AND NOT EXISTS(SELECT 1 FROM calificaciones c WHERE c.experimento_id=e.id AND c.profesor_id=?) ORDER BY e.fecha_creacion DESC LIMIT 12", [$id]);
    // Recoge o prepara el título que corresponde a este apartado o registro.
    $titulo = 'Proyectos esperando calificación';
} else {
    // Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
    $stats = ['Publicaciones pendientes' => valor("SELECT COUNT(*) FROM experimentos WHERE estado='pendiente'"), 'Comentarios pendientes' => valor("SELECT COUNT(*) FROM comentarios WHERE estado='pendiente'"), 'Usuarios' => valor('SELECT COUNT(*) FROM usuarios'), 'Aprobados' => valor("SELECT COUNT(*) FROM experimentos WHERE estado='aprobado'"), 'Rechazados' => valor("SELECT COUNT(*) FROM experimentos WHERE estado='rechazado'"), 'Promedio general' => valor('SELECT ROUND(AVG(nota),2) FROM calificaciones') ?? '—'];
    // Reserva esta decisión a los permisos de superadministrador.
    if (es_super_admin($viewer)) {
        // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
        foreach (ROLES as $r)$stats[ucfirst(str_replace('_', ' ', $r))] = valor('SELECT COUNT(*) FROM usuarios WHERE rol=?', [$r]);
        // Consulta usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
        $stats['Usuarios activos'] = valor("SELECT COUNT(*) FROM usuarios WHERE estado='activo'");
        // Consulta usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
        $stats['Usuarios inactivos'] = valor("SELECT COUNT(*) FROM usuarios WHERE estado='inactivo'");
        // Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
        $stats['Proyectos totales'] = valor('SELECT COUNT(*) FROM experimentos');
    }
    // Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
    $lista = filas("SELECT e.*,u.nombre autor_nombre FROM experimentos e JOIN usuarios u ON u.id=e.usuario_id WHERE e.estado='pendiente' ORDER BY e.fecha_creacion LIMIT 6");
    // Recoge o prepara el título que corresponde a este apartado o registro.
    $titulo = 'Contenido pendiente';
}
?>
<!-- Agrupa un apartado de contenido. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. -->
<section class="container">
    <?php require __DIR__ . '/includes/chat_panel.php'; ?>
    <div class="stats-grid"><?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach ($stats as $k => $v): ?><!-- Presenta una tarjeta o registro individual. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. --><article class="stat-card">
            <span><?= e($k) ?></span><strong><?= e($v) ?></strong></article><?php endforeach; ?></div>
    <div class="section-heading">
        <!-- Muestra el título de un apartado. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. -->
        <h2><?= e($titulo) ?></h2>
        <!-- Enlace de este apartado: abre administracion/moderacion.php. -->
        <a class="btn btn-primary" href="<?= e(url(puede_moderar($viewer) ? 'administracion/moderacion.php' : 'gestion_experimentos.php')) ?>"><?= puede_moderar($viewer) ? 'Moderar publicaciones' : 'Crear proyecto +' ?></a>
    </div><?php /* Construye las tarjetas de proyectos con foto, título, categoría, autor y estado. Se usa en listados y paneles. */ tarjetas($lista); ?>
    <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($viewer['rol'] === 'profesor'): ?><!-- Agrupa un apartado de contenido. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. --><section class="info-card">
        <!-- Muestra el título de un apartado. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. -->
        <h2>Estudiantes</h2>
        <p><!-- Enlace de este apartado: abre area_personal/perfil_usuario.php. --><a class="btn btn-outline" href="<?= e(url('area_personal/perfil_usuario.php')) ?>">Ver y actualizar mis evaluaciones</a>
        </p>
        <!-- Agrupa campos que se envían al pulsar su botón. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. -->
        <form method="get" class="search-form"><label>Buscar estudiante<!-- Permite introducir o enviar el campo q. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. --><input name="q"
                    value="<?= e($_GET['q'] ?? '') ?>"></label><!-- Ofrece el control de acción de este bloque. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. --><button class="btn btn-outline">Buscar</button></form>
        <div class="people-grid">
            <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas("SELECT id,nombre FROM usuarios WHERE rol='estudiante' AND nombre LIKE ? ORDER BY nombre LIMIT 100", ['%' . mb_substr($_GET['q'] ?? '', 0, 120) . '%']) as $s): ?><!-- Enlace de este apartado: abre explorar.php. --><a class="comment-card" href="<?= e(url('explorar.php?autor=' . $s['id'])) ?>"><?= e($s['nombre']) ?> · Ver proyectos →</a><?php endforeach; ?>
        </div>
    </section><?php endif; ?>
    <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($viewer['rol'] === 'estudiante'): ?><!-- Muestra el título de un apartado. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. --><h2>Comentarios recientes en tus proyectos</h2>
    <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas("SELECT c.*,u.nombre,e.titulo FROM comentarios c JOIN experimentos e ON e.id=c.experimento_id JOIN usuarios u ON u.id=c.usuario_id WHERE e.usuario_id=? AND c.estado='visible' ORDER BY c.fecha_creacion DESC LIMIT 5", [$id]) as $c): ?>
    <!-- Presenta una tarjeta o registro individual. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. -->
    <article class="comment-card">
        <!-- Enlace de este apartado: abre experimento.php. -->
        <a href="<?= e(url('experimento.php?id=' . $c['experimento_id'])) ?>"><?= e($c['titulo']) ?></a>
        <p><?= e($c['nombre'] . ': ' . $c['comentario']) ?></p>
    </article><?php endforeach; ?><!-- Muestra el título de un apartado. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. --><h2>Mis favoritos</h2>
    <?php /* Construye las tarjetas de proyectos con foto, título, categoría, autor y estado. Se usa en listados y paneles. */ tarjetas(filas("SELECT e.*,u.nombre autor_nombre FROM favoritos f JOIN experimentos e ON e.id=f.experimento_id JOIN usuarios u ON u.id=e.usuario_id WHERE f.usuario_id=? AND e.estado='aprobado' LIMIT 6", [$id])); endif; ?>
    <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if (puede_moderar($viewer)): ?><!-- Muestra el título de un apartado. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. --><h2>Estado de los proyectos</h2>
    <div class="info-card"><?php $total = max(1, (int) valor('SELECT COUNT(*) FROM experimentos'));
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach (filas('SELECT estado,COUNT(*) cantidad FROM experimentos GROUP BY estado') as $r): ?><div
            class="chart-row"><span><?= e(str_replace('_', ' ', $r['estado'])) ?></span><meter min="0"
                max="<?= $total ?>"
                value="<?= $r['cantidad'] ?>"><?= $r['cantidad'] ?></meter><strong><?= $r['cantidad'] ?></strong></div>
        <?php endforeach; ?></div>
    <!-- Muestra el título de un apartado. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. -->
    <h2>Actividad administrativa reciente</h2>
    <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas('SELECT h.*,u.nombre FROM historial_admin h JOIN usuarios u ON u.id=h.admin_id ORDER BY h.id DESC LIMIT 8') as $h): ?>
    <p class="comment-card"><?= e($h['nombre'] . ' · ' . $h['accion'] . ' · ' . $h['fecha']) ?></p>
    <?php endforeach; ?><!-- Enlace de este apartado: abre administracion/calificaciones.php. --><a class="btn btn-outline" href="<?= e(url('administracion/calificaciones.php')) ?>">Todas las calificaciones</a>
    <?php else: ?><!-- Muestra el título de un apartado. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. --><h2>Mi actividad reciente</h2>
    <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas('SELECT accion,fecha FROM historial_usuarios WHERE usuario_id=? ORDER BY id DESC LIMIT 6', [$id]) as $h): ?>
    <p class="comment-card"><?= e($h['accion'] . ' · ' . $h['fecha']) ?></p><?php endforeach; ?><!-- Muestra el título de un apartado. Ubicación: Mi panel: proyectos propios, evaluaciones del profesor o resumen administrativo. --><h2>Explora algo nuevo
    </h2><?php /* Construye las tarjetas de proyectos con foto, título, categoría, autor y estado. Se usa en listados y paneles. */ tarjetas(filas("SELECT e.*,u.nombre autor_nombre FROM experimentos e JOIN usuarios u ON u.id=e.usuario_id WHERE e.estado='aprobado' ORDER BY e.destacado DESC,e.fecha_creacion DESC LIMIT 3"));
    // Construye hasta seis tarjetas giratorias de curiosidades activas. Se muestran en Inicio, Datos y algunos paneles.
    datos_cards(); endif; ?>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/includes/pie_pagina.php'; ?>

