<?php
/*
 * ARCHIVO: club_robotica.php
 * PARA QUÉ SIRVE: Presenta el club y sus proyectos, con enlaces para participar.
 * DÓNDE SE UTILIZA: Botón Robótica del inicio y menú de usuarios registrados.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = usuario_actual();
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Club de Robótica';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/includes/encabezado.php'; ?>
<!-- Presenta la bienvenida del Club de Robótica. Ubicación: Botón Robótica del inicio y menú de usuarios registrados. -->
<section class="container robotics-banner">
    <div>
        <p class="eyebrow">IMAGINAR. CONSTRUIR. CONECTAR.</p>
        <!-- Muestra el título principal de esta pantalla. Ubicación: Botón Robótica del inicio y menú de usuarios registrados. -->
        <h2>Ideas que se ponen en movimiento</h2>
        <p class="lead">Construimos, programamos, probamos y volvemos a intentarlo.</p>
        <!-- Enlace de este apartado: abre editar_robotica.php. -->
        <a class="btn btn-primary" href="<?= e(url($viewer ? 'editar_robotica.php' : 'registro_usuario.php')) ?>">Crear un proyecto de robótica +</a>
    </div>
    <div class="robot-art" aria-hidden="true">⚙<span>EL FUTURO SE HACE EN EQUIPO</span></div>
</section>
<!-- Agrupa un apartado de contenido. Ubicación: Botón Robótica del inicio y menú de usuarios registrados. -->
<section class="container">
    <div class="section-heading">
        <!-- Muestra el título de un apartado. Ubicación: Botón Robótica del inicio y menú de usuarios registrados. -->
        <h2>Ideas en movimiento</h2><span class="meta">Equipos, prototipos y aprendizajes</span>
    </div>
    <div class="project-grid">
        <?php $lista = filas("SELECT p.*,u.nombre FROM proyectos_robotica p JOIN usuarios u ON u.id=p.creador_id WHERE p.moderacion='aprobado' OR p.creador_id=? OR ?=1 OR EXISTS(SELECT 1 FROM miembros_robotica m WHERE m.proyecto_id=p.id AND m.usuario_id=?) ORDER BY p.fecha_actualizacion DESC", [$viewer['id'] ?? 0, puede_moderar($viewer) ? 1 : 0, $viewer['id'] ?? 0]);
// Muestra el estado vacío cuando no hay registros para este apartado.
if (!$lista)echo '<div class="empty-state"><h3>El próximo prototipo puede ser el tuyo</h3><p>Crea un proyecto y reúne a tu equipo.</p></div>';
// Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
foreach ($lista as $p): ?><!-- Presenta una tarjeta o registro individual. Ubicación: Botón Robótica del inicio y menú de usuarios registrados. --><article class="robotics-card project-card">
            <!-- Enlace de este apartado: abre proyecto_robotica.php. -->
            <a href="<?= e(url('proyecto_robotica.php?id=' . $p['id'])) ?>"><!-- Muestra la imagen indicada por src y su descripción alternativa. Ubicación: Botón Robótica del inicio y menú de usuarios registrados. --><img src="<?= e(imagen_url($p['portada'], 'robotica')) ?>" alt="<?= e($p['titulo']) ?>"></a>
            <div class="project-body">
                <p class="eyebrow"><?= e($p['equipo']) ?></p>
                <!-- Muestra el título de una tarjeta o subsección. Ubicación: Botón Robótica del inicio y menú de usuarios registrados. -->
                <h3><!-- Enlace de este apartado: abre proyecto_robotica.php. --><a href="<?= e(url('proyecto_robotica.php?id=' . $p['id'])) ?>"><?= e($p['titulo']) ?></a></h3>
                <p><?= e(mb_strimwidth($p['descripcion'], 0, 140, '…')) ?></p><?php badge($p['estado']);
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($p['moderacion'] !== 'aprobado')badge($p['moderacion']); ?>
            </div>
        </article><?php endforeach; ?></div>
    <!-- Muestra el título de un apartado. Ubicación: Botón Robótica del inicio y menú de usuarios registrados. -->
    <h2>Últimos avances</h2>
    <div class="stack">
        <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas("SELECT a.*,p.titulo proyecto FROM avances_robotica a JOIN proyectos_robotica p ON p.id=a.proyecto_id WHERE a.estado='visible' AND p.moderacion='aprobado' ORDER BY a.fecha_creacion DESC LIMIT 6") as $a): ?><!-- Enlace de este apartado: abre proyecto_robotica.php. --><a class="info-card" href="<?= e(url('proyecto_robotica.php?id=' . $a['proyecto_id'] . '#avance-' . $a['id'])) ?>"><?= e($a['proyecto'] . ' · ' . $a['titulo'] . ' · ' . $a['fecha_avance']) ?> →</a><?php endforeach; ?>
    </div>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/includes/pie_pagina.php'; ?>
