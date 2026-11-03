<?php
/*
 * ARCHIVO: administracion/moderacion.php
 * PARA QUÉ SIRVE: Lista experimentos, proyectos de robótica y avances para su revisión.
 * DÓNDE SE UTILIZA: Administración > Contenido pendiente.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = exigir_administrador();
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Contenido pendiente';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/../includes/encabezado_gestion.php'; ?>
<!-- Agrupa un apartado de contenido. Ubicación: Administración > Contenido pendiente. -->
<section class="container">
    <!-- Muestra el título de un apartado. Ubicación: Administración > Contenido pendiente. -->
    <h2>Experimentos por revisar</h2>
    <div class="project-grid">
        <?php $lista = filas("SELECT e.*,u.nombre autor_nombre FROM experimentos e JOIN usuarios u ON u.id=e.usuario_id WHERE e.estado='pendiente' ORDER BY e.fecha_creacion");
// Muestra el estado vacío cuando no hay registros para este apartado.
if (!$lista)echo '<div class="empty-state">No hay experimentos pendientes.</div>';
// Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
foreach ($lista as $e): ?><!-- Presenta una tarjeta o registro individual. Ubicación: Administración > Contenido pendiente. --><article class="info-card"><!-- Muestra la imagen indicada por src y su descripción alternativa. Ubicación: Administración > Contenido pendiente. --><img class="thumb"
                src="<?= e(imagen_url(valor('SELECT archivo FROM experimento_imagenes WHERE experimento_id=? LIMIT 1', [$e['id']]))) ?>"
                alt="<?= e($e['titulo']) ?>">
            <p class="eyebrow"><?= e($e['categoria']) ?></p>
            <!-- Muestra el título de una tarjeta o subsección. Ubicación: Administración > Contenido pendiente. -->
            <h3><!-- Enlace de este apartado: abre experimento.php. --><a href="<?= e(url('experimento.php?id=' . $e['id'])) ?>"><?= e($e['titulo']) ?></a></h3>
            <p><?= e($e['autor_nombre'] . ' · ' . $e['fecha_creacion']) ?></p><?php badge($e['estado']);
    // Inserta las opciones con las que un administrador revisa esta publicación.
    require __DIR__ . '/../includes/moderar_form.php'; ?>
        </article><?php endforeach; ?></div>
    <!-- Muestra el título de un apartado. Ubicación: Administración > Contenido pendiente. -->
    <h2>Club de Robótica</h2>
    <div class="stack">
        <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas("SELECT * FROM proyectos_robotica WHERE moderacion='pendiente'") as $p): ?><!-- Enlace de este apartado: abre proyecto_robotica.php. --><a class="info-card" href="<?= e(url('proyecto_robotica.php?id=' . $p['id'])) ?>">Proyecto pendiente: <?= e($p['titulo']) ?> →</a><?php endforeach;
// Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
foreach (filas("SELECT a.*,p.titulo proyecto FROM avances_robotica a JOIN proyectos_robotica p ON p.id=a.proyecto_id WHERE a.estado='pendiente'") as $a): ?><!-- Enlace de este apartado: abre proyecto_robotica.php. --><a class="info-card" href="<?= e(url('proyecto_robotica.php?id=' . $a['proyecto_id'] . '#avance-' . $a['id'])) ?>">Avance pendiente: <?= e($a['proyecto'] . ' · ' . $a['titulo']) ?> →</a><?php endforeach; ?>
    </div>
    <!-- Muestra el título de un apartado. Ubicación: Administración > Contenido pendiente. -->
    <h2>Todas las publicaciones</h2>
    <?php /* Construye las tarjetas de proyectos con foto, título, categoría, autor y estado. Se usa en listados y paneles. */ tarjetas(filas('SELECT e.*,u.nombre autor_nombre FROM experimentos e JOIN usuarios u ON u.id=e.usuario_id ORDER BY e.fecha_actualizacion DESC')); ?>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/../includes/pie_pagina.php'; ?>
