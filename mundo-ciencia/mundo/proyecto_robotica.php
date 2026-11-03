<?php
/*
 * ARCHIVO: proyecto_robotica.php
 * PARA QUÉ SIRVE: Muestra integrantes, avances, fotografías y acciones del proyecto de robótica.
 * DÓNDE SE UTILIZA: Detalle de un proyecto del club.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/includes/funciones.php';
// Prepara parámetros o datos usados por esta operación.
$p = robotica((int) ($_GET['id'] ?? 0));
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = usuario_actual();
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = $p['titulo'];
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/includes/encabezado.php'; ?>
<!-- Agrupa un apartado de contenido. Ubicación: Detalle de un proyecto del club. -->
<section class="container"><!-- Enlace de este apartado: abre club_robotica.php. --><a href="<?= e(url('club_robotica.php')) ?>">← Club de Robótica</a>
    <div class="section-heading">
        <div>
            <p class="eyebrow"><?= e($p['equipo']) ?></p>
            <!-- Muestra el título principal de esta pantalla. Ubicación: Detalle de un proyecto del club. -->
            <h1><?= e($p['titulo']) ?></h1>
            <p>Creado por <?= e($p['nombre']) ?></p>
        </div><?php badge($p['estado']);
// Dibuja una etiqueta de rol o estado, como aprobado o pendiente, en tarjetas y paneles.
badge($p['moderacion']); ?>
    </div>
    <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($p['portada']): ?><!-- Muestra la imagen indicada por src y su descripción alternativa. Ubicación: Detalle de un proyecto del club. --><img class="cover" src="<?= e(imagen_url($p['portada'], 'robotica')) ?>"
        alt="<?= e($p['titulo']) ?>"><?php endif; ?><p class="lead prose"><?= nl2br(e($p['descripcion'])) ?></p>
    <!-- Agrupa un apartado de contenido. Ubicación: Detalle de un proyecto del club. -->
    <section class="info-card">
        <!-- Muestra el título de un apartado. Ubicación: Detalle de un proyecto del club. -->
        <h2>Nuestro equipo</h2>
        <p><?= e($p['nombre']) ?>
            (creador)<?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (filas('SELECT u.nombre FROM miembros_robotica m JOIN usuarios u ON u.id=m.usuario_id WHERE proyecto_id=? AND usuario_id<>?', [$p['id'], $p['creador_id']]) as $m)echo ' · ' . e($m['nombre']); ?>
        </p>
        <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($viewer && !miembro_robotica($viewer, $p) && $p['moderacion'] === 'aprobado'): /* Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción. */ formulario('robotica_unirse', 'inline');
    // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
    oculto('id', $p['id']); ?><!-- Ofrece el control de acción de este bloque. Ubicación: Detalle de un proyecto del club. --><button class="btn btn-primary">Unirme al equipo</button></form><?php endif; ?>
        <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($viewer && ((int) $viewer['id'] === (int) $p['creador_id'] || puede_moderar($viewer))): ?><!-- Enlace de este apartado: abre editar_robotica.php. --><a class="btn btn-outline" href="<?= e(url('editar_robotica.php?id=' . $p['id'])) ?>">Editar proyecto</a><?php endif; ?>
        <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if (puede_moderar($viewer)): /* Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción. */ formulario('robotica_moderar');
        // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
        oculto('id', $p['id']); ?><label>Moderación<!-- Permite elegir una opción del campo estado. Ubicación: Detalle de un proyecto del club. --><select name="estado">
                <option value="aprobado">Aprobar / restaurar</option>
                <option value="rechazado">Rechazar</option>
                <option value="oculto">Ocultar</option>
            </select></label><!-- Ofrece el control de acción de este bloque. Ubicación: Detalle de un proyecto del club. --><button class="btn btn-primary">Guardar moderación</button></form><?php endif; ?>
    </section>
    <div class="detail-grid">
        <div>
            <!-- Muestra el título de un apartado. Ubicación: Detalle de un proyecto del club. -->
            <h2>Bitácora del proyecto</h2>
            <div class="timeline">
                <?php $avances = filas('SELECT a.*,u.nombre FROM avances_robotica a JOIN usuarios u ON u.id=a.usuario_id WHERE proyecto_id=? ORDER BY fecha_avance,id', [$p['id']]);
// Prepara $visibles con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
$visibles = 0;
// Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
foreach ($avances as $a):if (!ver_avance($a, $p, $viewer))continue;
    $visibles++; ?><!-- Presenta una tarjeta o registro individual. Ubicación: Detalle de un proyecto del club. --><article class="timeline-item info-card" id="avance-<?= $a['id'] ?>">
                    <p class="eyebrow"><?= e($a['fecha_avance']) ?></p>
                    <!-- Muestra el título de una tarjeta o subsección. Ubicación: Detalle de un proyecto del club. -->
                    <h3><?= e($a['titulo']) ?></h3>
                    <p class="meta"><?= e($a['nombre']) ?></p>
                    <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($a['estado'] !== 'visible')badge($a['estado']); ?><p class="prose">
                        <?= nl2br(e($a['descripcion'])) ?></p>
                    <?php /* Dibuja fotografías con botones para ampliarlas. Se utiliza en proyectos y avances de robótica. */ galeria(filas('SELECT * FROM avances_robotica_imagenes WHERE avance_id=? ORDER BY orden', [$a['id']]), 'robotica'); ?>
                    <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if (puede_moderar($viewer)): /* Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción. */ formulario('avance_moderar');
        // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
        oculto('id', $a['id']); ?><label>Estado del avance<!-- Permite elegir una opción del campo estado. Ubicación: Detalle de un proyecto del club. --><select name="estado">
                            <option value="visible">Visible</option>
                            <option value="oculto">Oculto</option>
                        </select></label><!-- Ofrece el control de acción de este bloque. Ubicación: Detalle de un proyecto del club. --><button class="btn btn-primary">Moderar avance</button></form><?php endif; ?>
                    <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($a['estado'] === 'visible' && $p['moderacion'] === 'aprobado')/* Lista comentarios accesibles y muestra acciones según la cuenta. Añade el formulario de participación si hay sesión. */ comentarios((int) $a['id'], 'avance'); ?>
                </article>
                <?php endforeach;
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if (!$visibles)echo '<div class="empty-state">Cada gran proyecto comienza con un primer intento. Pronto verás aquí sus avances.</div>'; ?>
            </div>
        </div>
        <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($viewer && (miembro_robotica($viewer, $p) || puede_moderar($viewer))): ?><!-- Coloca información y acciones complementarias. Ubicación: Detalle de un proyecto del club. --><aside><?php formulario('avance_guardar');
    // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
    oculto('id', $p['id']); ?><!-- Muestra el título de un apartado. Ubicación: Detalle de un proyecto del club. --><h2>Compartir un avance</h2><label>Título<!-- Permite introducir o enviar el campo titulo. Ubicación: Detalle de un proyecto del club. --><input name="titulo" required
                    maxlength="160"></label><label>Fecha del avance<!-- Permite introducir o enviar el campo fecha_avance. Ubicación: Detalle de un proyecto del club. --><input type="date" name="fecha_avance" required
                    value="<?= date('Y-m-d') ?>"></label><label>¿Qué probaron y aprendieron?<!-- Permite escribir un texto de varias líneas en descripcion. Ubicación: Detalle de un proyecto del club. --><textarea name="descripcion"
                    required maxlength="5000" rows="7"></textarea></label><label>Fotografías<!-- Permite introducir o enviar el campo imagenes[]. Ubicación: Detalle de un proyecto del club. --><input type="file"
                    name="imagenes[]" multiple
                    accept="image/jpeg,image/png,image/webp"></label><small>Hasta 8 imágenes de 5 MB. El avance será revisado.</small><!-- Ofrece el control de acción de este bloque. Ubicación: Detalle de un proyecto del club. --><button
                class="btn btn-primary">Enviar avance</button></form>
        </aside><?php endif; ?>
    </div>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/includes/pie_pagina.php'; ?>
