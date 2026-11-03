<?php
/*
 * ARCHIVO: administracion/configuracion.php
 * PARA QUÉ SIRVE: Permite al superadministrador guardar el mensaje de la comunidad en la base.
 * DÓNDE SE UTILIZA: Administración > Sistema. El inicio actual usa su propia introducción, no este mensaje.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = requiere_rol('super_administrador');
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Administración del sistema';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/../includes/encabezado_gestion.php'; ?><!-- Agrupa un apartado de contenido. Ubicación: Administración > Sistema. El inicio actual usa su propia introducción, no este mensaje. --><section class="container">
    <?php /* Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción. */ formulario('configuracion'); ?><!-- Muestra el título de un apartado. Ubicación: Administración > Sistema. El inicio actual usa su propia introducción, no este mensaje. --><h2>Mensaje de la comunidad</h2><label>Texto de bienvenida<!-- Permite escribir un texto de varias líneas en mensaje_comunidad. Ubicación: Administración > Sistema. El inicio actual usa su propia introducción, no este mensaje. --><textarea
            name="mensaje_comunidad" required
            maxlength="500"><?= e(valor("SELECT valor FROM configuracion WHERE clave='mensaje_comunidad'")) ?></textarea></label><!-- Ofrece el control de acción de este bloque. Ubicación: Administración > Sistema. El inicio actual usa su propia introducción, no este mensaje. --><button
        class="btn btn-primary">Guardar configuración</button></form>
    <p><!-- Enlace de este apartado: abre administracion/gestion_usuarios.php. --><a href="<?= e(url('administracion/gestion_usuarios.php')) ?>">Crear profesores y administradores o cambiar roles →</a>
    </p>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/../includes/pie_pagina.php'; ?>
