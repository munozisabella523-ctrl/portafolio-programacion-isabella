<!-- Agrupa un apartado de contenido. Ubicación: Acciones de moderación en el detalle y en Contenido pendiente. -->
<section class="info-card moderation">
    <!-- Muestra el título de una tarjeta o subsección. Ubicación: Acciones de moderación en el detalle y en Contenido pendiente. -->
    <h3>Revisar proyecto</h3><?php /*
 * ARCHIVO: includes/moderar_form.php
 * PARA QUÉ SIRVE: Construye el formulario de revisión de un experimento.
 * DÓNDE SE UTILIZA: Acciones de moderación en el detalle y en Contenido pendiente.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
formulario('moderar');
// Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
oculto('id', $e['id']);
// Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
oculto('estado', 'aprobado'); ?><!-- Ofrece el control de acción de este bloque. Ubicación: Acciones de moderación en el detalle y en Contenido pendiente. --><button class="btn btn-primary">Aprobar proyecto</button></form>
    <div class="card-actions">
        <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (['cambios_solicitados' => 'Solicitar cambios', 'rechazado' => 'Rechazar', 'oculto' => 'Ocultar'] as $estado => $etiqueta): ?><!-- Ofrece el control de acción de este bloque. Ubicación: Acciones de moderación en el detalle y en Contenido pendiente. --><button
            class="btn btn-outline" type="button"
            data-dialog="mod-<?= $e['id'] . '-' . $estado ?>"><?= $etiqueta ?></button>
        <!-- Define una ventana que JavaScript abre sobre la página. Ubicación: Acciones de moderación en el detalle y en Contenido pendiente. -->
        <dialog class="modal" id="mod-<?= $e['id'] . '-' . $estado ?>"><?php formulario('moderar');
    // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
    oculto('id', $e['id']);
    // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
    oculto('estado', $estado); ?><!-- Muestra el título de una tarjeta o subsección. Ubicación: Acciones de moderación en el detalle y en Contenido pendiente. --><h3><?= $etiqueta ?></h3><label>Motivo u observación<!-- Permite escribir un texto de varias líneas en observacion. Ubicación: Acciones de moderación en el detalle y en Contenido pendiente. --><textarea name="observacion"
                    required maxlength="3000"></textarea></label>
            <div class="card-actions"><!-- Ofrece el control de acción de este bloque. Ubicación: Acciones de moderación en el detalle y en Contenido pendiente. --><button class="btn btn-primary">Confirmar</button><!-- Ofrece el control de acción de este bloque. Ubicación: Acciones de moderación en el detalle y en Contenido pendiente. --><button class="btn btn-outline"
                    type="button" data-close>Cancelar</button></div>
            </form>
        </dialog><?php endforeach; ?>
    </div>
</section>
