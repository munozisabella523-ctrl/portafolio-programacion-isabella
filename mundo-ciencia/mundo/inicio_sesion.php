<?php
/*
 * ARCHIVO: inicio_sesion.php
 * PARA QUÉ SIRVE: Muestra el formulario de acceso con correo y contraseña.
 * DÓNDE SE UTILIZA: Pantalla Iniciar sesión del menú superior.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Qué bueno verte de nuevo';
// Prepara $activePage con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
$activePage = 'inicio_sesion';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require_once __DIR__ . '/includes/encabezado.php';
?>
<!-- Agrupa un apartado de contenido. Ubicación: Pantalla Iniciar sesión del menú superior. -->
<section class="auth-shell">
    <!-- Agrupa campos que se envían al pulsar su botón. Ubicación: Pantalla Iniciar sesión del menú superior. -->
    <form class="form-card" action="controllers/gestion_sesion.php?action=iniciar_sesion" method="post">
        <?php /* Inserta el token en un campo oculto del formulario; no es visible en pantalla. */ campo_csrf(); ?>
        <!-- Muestra el título principal de esta pantalla. Ubicación: Pantalla Iniciar sesión del menú superior. -->
        <h1>Qué bueno verte de nuevo</h1>
        <label>Correo
            <!-- Permite introducir o enviar el campo correo. Ubicación: Pantalla Iniciar sesión del menú superior. -->
            <input type="email" name="correo" required autocomplete="email">
        </label>
        <label>Contraseña
            <!-- Permite introducir o enviar el campo contrasena. Ubicación: Pantalla Iniciar sesión del menú superior. -->
            <input type="password" name="contrasena" required autocomplete="current-password">
        </label>
        <!-- Ofrece el control de acción de este bloque. Ubicación: Pantalla Iniciar sesión del menú superior. -->
        <button class="btn btn-primary" type="submit">Ingresar</button>
        <p>No tienes cuenta? <!-- Enlace de este apartado: abre registro_usuario.php. --><a href="registro_usuario.php">Crear una cuenta</a></p>
    </form>
</section>
<?php /* Inserta el footer con contactos y carga las interacciones. */ require_once __DIR__ . '/includes/pie_pagina.php'; ?>
