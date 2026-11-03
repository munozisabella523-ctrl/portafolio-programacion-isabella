<?php
/*
 * ARCHIVO: experimento.php
 * PARA QUÉ SIRVE: Presenta un experimento, sus fotografías, comentarios, notas y acciones permitidas.
 * DÓNDE SE UTILIZA: Detalle que se abre al seleccionar un proyecto.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Prepara $e con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
$e = experimento((int) ($_GET['id'] ?? 0));
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = $e['titulo'];
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = usuario_actual();
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/includes/encabezado.php'; ?>
<!-- Presenta una tarjeta o registro individual. Ubicación: Detalle que se abre al seleccionar un proyecto. -->
<article class="container">
    <!-- Agrupa enlaces o controles de navegación. Ubicación: Detalle que se abre al seleccionar un proyecto. -->
    <nav class="breadcrumb" aria-label="Ruta de navegación"><!-- Enlace de este apartado: abre inicio.php. --><a href="<?= e(url('inicio.php')) ?>">Inicio</a> /
        <!-- Enlace de este apartado: abre explorar.php. -->
        <a href="<?= e(url('explorar.php')) ?>">Experimentos</a> / <?= e($e['titulo']) ?></nav>
    <div class="section-heading">
        <div>
            <p class="eyebrow"><?= e($e['categoria']) ?></p>
            <!-- Muestra el título principal de esta pantalla. Ubicación: Detalle que se abre al seleccionar un proyecto. -->
            <h1><?= e($e['titulo']) ?></h1>
            <div class="author">
                <?php /* Muestra la foto de la cuenta o su inicial si no tiene imagen. Aparece en menú, perfil y comentarios. */ avatar($e); ?><span><?= e($e['autor_nombre']) ?> · <?= e($e['fecha_creacion']) ?></span></div>
        </div><?php badge($e['estado']); ?>
    </div>
    <?php /* Dibuja fotografías con botones para ampliarlas. Se utiliza en proyectos y avances de robótica. */ galeria(filas('SELECT * FROM experimento_imagenes WHERE experimento_id=? ORDER BY orden,id', [$e['id']])); ?>
    <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($e['archivo']): ?><p>
        <!-- Enlace del componente: abre el destino que se construye en href. -->
        <a class="btn btn-outline" href="<?= e(imagen_url($e['archivo'])) ?>">Abrir adjunto original</a></p>
    <?php endif; ?>
    <div class="detail-grid">
        <!-- Agrupa un apartado de contenido. Ubicación: Detalle que se abre al seleccionar un proyecto. -->
        <section class="info-card">
            <!-- Muestra el título de un apartado. Ubicación: Detalle que se abre al seleccionar un proyecto. -->
            <h2>La idea y el experimento</h2>
            <p class="prose"><?= nl2br(e($e['descripcion'])) ?></p>
            <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas('SELECT * FROM reconocimientos WHERE experimento_id=?', [$e['id']]) as $r): ?><div
                class="recognition"><strong>★ <?= e($r['titulo']) ?></strong>
                <p><?= e($r['descripcion']) ?></p>
            </div><?php endforeach; ?>
            <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($viewer): ?><div class="card-actions">
                <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if (puede_editar_experimento($viewer, $e)): ?><!-- Enlace de este apartado: abre gestion_experimentos.php. --><a class="btn btn-primary" href="<?= e(url('gestion_experimentos.php?editar=' . $e['id'])) ?>">Editar proyecto</a><?php endif; ?>
                <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($e['estado'] === 'aprobado'): /* Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción. */ formulario('favorito', 'inline');
    // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
    oculto('id', $e['id']); ?><!-- Ofrece el control de acción de este bloque. Ubicación: Detalle que se abre al seleccionar un proyecto. --><button
                    class="btn btn-outline"><?= valor('SELECT 1 FROM favoritos WHERE usuario_id=? AND experimento_id=?', [$viewer['id'], $e['id']]) ? '★ Quitar favorito' : '☆ Guardar favorito' ?></button>
                </form><?php endif; ?>
                <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if (es_super_admin($viewer) || ((int) $viewer['id'] === (int) $e['usuario_id'] && puede_editar_experimento($viewer, $e))): /* Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción. */ formulario('experimento_eliminar', 'inline');
        // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
        oculto('id', $e['id']); ?><!-- Ofrece el control de acción de este bloque. Ubicación: Detalle que se abre al seleccionar un proyecto. --><button class="btn btn-danger"
                    data-confirm="¿Eliminar el proyecto y sus comentarios y evaluaciones? Esta acción no se puede deshacer.">Eliminar</button>
                </form><?php endif; ?></div><?php endif; ?>
            <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($viewer && ((int) $viewer['id'] === (int) $e['usuario_id'] || puede_moderar($viewer))): ?><!-- Muestra el título de una tarjeta o subsección. Ubicación: Detalle que se abre al seleccionar un proyecto. --><h3>
                Observaciones de revisión</h3>
            <p><?= nl2br(e($e['observacion_moderacion'] ?: 'Sin observaciones todavía.')) ?></p>
            <details>
                <summary>Historial de moderación</summary>
                <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas('SELECT m.*,u.nombre FROM moderaciones m JOIN usuarios u ON u.id=m.administrador_id WHERE experimento_id=? ORDER BY fecha DESC', [$e['id']]) as $m): ?>
                <p><?= e($m['fecha'] . ' · ' . $m['nombre'] . ' · ' . $m['accion']) ?><br><?= e($m['observacion']) ?>
                </p><?php endforeach; ?>
            </details><?php endif; ?>
        </section>
        <!-- Coloca información y acciones complementarias. Ubicación: Detalle que se abre al seleccionar un proyecto. -->
        <aside class="stack">
            <!-- Agrupa un apartado de contenido. Ubicación: Detalle que se abre al seleccionar un proyecto. -->
            <section class="info-card">
                <!-- Muestra el título de un apartado. Ubicación: Detalle que se abre al seleccionar un proyecto. -->
                <h2>Evaluaciones</h2><?php notas((int) $e['id']); ?>
            </section>
            <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($viewer && puede_calificar($viewer, $e)): $n = fila('SELECT * FROM calificaciones WHERE experimento_id=? AND profesor_id=?', [$e['id'], $viewer['id']]);
    // Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción.
    formulario('calificar');
    // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
    oculto('id', $e['id']); ?><!-- Muestra el título de una tarjeta o subsección. Ubicación: Detalle que se abre al seleccionar un proyecto. --><h3>Tu evaluación</h3><label>Nota (<?= NOTA_MIN ?>–<?= NOTA_MAX ?>)<!-- Permite introducir o enviar el campo nota. Ubicación: Detalle que se abre al seleccionar un proyecto. --><input type="number"
                    name="nota" step="0.01" min="<?= NOTA_MIN ?>" max="<?= NOTA_MAX ?>" required
                    value="<?= e($n['nota'] ?? '') ?>"></label><label>Comentario del profesor<!-- Permite escribir un texto de varias líneas en comentario. Ubicación: Detalle que se abre al seleccionar un proyecto. --><textarea name="comentario"
                    required maxlength="3000"><?= e($n['comentario'] ?? '') ?></textarea></label><!-- Ofrece el control de acción de este bloque. Ubicación: Detalle que se abre al seleccionar un proyecto. --><button
                class="btn btn-primary">Guardar evaluación</button></form><?php endif; ?>
            <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if (puede_moderar($viewer)): /* Muestra el formulario de revisión de esta publicación. */ require __DIR__ . '/includes/moderar_form.php';
        // Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción.
        formulario('destacar');
        // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
        oculto('id', $e['id']); ?><!-- Ofrece el control de acción de este bloque. Ubicación: Detalle que se abre al seleccionar un proyecto. --><button
                class="btn btn-outline"><?= $e['destacado'] ? 'Quitar destacado' : '★ Destacar proyecto' ?></button>
            </form>
            <details class="info-card">
                <summary>Otorgar reconocimiento</summary><?php /* Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción. */ formulario('reconocimiento');
        // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
        oculto('id', $e['id']); ?><label>Título<!-- Permite introducir o enviar el campo titulo. Ubicación: Detalle que se abre al seleccionar un proyecto. --><input name="titulo" required
                        maxlength="120"></label><label>Descripción<!-- Permite escribir un texto de varias líneas en descripcion. Ubicación: Detalle que se abre al seleccionar un proyecto. --><textarea name="descripcion" required
                        maxlength="2000"></textarea></label><!-- Ofrece el control de acción de este bloque. Ubicación: Detalle que se abre al seleccionar un proyecto. --><button class="btn btn-primary">Otorgar</button></form>
            </details><?php endif; ?>
        </aside>
    </div>
    <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($e['estado'] === 'aprobado')/* Lista comentarios accesibles y muestra acciones según la cuenta. Añade el formulario de participación si hay sesión. */ comentarios((int) $e['id']); ?>
    <!-- Agrupa un apartado de contenido. Ubicación: Detalle que se abre al seleccionar un proyecto. -->
    <section>
        <!-- Muestra el título de un apartado. Ubicación: Detalle que se abre al seleccionar un proyecto. -->
        <h2>Más ideas para explorar</h2>
        <?php /* Construye las tarjetas de proyectos con foto, título, categoría, autor y estado. Se usa en listados y paneles. */ tarjetas(filas("SELECT e.*,u.nombre autor_nombre FROM experimentos e JOIN usuarios u ON u.id=e.usuario_id WHERE e.estado='aprobado' AND e.categoria=? AND e.id<>? ORDER BY e.fecha_creacion DESC LIMIT 3", [$e['categoria'], $e['id']])); ?>
    </section>
</article><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/includes/pie_pagina.php'; ?>
