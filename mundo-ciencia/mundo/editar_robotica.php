<?php
/*
 * ARCHIVO: editar_robotica.php
 * PARA QUÉ SIRVE: Carga datos para crear o modificar un proyecto de robótica.
 * DÓNDE SE UTILIZA: Formulario de proyectos del Club de Robótica.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = requiere_login();
// Prepara parámetros o datos usados por esta operación.
$p = null;
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if ($id = (int) ($_GET['id'] ?? 0)) {
    // Prepara parámetros o datos usados por esta operación.
    $p = robotica($id);
    // Comprueba los permisos de administración antes de continuar con esta acción.
    if (!puede_moderar($viewer) && (int) $p['creador_id'] !== (int) $viewer['id'])denegar();
}$pageTitle = $p ? 'Editar proyecto de robótica' : 'Un nuevo proyecto de robótica';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/includes/encabezado.php'; ?>
<!-- Agrupa un apartado de contenido. Ubicación: Formulario de proyectos del Club de Robótica. -->
<section class="container reading">
    <!-- Muestra el título principal de esta pantalla. Ubicación: Formulario de proyectos del Club de Robótica. -->
    <h1><?= e($pageTitle) ?></h1><?php formulario('robotica_guardar');
// Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
oculto('id', $p['id'] ?? 0); ?><label>Título<!-- Permite introducir o enviar el campo titulo. Ubicación: Formulario de proyectos del Club de Robótica. --><input name="titulo" required maxlength="160"
            value="<?= e($p['titulo'] ?? '') ?>"></label><label>Nombre del equipo<!-- Permite introducir o enviar el campo equipo. Ubicación: Formulario de proyectos del Club de Robótica. --><input name="equipo" required
            maxlength="120" value="<?= e($p['equipo'] ?? '') ?>"></label><label>Descripción<!-- Permite escribir un texto de varias líneas en descripcion. Ubicación: Formulario de proyectos del Club de Robótica. --><textarea name="descripcion"
            required maxlength="5000" rows="8"><?= e($p['descripcion'] ?? '') ?></textarea></label><label>Etapa<!-- Permite elegir una opción del campo estado. Ubicación: Formulario de proyectos del Club de Robótica. --><select
            name="estado"><?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (['idea', 'prototipo', 'programacion', 'pruebas', 'terminado'] as $s): ?><option
                <?= ($p['estado'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?></select></label><label>Portada<!-- Permite introducir o enviar el campo portada. Ubicación: Formulario de proyectos del Club de Robótica. --><input type="file" name="portada"
            accept="image/jpeg,image/png,image/webp"></label>
    <p class="meta">El proyecto y sus cambios se revisan antes de mostrarse públicamente.</p><!-- Ofrece el control de acción de este bloque. Ubicación: Formulario de proyectos del Club de Robótica. --><button
        class="btn btn-primary">Enviar para revisión</button></form>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/includes/pie_pagina.php'; ?>
