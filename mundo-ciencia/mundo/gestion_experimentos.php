<?php
/*
 * ARCHIVO: gestion_experimentos.php
 * PARA QUÉ SIRVE: Permite crear, editar y gestionar experimentos propios según sus permisos.
 * DÓNDE SE UTILIZA: Mis proyectos y formularios de publicación.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = usuario_actual();
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if (!$viewer)ir('explorar.php');
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Mis proyectos';
// Prepara la cuenta que se va a editar o indica que se creará una nueva.
$edit = null;
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if ($id = (int) ($_GET['editar'] ?? 0)) {
    // Prepara la cuenta que se va a editar o indica que se creará una nueva.
    $edit = experimento($id);
    // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
    if (!puede_editar_experimento($viewer, $edit))denegar();
}
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/includes/encabezado.php'; ?>
<!-- Agrupa un apartado de contenido. Ubicación: Mis proyectos y formularios de publicación. -->
<section class="container">
    <p class="eyebrow">DE LA PREGUNTA AL DESCUBRIMIENTO</p>
    <!-- Muestra el título principal de esta pantalla. Ubicación: Mis proyectos y formularios de publicación. -->
    <h1><?= $edit ? 'Dale forma a tu idea' : 'Mis proyectos' ?></h1>
    <div class="two-column">
        <div><?php /* Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción. */ formulario('experimento_guardar');
// Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
oculto('id', $edit['id'] ?? 0); ?><!-- Muestra el título de un apartado. Ubicación: Mis proyectos y formularios de publicación. --><h2><?= $edit ? 'Editar proyecto' : 'Crear un proyecto' ?></h2>
            <p class="meta">Tu proyecto será revisado antes de aparecer en la comunidad.</p>
            <label>Título<!-- Permite introducir o enviar el campo titulo. Ubicación: Mis proyectos y formularios de publicación. --><input name="titulo" maxlength="160" required
                    value="<?= e($edit['titulo'] ?? '') ?>"></label><label>Categoría<!-- Permite introducir o enviar el campo categoria. Ubicación: Mis proyectos y formularios de publicación. --><input name="categoria"
                    list="categorias" maxlength="80" required
                    value="<?= e($edit['categoria'] ?? '') ?>"></label><datalist
                id="categorias"><?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (['Biología', 'Física', 'Química', 'Astronomía', 'Tecnología', 'Medio ambiente', 'Robótica'] as $c): ?>
                <option><?= $c ?></option><?php endforeach; ?></datalist>
            <label>Descripción, materiales, procedimiento y resultados<!-- Permite escribir un texto de varias líneas en descripcion. Ubicación: Mis proyectos y formularios de publicación. --><textarea name="descripcion" required
                    maxlength="5000"
                    rows="9"><?= e($edit['descripcion'] ?? '') ?></textarea></label><label>Fotografías<!-- Permite introducir o enviar el campo imagenes[]. Ubicación: Mis proyectos y formularios de publicación. --><input type="file"
                    name="imagenes[]" accept="image/jpeg,image/png,image/webp"
                    multiple></label><small>Hasta 8 imágenes por envío · 5 MB por imagen.</small><label>Guardar
                como<!-- Permite elegir una opción del campo estado. Ubicación: Mis proyectos y formularios de publicación. --><select name="estado">
                    <option value="pendiente">Enviar para revisión</option>
                    <option value="borrador" <?= ($edit['estado'] ?? '') === 'borrador' ? 'selected' : '' ?>>Borrador
                    </option>
                </select></label><!-- Ofrece el control de acción de este bloque. Ubicación: Mis proyectos y formularios de publicación. --><button class="btn btn-primary">Guardar proyecto →</button></form>
            <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($edit): /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas('SELECT * FROM experimento_imagenes WHERE experimento_id=?', [$edit['id']]) as $i): ?>
            <div class="comment-card"><!-- Muestra la imagen indicada por src y su descripción alternativa. Ubicación: Mis proyectos y formularios de publicación. --><img class="thumb" src="<?= e(imagen_url($i['archivo'])) ?>"
                    alt="Fotografía actual"><?php /* Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción. */ formulario('imagen_eliminar', 'inline');
    // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
    oculto('id', $i['id']); ?><!-- Ofrece el control de acción de este bloque. Ubicación: Mis proyectos y formularios de publicación. --><button class="btn btn-outline" data-confirm="¿Quitar esta imagen del proyecto?">Quitar
                    imagen</button></form>
            </div><?php endforeach; endif; ?>
        </div>
        <div class="stack">
            <!-- Muestra el título de un apartado. Ubicación: Mis proyectos y formularios de publicación. -->
            <h2>Continúa trabajando</h2><?php $lista = filas('SELECT e.*,u.nombre autor_nombre FROM experimentos e JOIN usuarios u ON u.id=e.usuario_id WHERE e.usuario_id=? ORDER BY fecha_actualizacion DESC', [$viewer['id']]);
// Muestra el estado vacío cuando no hay registros para este apartado.
if (!$lista): ?><div class="empty-state">
                <!-- Muestra el título de una tarjeta o subsección. Ubicación: Mis proyectos y formularios de publicación. -->
                <h3>No tienes proyectos todavía 🔬</h3>
                <p>Tu próxima gran idea puede empezar hoy. Completa el formulario para crear tu primer proyecto.</p>
            </div><?php endif;
// Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
foreach ($lista as $e): ?><!-- Presenta una tarjeta o registro individual. Ubicación: Mis proyectos y formularios de publicación. --><article class="info-card"><?php badge($e['estado']); ?><!-- Muestra el título de una tarjeta o subsección. Ubicación: Mis proyectos y formularios de publicación. --><h3>
                    <!-- Enlace de este apartado: abre experimento.php. -->
                    <a href="<?= e(url('experimento.php?id=' . $e['id'])) ?>"><?= e($e['titulo']) ?></a></h3>
                <p><?= e($e['observacion_moderacion']) ?></p>
                <div class="card-actions">
                    <!-- Enlace de este apartado: abre experimento.php. -->
                    <a class="btn btn-outline" href="<?= e(url('experimento.php?id=' . $e['id'])) ?>">Ver proyecto</a><?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if (puede_editar_experimento($viewer, $e)): ?><!-- Enlace del componente: abre el destino que se construye en href. --><a class="btn btn-primary" href="?editar=<?= $e['id'] ?>">Editar</a><?php endif; ?>
                </div>
            </article><?php endforeach; ?>
        </div>
    </div>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/includes/pie_pagina.php'; ?>
