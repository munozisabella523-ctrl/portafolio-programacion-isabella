<?php
/*
 * ARCHIVO: feria.php
 * PARA QUÉ SIRVE: Reutiliza el carrusel y lista proyectos destacados o reconocidos.
 * DÓNDE SE UTILIZA: Feria de Ciencia; los destacados permanecen en esta pantalla.
 como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Nuestra feria científica';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/includes/encabezado.php';
// Inserta el carrusel con su portada, fotografías y controles.
require __DIR__ . '/includes/feria_galeria.php'; ?><!-- Agrupa un apartado de contenido. Ubicación: Feria de Ciencia; los destacados permanecen en esta pantalla. --><section class="container">
    <!-- Muestra el título de un apartado. Ubicación: Feria de Ciencia; los destacados permanecen en esta pantalla. -->
    <h2>Proyectos destacados de la feria</h2>
    <?php /* Construye las tarjetas de proyectos con foto, título, categoría, autor y estado. Se usa en listados y paneles. */ tarjetas(filas("SELECT e.*,u.nombre autor_nombre FROM experimentos e JOIN usuarios u ON u.id=e.usuario_id WHERE e.estado='aprobado' AND (e.destacado=1 OR EXISTS(SELECT 1 FROM reconocimientos r WHERE r.experimento_id=e.id)) ORDER BY e.fecha_creacion DESC")); ?>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/includes/pie_pagina.php'; ?>
