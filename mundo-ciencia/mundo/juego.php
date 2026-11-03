<?php
// Página del único juego: utiliza el mismo menú y footer que el resto del sitio.
require_once __DIR__ . '/includes/funciones.php';
$pageTitle = 'Adivina el invento';
require __DIR__ . '/includes/encabezado.php';
?>
<section class="container juego" aria-labelledby="juego-titulo">
    <p class="eyebrow">UN RETO DE CIENCIA</p>
    <h1 id="juego-titulo">Adivina el invento</h1>
    <p>Lee la pista y elige una respuesta. ¡Consigue los 5 puntos!</p>

    <!-- JavaScript cambia la pista, las opciones y el marcador en esta tarjeta. -->
    <div class="juego-tarjeta">
        <div class="juego-marcador"><span id="ronda"></span><span id="puntos"></span></div>
        <h2 id="pista" tabindex="-1"></h2>
        <div id="opciones" class="juego-opciones"></div>
        <p id="respuesta" role="status" aria-live="polite"></p>
        <button id="siguiente" class="btn btn-primary" hidden>Siguiente pista →</button>
        <button id="reiniciar" class="btn btn-primary" hidden>Volver a jugar</button>
    </div>
</section>
<!-- Este archivo solo se carga aquí; no modifica las otras páginas. -->
<!-- La versión depende del contenido: al editar el archivo se descarga el script actualizado. -->
<script src="<?= e(url('js/juego.js?v=' . md5_file(__DIR__ . '/js/juego.js'))) ?>" defer></script>
<?php require __DIR__ . '/includes/pie_pagina.php'; ?>
