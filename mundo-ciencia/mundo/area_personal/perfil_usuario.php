<?php
/*
 * ARCHIVO: area_personal/perfil_usuario.php
 * PARA QUÉ SIRVE: Muestra datos personales, formularios de actualización y las notas de la cuenta.
 * DÓNDE SE UTILIZA: Mi perfil y Configuración del menú del usuario.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = exigir_sesion();
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Mi perfil';
// Conserva el identificador de contexto utilizado por esta pantalla.
$panelType = 'usuario';
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require_once __DIR__ . '/../includes/encabezado_gestion.php';
?>
<!-- Presenta el avatar y el resumen de la cuenta. Ubicación: Mi perfil y Configuración del menú del usuario. -->
<section class="container profile-header"><?php avatar($viewer); ?><div>
        <!-- Muestra el título principal de esta pantalla. Ubicación: Mi perfil y Configuración del menú del usuario. -->
        <h1><?= e($viewer['nombre']) ?></h1>
        <p><?= e(str_replace('_', ' ', $viewer['rol'])) ?> · En la comunidad desde <?= e($viewer['fecha_registro']) ?>
        </p>
        <p><?= (int) valor('SELECT COUNT(*) FROM experimentos WHERE usuario_id=?', [$viewer['id']]) ?> proyectos ·
            <?= (int) valor("SELECT COUNT(*) FROM experimentos WHERE usuario_id=? AND estado='aprobado'", [$viewer['id']]) ?>
            aprobados · <?= (int) valor('SELECT COUNT(*) FROM avances_robotica WHERE usuario_id=?', [$viewer['id']]) ?>
            avances de robótica</p>
    </div>
</section>
<!-- Agrupa un apartado de contenido. Ubicación: Mi perfil y Configuración del menú del usuario. -->
<section class="container" id="configuracion">
    <div class="two-column gestion-form-grid">
        <!-- Agrupa campos que se envían al pulsar su botón. Ubicación: Mi perfil y Configuración del menú del usuario. -->
        <form class="form-card" action="../controllers/gestion_sesion.php?action=actualizar_perfil" method="post"
            enctype="multipart/form-data"><?php /* Inserta el token en un campo oculto del formulario; no es visible en pantalla. */ campo_csrf(); ?>
            <!-- Muestra el título de un apartado. Ubicación: Mi perfil y Configuración del menú del usuario. -->
            <h2>Datos personales</h2><label>Foto de perfil<!-- Permite introducir o enviar el campo avatar. Ubicación: Mi perfil y Configuración del menú del usuario. --><input type="file" name="avatar"
                    accept="image/jpeg,image/png,image/webp"></label>
            <label>Nombre
                <!-- Permite introducir o enviar el campo nombre. Ubicación: Mi perfil y Configuración del menú del usuario. -->
                <input type="text" name="nombre" value="<?= e($viewer['nombre']) ?>" required>
            </label>
            <label>Usuario
                <!-- Permite introducir o enviar el campo usuario. Ubicación: Mi perfil y Configuración del menú del usuario. -->
                <input type="text" name="usuario" value="<?= e($viewer['usuario']) ?>" required>
            </label>
            <label>Correo
                <!-- Permite introducir o enviar el campo correo. Ubicación: Mi perfil y Configuración del menú del usuario. -->
                <input type="email" name="correo" value="<?= e($viewer['correo']) ?>" required>
            </label>
            <!-- Ofrece el control de acción de este bloque. Ubicación: Mi perfil y Configuración del menú del usuario. -->
            <button class="btn btn-primary" type="submit">Guardar cambios</button>
        </form>
        <!-- Agrupa campos que se envían al pulsar su botón. Ubicación: Mi perfil y Configuración del menú del usuario. -->
        <form class="form-card" action="../controllers/gestion_sesion.php?action=cambiar_contrasena" method="post"
            enctype="multipart/form-data"><?php /* Inserta el token en un campo oculto del formulario; no es visible en pantalla. */ campo_csrf(); ?>
            <!-- Muestra el título de un apartado. Ubicación: Mi perfil y Configuración del menú del usuario. -->
            <h2>Cambiar contraseña</h2>
            <label>Contraseña actual
                <!-- Permite introducir o enviar el campo actual. Ubicación: Mi perfil y Configuración del menú del usuario. -->
                <input type="password" name="actual" required>
            </label>
            <label>Nueva contrasena
                <!-- Permite introducir o enviar el campo nueva. Ubicación: Mi perfil y Configuración del menú del usuario. -->
                <input type="password" name="nueva" required minlength="8">
            </label>
            <label>Confirmar nueva contrasena
                <!-- Permite introducir o enviar el campo confirmar. Ubicación: Mi perfil y Configuración del menú del usuario. -->
                <input type="password" name="confirmar" required minlength="8">
            </label>
            <!-- Ofrece el control de acción de este bloque. Ubicación: Mi perfil y Configuración del menú del usuario. -->
            <button class="btn btn-primary" type="submit">Actualizar contrasena</button>
        </form>
    </div>
</section>
<!-- Agrupa un apartado de contenido. Ubicación: Mi perfil y Configuración del menú del usuario. -->
<section class="container">
    <!-- Muestra el título de un apartado. Ubicación: Mi perfil y Configuración del menú del usuario. -->
    <h2><?= $viewer['rol'] === 'profesor' ? 'Mis evaluaciones' : 'Mis calificaciones' ?></h2>
    <?php $campo = $viewer['rol'] === 'profesor' ? 'profesor_id' : 'estudiante_id';
// Consulta calificaciones. Notas y comentarios del profesor vinculados a proyecto y estudiante. Se ven en Evaluaciones y Mi perfil.
$notas = filas("SELECT c.*,e.titulo,u.nombre profesor FROM calificaciones c JOIN experimentos e ON e.id=c.experimento_id JOIN usuarios u ON u.id=c.profesor_id WHERE c.$campo=? ORDER BY c.fecha_actualizacion DESC", [$viewer['id']]);
// Muestra el estado vacío cuando no hay registros para este apartado.
if (!$notas): ?><div class="empty-state">Todavía no hay evaluaciones para mostrar.</div><?php endif;
// Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
foreach ($notas as $n): ?><!-- Presenta una tarjeta o registro individual. Ubicación: Mi perfil y Configuración del menú del usuario. --><article class="comment-card">
        <!-- Muestra el título de una tarjeta o subsección. Ubicación: Mi perfil y Configuración del menú del usuario. -->
        <h3><!-- Enlace de este apartado: abre experimento.php. --><a href="<?= e(url('experimento.php?id=' . $n['experimento_id'])) ?>"><?= e($n['titulo']) ?></a></h3>
        <strong><?= e($n['nota']) ?> / <?= NOTA_MAX ?></strong>
        <p><?= e($n['profesor'] . ' · ' . $n['comentario']) ?></p><small><?= e($n['fecha_actualizacion']) ?></small>
    </article><?php endforeach; ?>
</section><?php /* Inserta el footer con contactos y carga las interacciones. */ require_once __DIR__ . '/../includes/pie_pagina_gestion.php'; ?>
