<?php
/*
 * ARCHIVO: dato.php
 * PARA QUÉ SIRVE: Consulta una curiosidad y muestra su explicación y, si existe, su fuente científica.
 * DÓNDE SE UTILIZA: Detalle abierto mediante Descubrir más.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Consulta datos_cientificos. Curiosidades, categoría, imagen y visibilidad. Alimentan ¿Sabías que? y Datos científicos.
$d = fila('SELECT * FROM datos_cientificos WHERE id=? AND (activo=1 OR ?=1)', [(int) ($_GET['id'] ?? 0), puede_moderar() ? 1 : 0]);
// Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
if (!$d)denegar(404);
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = $d['titulo'];
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/includes/encabezado.php'; ?><!-- Presenta una tarjeta o registro individual. Ubicación: Detalle abierto mediante Descubrir más. --><article class="container reading">
    <!-- Enlace de este apartado: abre datos.php. -->
    <a href="<?= e(url('datos.php')) ?>">← Datos científicos</a>
    <p class="eyebrow"><?= e($d['categoria']) ?></p>
    <!-- Muestra el título principal de esta pantalla. Ubicación: Detalle abierto mediante Descubrir más. -->
    <h1><?= e($d['titulo']) ?></h1><?php if ($d['imagen']): ?><!-- Muestra la imagen indicada por src y su descripción alternativa. Ubicación: Detalle abierto mediante Descubrir más. --><img class="cover"
        src="<?= e(imagen_url($d['imagen'], 'dato')) ?>" alt="<?= e($d['titulo']) ?>"><?php endif; ?><p class="lead">
        <?= nl2br(e($d['descripcion'])) ?></p><?php /* Carga las fuentes de la curiosidad para mostrar su enlace. */ require __DIR__ . '/includes/fuentes_datos.php';
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if (isset($fuentesDatos[$d['titulo']])): ?><p>
        <!-- Enlace del componente: abre el destino que se construye en href. -->
        <a href="<?= e($fuentesDatos[$d['titulo']]) ?>" target="_blank" rel="noopener noreferrer">Consultar fuente científica ↗</a>
    </p>
    <?php endif; ?><!-- Enlace de este apartado: abre explorar.php. --><a class="btn btn-primary"  href="<?= e(url('explorar.php?categoria=' . rawurlencode($d['categoria']))) ?>">Explorar proyectos relacionados</a>
</article><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/includes/pie_pagina.php'; ?>
