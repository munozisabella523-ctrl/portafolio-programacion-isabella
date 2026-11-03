<?php
/*
 * ARCHIVO: includes/feria_galeria.php
 * PARA QUÉ SIRVE: Define la portada, las cuatro fotografías y sus controles de navegación.
 * DÓNDE SE UTILIZA: Carrusel del inicio y de la página Feria de Ciencia.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// La primera imagen es la portada; las otras cuatro son fotografías de la feria.
// Define la portada y las cuatro fotografías que recorrerá el carrusel.
$fotosFeria = [
    ['imagen_portada.png', 'Feria de la Ciencia'],
    ['imagen_carrusel_01.jpg', 'Ideas y proyectos de nuestra comunidad'],
    ['imagen_carrusel_02.jpg', 'Ciencia y creatividad en equipo'],
    ['imagen_carrusel_03.jpg', 'Compartimos nuestros descubrimientos'],
    ['imagen_carrusel_04.jpg', 'Aprendemos experimentando'],
];
?>
<!-- Agrupa un apartado de contenido. Ubicación: Carrusel del inicio y de la página Feria de Ciencia. -->
<section class="container fair-section" aria-label="Nuestra feria de ciencia">
    <div class="carousel" aria-roledescription="carrusel" aria-label="Fotografías de la feria">
        <div class="slides">
            <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach ($fotosFeria as $numero => [$archivo, $descripcion]): ?>
            <!-- Muestra la imagen indicada por src y su descripción alternativa. Ubicación: Carrusel del inicio y de la página Feria de Ciencia. -->
            <img <?= $numero ? 'hidden loading="lazy"' : 'fetchpriority="high"' ?>
                src="<?= e(url($archivo)) ?>" alt="<?= e($descripcion) ?>">
            <?php endforeach; ?>
        </div>
        <div class="carousel-controls">
            <span class="carousel-label">Nuestra feria</span>
            <div class="dots" aria-label="Elegir fotografía">
                <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach ($fotosFeria as $numero => [$archivo, $descripcion]): ?>
                <!-- Ofrece el control de acción de este bloque. Ubicación: Carrusel del inicio y de la página Feria de Ciencia. -->
                <button type="button" aria-label="<?= e($descripcion) ?>"
                    data-slide="<?= $numero ?>" <?= $numero === 0 ? 'aria-current="true"' : '' ?>></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-buttons">
                <!-- Ofrece el control de acción de este bloque. Ubicación: Carrusel del inicio y de la página Feria de Ciencia. -->
                <button type="button" class="btn btn-outline prev" aria-label="Fotografía anterior">←</button>
                <!-- Ofrece el control de acción de este bloque. Ubicación: Carrusel del inicio y de la página Feria de Ciencia. -->
                <button type="button" class="btn btn-outline pause" aria-label="Pausar carrusel">Ⅱ</button>
                <!-- Ofrece el control de acción de este bloque. Ubicación: Carrusel del inicio y de la página Feria de Ciencia. -->
                <button type="button" class="btn btn-outline next" aria-label="Fotografía siguiente">→</button>
            </div>
        </div>
    </div>
</section>
