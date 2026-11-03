<?php
/*
 * ARCHIVO: explorar.php
 * PARA QUÉ SIRVE: Consulta proyectos aprobados y los presenta con paginación. Conserva filtros recibidos por enlaces, sin mostrar un buscador.
 * DÓNDE SE UTILIZA: Experimentos, desde el menú o el footer.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Explorar proyectos';
// Lee la categoría recibida por un enlace de proyectos relacionados.
$cat = mb_substr($_GET['categoria'] ?? '', 0, 80);
// Lee el identificador del autor para mostrar sus proyectos.
$autor = (int) ($_GET['autor'] ?? 0);
// Lee el filtro de fecha recibido por la dirección.
$fecha = $_GET['fecha'] ?? '';
// Lee si el enlace solicita únicamente proyectos destacados.
$dest = (int) ($_GET['destacados'] ?? 0);
// Prepara el orden de presentación o de almacenamiento de estos elementos.
$orden = $_GET['orden'] ?? 'recientes';
unset($_GET['q']);
// Reúne las condiciones de visibilidad y selección de los proyectos.
$where = ["e.estado='aprobado'"];
// Prepara parámetros o datos usados por esta operación.
$p = [];
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if ($cat !== '') {
    $where[] = 'e.categoria=?';
    $p[] = $cat;
}
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if ($autor) {
    $where[] = 'e.usuario_id=?';
    $p[] = $autor;
}
// Comprueba el formato del dato antes de utilizarlo.
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $where[] = 'e.fecha_creacion>=?';
    $p[] = $fecha;
}
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if ($dest)$where[] = 'e.destacado=1';
// Une las condiciones para construir la cláusula WHERE de la consulta.
$w = implode(' AND ', $where);
// Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
$total = (int) valor("SELECT COUNT(*) FROM experimentos e WHERE $w", $p);
// Calcula la cantidad total de páginas del listado.
$paginas = max(1, (int) ceil($total / 12));
// Calcula el número de página válido para el listado.
$pagina = min($paginas, max(1, (int) ($_GET['pagina'] ?? 1)));
// Calcula cuántos registros saltar para mostrar la página solicitada.
$offset = ($pagina - 1) * 12;
// Consulta calificaciones. Notas y comentarios del profesor vinculados a proyecto y estudiante. Se ven en Evaluaciones y Mi perfil.
$order = $orden === 'notas' ? '(SELECT AVG(nota) FROM calificaciones c WHERE c.experimento_id=e.id) DESC,e.id DESC' : 'e.fecha_creacion DESC,e.id DESC';
// Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
$lista = filas("SELECT e.*,u.nombre autor_nombre FROM experimentos e JOIN usuarios u ON u.id=e.usuario_id WHERE $w ORDER BY $order LIMIT 12 OFFSET ?", [...$p, $offset]);
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/includes/encabezado.php'; ?>
<!-- Agrupa un apartado de contenido. Ubicación: Experimentos, desde el menú o el footer. -->
<section class="container">
    <p class="eyebrow">EL LABORATORIO DE NUESTRA COMUNIDAD</p>
    <!-- Muestra el título principal de esta pantalla. Ubicación: Experimentos, desde el menú o el footer. -->
    <h2>Una idea puede inspirar otra.</h2>
    <p class="lead">Descubre lo que pasa cuando la curiosidad se convierte en un proyecto.</p>

    <div class="section-heading">
        <!-- Muestra el título de un apartado. Ubicación: Experimentos, desde el menú o el footer. -->
        <h2>Proyectos de la comunidad</h2><span class="meta"><?= $total ?> proyectos</span>
    </div><?php /* Construye las tarjetas de proyectos con foto, título, categoría, autor y estado. Se usa en listados y paneles. */ tarjetas($lista); ?>
    <!-- Agrupa enlaces o controles de navegación. Ubicación: Experimentos, desde el menú o el footer. -->
    <nav class="pagination" aria-label="Páginas">
        <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ for ($i = max(1, $pagina - 2); $i <= min($paginas, $pagina + 2); $i++): ?><!-- Enlace del componente: abre el destino que se construye en href. --><a class="btn <?= $i === $pagina ? 'btn-primary' : 'btn-outline' ?>" href="?<?= e(http_build_query([...$_GET, 'pagina' => $i])) ?>" <?= $i === $pagina ? 'aria-current="page"' : '' ?>><?= $i ?></a><?php endfor; ?>
    </nav>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/includes/pie_pagina.php'; ?>
