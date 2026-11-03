<?php
/*
 * ARCHIVO: administracion/calificaciones.php
 * PARA QUÉ SIRVE: Consulta notas con el proyecto, estudiante y profesor correspondientes.
 * DÓNDE SE UTILIZA: Todas las calificaciones, desde el panel administrativo.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = exigir_administrador();
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Todas las calificaciones';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/../includes/encabezado_gestion.php'; ?><!-- Agrupa un apartado de contenido. Ubicación: Todas las calificaciones, desde el panel administrativo. --><section class="container stack">
    <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas('SELECT c.*,e.titulo,p.nombre profesor,u.nombre estudiante FROM calificaciones c JOIN experimentos e ON e.id=c.experimento_id JOIN usuarios p ON p.id=c.profesor_id JOIN usuarios u ON u.id=c.estudiante_id ORDER BY c.fecha_actualizacion DESC') as $n): ?>
    <!-- Presenta una tarjeta o registro individual. Ubicación: Todas las calificaciones, desde el panel administrativo. -->
    <article class="comment-card">
        <!-- Muestra el título de una tarjeta o subsección. Ubicación: Todas las calificaciones, desde el panel administrativo. -->
        <h3><!-- Enlace de este apartado: abre experimento.php. --><a href="<?= e(url('experimento.php?id=' . $n['experimento_id'])) ?>"><?= e($n['titulo']) ?></a> ·
            <?= e($n['nota']) ?>/5</h3>
        <p><?= e($n['estudiante'] . ' · Profesor: ' . $n['profesor']) ?></p>
        <p><?= e($n['comentario']) ?></p><small><?= e($n['fecha_actualizacion']) ?></small>
    </article><?php endforeach; ?></section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/../includes/pie_pagina.php'; ?>
