<?php
/*
 * ARCHIVO: includes/encabezado_gestion.php
 * PARA QUÉ SIRVE: Reutiliza el encabezado general y añade navegación de gestión y título.
 * DÓNDE SE UTILIZA: Parte superior del panel y de las pantallas administrativas.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/encabezado.php'; ?>
<div class="admin-nav"><!-- Enlace de este apartado: abre dashboard.php. --><a href="<?= e(url('dashboard.php')) ?>">Mi panel</a><a href="<?= e(url('historial_chat.php')) ?>">Conversaciones</a>
    <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if (puede_moderar($viewer)): ?>
    <!-- Enlace de este apartado: abre administracion/moderacion.php. -->
    <a href="<?= e(url('administracion/moderacion.php')) ?>">Contenido pendiente</a><!-- Enlace de este apartado: abre administracion/gestion_usuarios.php. --><a href="<?= e(url('administracion/gestion_usuarios.php')) ?>">Usuarios</a><!-- Enlace de este apartado: abre administracion/comentarios.php. --><a href="<?= e(url('administracion/comentarios.php')) ?>">Comentarios</a><!-- Enlace de este apartado: abre administracion/datos_cientificos.php. --><a href="<?= e(url('administracion/datos_cientificos.php')) ?>">Datos científicos</a><!-- Enlace de este apartado: abre administracion/historial_actividad.php. --><a href="<?= e(url('administracion/historial_actividad.php')) ?>">Historial</a>
    <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if (es_super_admin($viewer)): ?><!-- Enlace de este apartado: abre administracion/configuracion.php. --><a href="<?= e(url('administracion/configuracion.php')) ?>">Sistema</a><?php endif; endif; ?>
</div>
<!-- Agrupa un apartado de contenido. Ubicación: Parte superior del panel y de las pantallas administrativas. -->
<section class="page-band">
    <p class="eyebrow"><?= e(str_replace('_', ' ', $viewer['rol'])) ?></p>
    <!-- Muestra el título principal de esta pantalla. Ubicación: Parte superior del panel y de las pantallas administrativas. -->
    <h1><?= e($pageTitle) ?></h1>
</section>

