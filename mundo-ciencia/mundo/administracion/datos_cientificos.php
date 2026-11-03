<?php
/*
 * ARCHIVO: administracion/datos_cientificos.php
 * PARA QUÉ SIRVE: Permite crear, editar y controlar la visibilidad de curiosidades.
 * DÓNDE SE UTILIZA: Administración > Datos científicos; los datos activos alimentan las tarjetas públicas.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = exigir_administrador();
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Datos científicos';
// Consulta datos_cientificos. Curiosidades, categoría, imagen y visibilidad. Alimentan ¿Sabías que? y Datos científicos.
$d = fila('SELECT * FROM datos_cientificos WHERE id=?', [(int) ($_GET['editar'] ?? 0)]);
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/../includes/encabezado_gestion.php'; ?>
<!-- Agrupa un apartado de contenido. Ubicación: Administración > Datos científicos; los datos activos alimentan las tarjetas públicas. -->
<section class="container two-column"><?php formulario('dato_guardar');
// Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
oculto('id', $d['id'] ?? 0); ?><!-- Muestra el título de un apartado. Ubicación: Administración > Datos científicos; los datos activos alimentan las tarjetas públicas. --><h2><?= $d ? 'Editar dato' : 'Nuevo dato científico' ?></h2><label>Título<!-- Permite introducir o enviar el campo titulo. Ubicación: Administración > Datos científicos; los datos activos alimentan las tarjetas públicas. --><input
            name="titulo" required maxlength="160" value="<?= e($d['titulo'] ?? '') ?>"></label><label>Categoría<!-- Permite introducir o enviar el campo categoria. Ubicación: Administración > Datos científicos; los datos activos alimentan las tarjetas públicas. --><input
            name="categoria" required maxlength="80"
            value="<?= e($d['categoria'] ?? '') ?>"></label><label>Explicación<!-- Permite escribir un texto de varias líneas en descripcion. Ubicación: Administración > Datos científicos; los datos activos alimentan las tarjetas públicas. --><textarea name="descripcion" required
            maxlength="5000" rows="8"><?= e($d['descripcion'] ?? '') ?></textarea></label><label>Ilustración o
        fotografía<!-- Permite introducir o enviar el campo imagen. Ubicación: Administración > Datos científicos; los datos activos alimentan las tarjetas públicas. --><input type="file" name="imagen" accept="image/jpeg,image/png,image/webp"></label><label
        class="checkbox"><!-- Permite introducir o enviar el campo activo. Ubicación: Administración > Datos científicos; los datos activos alimentan las tarjetas públicas. --><input type="checkbox" name="activo" <?= ($d['activo'] ?? 1) ? 'checked' : '' ?>> Visible en la
        comunidad</label><!-- Ofrece el control de acción de este bloque. Ubicación: Administración > Datos científicos; los datos activos alimentan las tarjetas públicas. --><button class="btn btn-primary">Guardar dato</button></form>
    <div class="stack"><?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas('SELECT * FROM datos_cientificos ORDER BY id DESC') as $r): ?><!-- Presenta una tarjeta o registro individual. Ubicación: Administración > Datos científicos; los datos activos alimentan las tarjetas públicas. --><article
            class="info-card">
            <!-- Muestra el título de una tarjeta o subsección. Ubicación: Administración > Datos científicos; los datos activos alimentan las tarjetas públicas. -->
            <h3><?= e($r['titulo']) ?></h3>
            <p><?= e($r['descripcion']) ?></p>
            <?php badge($r['activo'] ? 'activo' : 'inactivo'); ?><!-- Enlace del componente: abre el destino que se construye en href. --><a class="btn btn-outline" href="?editar=<?= $r['id'] ?>">Editar</a>
        </article><?php endforeach; ?></div>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/../includes/pie_pagina.php'; ?>
