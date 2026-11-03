<?php
/*
 * ARCHIVO: administracion/comentarios.php
 * PARA QUÉ SIRVE: Lista comentarios y ofrece las acciones de moderación.
 * DÓNDE SE UTILIZA: Administración > Comentarios.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = exigir_administrador();
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Moderación de comentarios';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/../includes/encabezado_gestion.php'; ?>
<!-- Agrupa un apartado de contenido. Ubicación: Administración > Comentarios. -->
<section class="container">
    <div class="stack"><?php $lista = filas('SELECT c.*,u.nombre FROM comentarios c JOIN usuarios u ON u.id=c.usuario_id ORDER BY c.id DESC LIMIT 200');
// Muestra el estado vacío cuando no hay registros para este apartado.
if (!$lista)echo '<div class="empty-state">Todavía no hay comentarios.</div>';
// Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
foreach ($lista as $c): ?><!-- Presenta una tarjeta o registro individual. Ubicación: Administración > Comentarios. --><article class="comment-card"><strong><?= e($c['nombre']) ?></strong>
            <p><?= e($c['comentario']) ?></p><?php badge($c['estado']);
    // Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción.
    formulario('comentario_moderar');
    // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
    oculto('id', $c['id']); ?><label>Estado<!-- Permite elegir una opción del campo estado. Ubicación: Administración > Comentarios. --><select
                    name="estado"><?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (['visible', 'pendiente', 'oculto'] as $s): ?><option
                        <?= $s === $c['estado'] ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?></select></label><!-- Ofrece el control de acción de este bloque. Ubicación: Administración > Comentarios. --><button class="btn btn-primary">Actualizar estado</button>
            </form>
        </article><?php endforeach; ?></div>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/../includes/pie_pagina.php'; ?>
