<?php
/*
 * ARCHIVO: notificaciones.php
 * PARA QUÉ SIRVE: Consulta los avisos de la cuenta y permite marcarlos como leídos.
 * DÓNDE SE UTILIZA: Notificaciones del menú de una persona identificada.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = requiere_login();
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Notificaciones';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/includes/encabezado.php'; ?>
<!-- Agrupa un apartado de contenido. Ubicación: Notificaciones del menú de una persona identificada. -->
<section class="container reading">
    <div class="section-heading">
        <!-- Muestra el título principal de esta pantalla. Ubicación: Notificaciones del menú de una persona identificada. -->
        <h1>Tus novedades</h1><?php formulario('notificacion_leer', 'inline');
// Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
oculto('id', 0); ?><!-- Ofrece el control de acción de este bloque. Ubicación: Notificaciones del menú de una persona identificada. --><button class="btn btn-outline">Marcar todas como leídas</button></form>
    </div>
    <?php $lista = filas('SELECT * FROM notificaciones WHERE usuario_id=? ORDER BY id DESC LIMIT 100', [$viewer['id']]);
// Muestra el estado vacío cuando no hay registros para este apartado.
if (!$lista): ?><div class="empty-state">
        <!-- Muestra el título de un apartado. Ubicación: Notificaciones del menú de una persona identificada. -->
        <h2>Todo al día</h2>
        <p>Aquí encontrarás las novedades de tus proyectos.</p>
    </div><?php endif;
// Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
foreach ($lista as $n): ?><!-- Presenta una tarjeta o registro individual. Ubicación: Notificaciones del menú de una persona identificada. --><article class="notification-item <?= $n['leida'] ? '' : 'unread' ?>">
        <!-- Muestra el título de una tarjeta o subsección. Ubicación: Notificaciones del menú de una persona identificada. -->
        <h3><!-- Enlace del componente: abre el destino que se construye en href. --><a href="<?= e(url($n['url'])) ?>"><?= e($n['titulo']) ?></a></h3>
        <p><?= e($n['mensaje']) ?></p><small><?= e($n['fecha_creacion']) ?></small><?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if (!$n['leida']): /* Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción. */ formulario('notificacion_leer', 'inline');
    // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
    oculto('id', $n['id']); ?><!-- Ofrece el control de acción de este bloque. Ubicación: Notificaciones del menú de una persona identificada. --><button class="btn btn-outline">Marcar como leída</button></form><?php endif; ?>
    </article><?php endforeach; ?>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/includes/pie_pagina.php'; ?>
