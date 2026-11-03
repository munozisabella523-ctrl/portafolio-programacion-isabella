<?php
/*
 * ARCHIVO: inicio.php
 * PARA QUÉ SIRVE: Presenta la portada científica, las fotografías, el carrusel, las curiosidades y el acceso flotante a Robótica.
 * DÓNDE SE UTILIZA: Inicio público; una persona con sesión iniciada es enviada a su panel.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Si hay una cuenta conectada, abre su panel en lugar del inicio público.
if (usuario_actual()) ir('dashboard.php');
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Mundo Ciencia';
// Selecciona las clases de diseño aplicadas al fondo de esta página.
$bodyClass = 'inicio-publico';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/includes/encabezado.php';
?>
<!-- Portada del inicio: imagen de laboratorio, título y enlace para bajar. -->
<section class="portada-ciencia" aria-labelledby="bienvenida-titulo">
    <div>
        <p class="portada-etiqueta">DESCUBRE · EXPERIMENTA · COMPARTE</p>
        <h1 id="bienvenida-titulo">Mundo Ciencia</h1>
        <p>Las grandes ideas empiezan con una pregunta.</p>
    </div>
    <a class="portada-bajar" href="#descubre" aria-label="Ver las fotografías de ciencia">↓</a>
</section>

<!-- Tres imágenes ilustrativas; las fotos de la feria siguen en el carrusel. -->
<section class="container fotos-ciencia" id="descubre" aria-label="La ciencia en imágenes">
    <figure>
        <img src="<?= e(url('imagenes/microscopio.jpg')) ?>" alt="Investigador utilizando un microscopio en el laboratorio" width="960" height="640" loading="lazy">
        <figcaption><span>01 / INVESTIGAR</span><h2>Mirar más allá</h2></figcaption>
    </figure>
    <figure>
        <img src="<?= e(url('imagenes/robot.jpg')) ?>" alt="Escultura de un robot hecha con circuitos reciclados" width="960" height="640" loading="lazy">
        <figcaption><span>02 / INVENTAR</span><h2>Dar vida a las ideas</h2></figcaption>
    </figure>
    <figure>
        <img src="<?= e(url('imagenes/solar.jpg')) ?>" alt="Paneles solares bajo el cielo" width="960" height="640" loading="lazy">
        <figcaption><span>03 / TRANSFORMAR</span><h2>Imaginar el futuro</h2></figcaption>
    </figure>
</section>
<?php /* Inserta el carrusel de la portada y la feria. */ require __DIR__ . '/includes/feria_galeria.php'; ?>

<!-- Agrupa las tarjetas de ¿Sabías que? en Inicio. Ubicación: Inicio público; una persona con sesión iniciada es enviada a su panel. -->
<section class="container curiosity" aria-labelledby="curiosidades-titulo">
    <div class="section-heading">
        <!-- Muestra el título de un apartado. Ubicación: Inicio público; una persona con sesión iniciada es enviada a su panel. -->
        <h2 id="curiosidades-titulo">¿Sabías que…?</h2>
        <!-- Enlace de este apartado: abre datos.php. -->
        <a href="<?= e(url('datos.php')) ?>">Ver curiosidades →</a>
    </div>
    <?php /* Construye hasta seis tarjetas giratorias de curiosidades activas. Se muestran en Inicio, Datos y algunos paneles. */ datos_cards(); ?>
</section>

<!-- Botón fijo a la izquierda del inicio: abre el Club de Robótica. -->
<a class="robotics-fixed" href="<?= e(url('club_robotica.php')) ?>" aria-label="Club de Robótica">
    <span aria-hidden="true">⚙</span><span>Robótica</span>
</a>

<?php require __DIR__ . '/includes/pie_pagina.php'; ?>
