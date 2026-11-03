<?php
/*
 * ARCHIVO: datos.php
 * PARA QUÉ SIRVE: Reutiliza las tarjetas de curiosidades activas.
 * DÓNDE SE UTILIZA: Ver curiosidades, desde el inicio.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = '¿Sabías que…?';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/includes/encabezado.php'; ?><!-- Agrupa un apartado de contenido. Ubicación: Ver curiosidades, desde el inicio. --><section class="container">
    <p class="eyebrow">Una dosis de asombro</p>
    <!-- Muestra el título principal de esta pantalla. Ubicación: Ver curiosidades, desde el inicio. -->
    <?php datos_cards(); ?><div class="stack">
        <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas('SELECT * FROM datos_cientificos WHERE activo=1 ORDER BY categoria,titulo') as $d): ?><!-- Enlace de este apartado: abre dato.php. --><a class="info-card" href="<?= e(url('dato.php?id=' . $d['id'])) ?>"><?= e($d['categoria'] . ' · ' . $d['titulo']) ?> →</a><?php endforeach; ?>
    </div>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/includes/pie_pagina.php'; ?>
