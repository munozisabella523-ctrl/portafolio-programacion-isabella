<!-- ARCHIVO: includes/pie_pagina.php. Muestra el footer, Instagram y WhatsApp; prepara las ventanas de confirmación y fotografías compartidas. -->
</main>
<!-- Muestra el footer con enlaces, Instagram y WhatsApp. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. -->
<footer class="site-footer">
    <div class="footer-content">
        <div>
            <!-- Logo de navegación: permite volver al inicio del sitio. -->
            <a class="brand" href="<?= e(url('inicio.php')) ?>" aria-label="Mundo Ciencia, volver al inicio">
                <span class="brand-mark" aria-hidden="true">⚛</span>
                <span>Mundo<span class="brand-green">Ciencia</span></span>
            </a>
            <p>Ciencia que nos conecta.</p>
        </div>
        <!-- Agrupa enlaces o controles de navegación. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. -->
        <nav aria-label="Enlaces del pie de página">
            <!-- Muestra el título de un apartado. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. -->
            <h2>Descubre</h2>
            <!-- Enlace de este apartado: abre explorar.php. -->
            <a href="<?= e(url('explorar.php')) ?>">Experimentos</a>
            <!-- Enlace de este apartado: abre feria.php. -->
            <a href="<?= e(url('feria.php')) ?>">Nuestra feria</a>
        </nav>
        <div class="footer-contact">
            <!-- Muestra el título de un apartado. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. -->
            <h2>Contáctanos</h2>
            <!-- Enlace del footer: abre el Instagram @mundo__ciencia en una pestaña nueva. -->
            <a href="https://www.instagram.com/mundo__ciencia/" target="_blank" rel="noopener noreferrer">
                <span>Instagram</span> @mundo__ciencia ↗
            </a>
            <!-- Enlace del footer: abre WhatsApp para contactar al número indicado; no envía un mensaje automáticamente. -->
            <a href="https://wa.me/573145169841" target="_blank" rel="noopener noreferrer">
                <span>WhatsApp</span> +57 314 516 9841 ↗
            </a>
        </div>
    </div>
    <div class="footer-bottom">© <?= date('Y') ?> Mundo Ciencia · Comunidad escolar</div>
</footer>
<!-- Define una ventana que JavaScript abre sobre la página. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. -->
<dialog class="modal" id="confirmacion">
    <!-- Agrupa campos que se envían al pulsar su botón. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. -->
    <form method="dialog">
        <!-- Muestra el título de un apartado. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. -->
        <h2>Confirmar acción</h2>
        <p id="confirmacion-texto"></p>
        <div class="card-actions"><!-- Ofrece el control de acción de este bloque. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. --><button class="btn btn-outline" value="cancelar">Cancelar</button><!-- Ofrece el control de acción de este bloque. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. --><button
                class="btn btn-primary" value="aceptar">Confirmar</button></div>
    </form>
</dialog>
<!-- Define una ventana que JavaScript abre sobre la página. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. -->
<dialog class="modal lightbox" id="visor"><!-- Ofrece el control de acción de este bloque. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. --><button class="btn btn-outline cerrar-visor"
        aria-label="Cerrar fotografía">Cerrar ×</button><!-- Muestra la imagen indicada por src y su descripción alternativa. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. --><img alt="Fotografía ampliada"></dialog>
<!-- Carga las interacciones de JavaScript después del HTML. Ubicación: Footer con Instagram y WhatsApp; ventanas de confirmación y fotografías. -->
<script
    src="<?= e(url('js/confirmaciones_interfaz.js?v=' . md5_file(__DIR__ . '/../js/confirmaciones_interfaz.js'))) ?>"
    defer></script>
<?php require __DIR__ . '/chat_widget.php'; ?>
</body>

</html>

