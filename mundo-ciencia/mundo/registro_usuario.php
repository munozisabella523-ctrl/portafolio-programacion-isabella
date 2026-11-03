<?php
/*
 * ARCHIVO: registro_usuario.php
 * PARA QUÉ SIRVE: Muestra el formulario para crear una cuenta de estudiante.
 * DÓNDE SE UTILIZA: Pantalla Registrarme del menú superior.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Tu próxima idea empieza aquí';
// Prepara $activePage con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
$activePage = 'registro';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require_once __DIR__ . '/includes/encabezado.php';
?>
<!-- Agrupa un apartado de contenido. Ubicación: Pantalla Registrarme del menú superior. -->
<section class="auth-shell">
    <!-- Agrupa campos que se envían al pulsar su botón. Ubicación: Pantalla Registrarme del menú superior. -->
    <form class="form-card" action="controllers/gestion_sesion.php?action=registrar_usuario" method="post">
        <?php /* Inserta el token en un campo oculto del formulario; no es visible en pantalla. */ campo_csrf(); ?>
        <!-- Muestra el título principal de esta pantalla. Ubicación: Pantalla Registrarme del menú superior. -->
        <h1>Crea tu cuenta</h1>
        <label>Nombre completo
            <!-- Permite introducir o enviar el campo nombre. Ubicación: Pantalla Registrarme del menú superior. -->
            <input type="text" name="nombre" required maxlength="120">
        </label>
        <label>Usuario
            <!-- Permite introducir o enviar el campo usuario. Ubicación: Pantalla Registrarme del menú superior. -->
            <input type="text" name="usuario" required maxlength="60">
        </label>
        <label class="campo-ancho">Correo
            <!-- Permite introducir o enviar el campo correo. Ubicación: Pantalla Registrarme del menú superior. -->
            <input type="email" name="correo" required maxlength="120">
        </label>
        <label>Contraseña
            <!-- Permite introducir o enviar el campo contrasena. Ubicación: Pantalla Registrarme del menú superior. -->
            <input type="password" name="contrasena" required minlength="8">
        </label>
        <label>Confirmar contraseña
            <!-- Permite introducir o enviar el campo confirmar_contrasena. Ubicación: Pantalla Registrarme del menú superior. -->
            <input type="password" name="confirmar_contrasena" required minlength="8">
        </label>
        <!-- Ofrece el control de acción de este bloque. Ubicación: Pantalla Registrarme del menú superior. -->
        <button class="btn btn-primary" type="submit">Registrar</button>
        <p>¿Ya tienes cuenta? <!-- Enlace de este apartado: abre inicio_sesion.php. --><a href="inicio_sesion.php">Iniciar sesión</a></p>
    </form>
</section>
<?php /* Inserta el footer con contactos y carga las interacciones. */ require_once __DIR__ . '/includes/pie_pagina.php'; ?>
