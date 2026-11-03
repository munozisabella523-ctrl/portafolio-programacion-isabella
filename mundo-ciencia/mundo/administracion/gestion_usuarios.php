<?php
/*
 * ARCHIVO: administracion/gestion_usuarios.php
 * PARA QUÉ SIRVE: Permite crear, editar y desactivar cuentas; los roles disponibles dependen del administrador.
 * DÓNDE SE UTILIZA: Administración > Usuarios.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = exigir_administrador();
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Gestión de usuarios';
// Prepara la cuenta que se va a editar o indica que se creará una nueva.
$edit = null;
// Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
if ($id = (int) ($_GET['edit'] ?? 0)) {
    // Consulta usuarios. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
    $edit = fila('SELECT id,nombre,usuario,correo,rol,estado FROM usuarios WHERE id=?', [$id]);
    // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
    if (!$edit)denegar(404);
    // Reserva esta decisión a los permisos de superadministrador.
    if (!es_super_admin($viewer) && $edit['rol'] !== 'estudiante')denegar();
}
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require __DIR__ . '/../includes/encabezado_gestion.php'; ?>
<!-- Agrupa un apartado de contenido. Ubicación: Administración > Usuarios. -->
<section class="container two-column">
    <!-- Agrupa campos que se envían al pulsar su botón. Ubicación: Administración > Usuarios. -->
    <form class="form-card" action="<?= e(url('controllers/gestion_administracion.php')) ?>" method="post"><?php campo_csrf();
// Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
oculto('action', 'guardar_usuario');
// Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
oculto('id', $edit['id'] ?? 0); ?><!-- Muestra el título de un apartado. Ubicación: Administración > Usuarios. --><h2><?= $edit ? 'Editar cuenta' : 'Crear cuenta' ?></h2>
        <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (['nombre' => 'Nombre', 'usuario' => 'Usuario', 'correo' => 'Correo'] as $k => $label): ?><label><?= $label ?><!-- Permite introducir o enviar el campo          . Ubicación: Administración > Usuarios. --><input
                name="<?= $k ?>" type="<?= $k === 'correo' ? 'email' : 'text' ?>" value="<?= e($edit[$k] ?? '') ?>"
                maxlength="<?= $k === 'usuario' ? 60 : 120 ?>" required></label><?php endforeach; ?>
        <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if (!$edit): ?><label>Contraseña inicial<!-- Permite introducir o enviar el campo contrasena. Ubicación: Administración > Usuarios. --><input type="password" name="contrasena" minlength="8" required
                autocomplete="new-password"></label><?php endif; ?>
        <label>Rol<!-- Permite elegir una opción del campo rol. Ubicación: Administración > Usuarios. --><select name="rol"><?php foreach (es_super_admin($viewer) ? ROLES : ['estudiante'] as $r): ?><option
                    value="<?= $r ?>" <?= ($edit['rol'] ?? '') === $r ? 'selected' : '' ?>>
                    <?= e(ucfirst(str_replace('_', ' ', $r))) ?></option>
                <?php endforeach; ?></select></label><label>Estado<!-- Permite elegir una opción del campo estado. Ubicación: Administración > Usuarios. --><select
                name="estado"><?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach (['activo', 'inactivo'] as $s): ?><option
                    <?= ($edit['estado'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?></select></label><!-- Ofrece el control de acción de este bloque. Ubicación: Administración > Usuarios. --><button class="btn btn-primary">Guardar cuenta</button>
    </form>
    <!-- Agrupa un apartado de contenido. Ubicación: Administración > Usuarios. -->
    <section>
        <!-- Agrupa campos que se envían al pulsar su botón. Ubicación: Administración > Usuarios. -->
        <form method="get" class="search-form"><label>Buscar personas<!-- Permite introducir o enviar el campo q. Ubicación: Administración > Usuarios. --><input type="search" name="q"
                    value="<?= e($_GET['q'] ?? '') ?>" placeholder="Nombre, usuario o correo"></label><!-- Ofrece el control de acción de este bloque. Ubicación: Administración > Usuarios. --><button
                class="btn btn-outline">Buscar</button></form>
        <div class="stack">
            <?php $like = '%' . mb_substr($_GET['q'] ?? '', 0, 120) . '%';
// Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
foreach (filas('SELECT id,nombre,usuario,correo,rol,estado FROM usuarios WHERE nombre LIKE ? OR usuario LIKE ? OR correo LIKE ? ORDER BY id DESC LIMIT 100', [$like, $like, $like]) as $u): ?>
            <!-- Presenta una tarjeta o registro individual. Ubicación: Administración > Usuarios. -->
            <article class="info-card">
                <!-- Muestra el título de una tarjeta o subsección. Ubicación: Administración > Usuarios. -->
                <h3><?= e($u['nombre']) ?></h3>
                <p><?= e($u['usuario'] . ' · ' . $u['correo']) ?></p><?php badge($u['rol']);
    // Dibuja una etiqueta de rol o estado, como aprobado o pendiente, en tarjetas y paneles.
    badge($u['estado']);
    // Reserva esta decisión a los permisos de superadministrador.
    if (es_super_admin($viewer) || $u['rol'] === 'estudiante'): ?><div class="card-actions">
                    <!-- Enlace del componente: abre el destino que se construye en href. -->
                    <a class="btn btn-outline" href="?edit=<?= $u['id'] ?>">Editar</a><?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ((int) $u['id'] !== (int) $viewer['id'] && $u['estado'] === 'activo'): ?>
                    <!-- Agrupa campos que se envían al pulsar su botón. Ubicación: Administración > Usuarios. -->
                    <form method="post" action="<?= e(url('controllers/gestion_administracion.php')) ?>"><?php campo_csrf();
        // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
        oculto('action', 'eliminar_usuario');
        // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
        oculto('id', $u['id']); ?><!-- Ofrece el control de acción de este bloque. Ubicación: Administración > Usuarios. --><button class="btn btn-danger"
                            data-confirm="¿Desactivar esta cuenta? Se conservarán sus proyectos y evaluaciones.">Desactivar</button>
                    </form><?php endif; ?></div><?php endif; ?>
            </article><?php endforeach; ?></div>
    </section>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require __DIR__ . '/../includes/pie_pagina.php'; ?>
