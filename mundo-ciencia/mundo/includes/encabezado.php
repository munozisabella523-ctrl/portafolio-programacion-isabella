<?php
/*
 * ARCHIVO: includes/encabezado.php
 * PARA QUÉ SIRVE: Construye el documento HTML, carga el CSS y muestra el menú según la sesión.
 * DÓNDE SE UTILIZA: Encabezado y navegación superior compartidos por las páginas.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = usuario_actual();
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = $pageTitle ?? 'Mundo Ciencia';
// Nombre del archivo abierto: permite elegir el fondo de cada apartado.
$paginaActual = basename($_SERVER['SCRIPT_NAME']);
$fondos = [
    'explorar.php' => ['saturno', 'Experimentos'],
    'club_robotica.php' => ['marte', 'Club de Robótica'],
    'datos.php' => ['galaxia', '¿Sabías que…?'],
    'feria.php' => ['tierra', 'Nuestra feria científica'],
    'dashboard.php' => ['tierra', 'Mi panel'],
    'gestion_experimentos.php' => ['saturno', 'Mis proyectos']
];
// Estas pantallas utilizan su propia distribución, con imagen al costado.
if ($paginaActual === 'registro_usuario.php') $bodyClass = 'acceso-espacial registro-compacto';
if ($paginaActual === 'inicio_sesion.php') $bodyClass = 'acceso-espacial';
if ($paginaActual === 'juego.php') $bodyClass = 'juego-espacial';
?>
<!doctype html>
<html lang="es">

    <head>
        <!-- Configura los metadatos del documento para el navegador. Ubicación: Encabezado y navegación superior compartidos por las páginas. -->
        <meta charset="utf-8">
        <!-- Configura los metadatos del documento para el navegador. Ubicación: Encabezado y navegación superior compartidos por las páginas. -->
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <!-- Configura los metadatos del documento para el navegador. Ubicación: Encabezado y navegación superior compartidos por las páginas. -->
        <meta name="theme-color" content="#245c78"><title><?= e($pageTitle) ?> · Mundo Ciencia</title>
        <?php
        // Carga los CSS separados en el mismo orden para conservar el diseño.
        $hojasCSS = ['general', 'componentes', 'adaptable', 'inicio', 'footer', 'juego', 'espacio', 'estilos_mundo_ciencia'];
        $hojasCSS[] = 'chat_inicio';
        foreach ($hojasCSS as $hojaCSS):
            // La versión cambia al guardar contenido diferente: evita usar CSS antiguo.
            $versionCSS = md5_file(__DIR__ . '/../css/' . $hojaCSS . '.css');
        ?>
            <link rel="stylesheet" href="<?= e(url('css/' . $hojaCSS . '.css?v=' . $versionCSS)) ?>">
        <?php endforeach; ?>
    </head>

    <body class="<?= e($bodyClass ?? '') ?>"><!-- Acceso para teclado: salta el menú y lleva al contenido principal. --><a class="skip" href="#contenido">Saltar al contenido</a>
        <!-- Muestra el logo y el menú superior compartido. Ubicación: Encabezado y navegación superior compartidos por las páginas. -->
        <header class="site-header">
            <!-- Logo de navegación: permite volver al inicio del sitio. -->
            <a class="brand" href="<?= e(url('inicio.php')) ?>"><span class="brand-mark">⚛</span><span>Mundo<span class="brand-green">Ciencia</span></span></a>
            <!-- Ofrece el control de acción de este bloque. Ubicación: Encabezado y navegación superior compartidos por las páginas. -->
            <button class="menu-toggle btn btn-outline" aria-label="Abrir navegación" aria-expanded="false"
                aria-controls="navegacion">☰</button>
            <!-- Agrupa enlaces o controles de navegación. Ubicación: Encabezado y navegación superior compartidos por las páginas. -->
            <nav class="top-nav" id="navegacion" aria-label="Navegación principal">
                <!-- Enlace de este apartado: abre inicio.php. -->
                <a href="<?= e(url('inicio.php')) ?>">Inicio</a><!-- Enlace de este apartado: abre explorar.php. --><a href="<?= e(url('explorar.php')) ?>">Experimentos</a>
                <!-- Abre el único juego de la página, disponible sin iniciar sesión. -->
                <a href="<?= e(url('juego.php')) ?>">Juego</a>
                <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if (!$viewer): ?><!-- Enlace de este apartado: abre feria.php. --><a href="<?= e(url('feria.php')) ?>">Feria de Ciencia</a><?php else: ?><!-- Enlace de este apartado: abre gestion_experimentos.php. --><a href="<?= e(url('gestion_experimentos.php')) ?>">Mis proyectos</a><?php endif; ?>
                <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($viewer): ?><!-- Enlace de este apartado: abre club_robotica.php. --><a href="<?= e(url('club_robotica.php')) ?>">Robótica</a><?php endif; ?>
                <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($viewer): ?><!-- Enlace de este apartado: abre notificaciones.php. --><a class="notification-link" href="<?= e(url('notificaciones.php')) ?>" aria-label="Notificaciones">♧ <span class="badge"><?= (int) valor('SELECT COUNT(*) FROM notificaciones WHERE usuario_id=? AND leida=0', [$viewer['id']]) ?></span></a>
                <details class="user-menu">
                    <summary><?php /* Muestra la foto de la cuenta o su inicial si no tiene imagen. Aparece en menú, perfil y comentarios. */ avatar($viewer); ?><span><?= e($viewer['nombre']) ?></span></summary>
                    <div>
                        <!-- Enlace de este apartado: abre area_personal/perfil_usuario.php. -->
                        <a href="<?= e(url('area_personal/perfil_usuario.php')) ?>">Mi perfil</a><!-- Enlace de este apartado: abre area_personal/perfil_usuario.php. --><a href="<?= e(url('area_personal/perfil_usuario.php#configuracion')) ?>">Configuración</a><!-- Enlace de este apartado: abre dashboard.php. --><a href="<?= e(url('dashboard.php')) ?>">Mi panel</a>
                        <!-- Agrupa campos que se envían al pulsar su botón. Ubicación: Encabezado y navegación superior compartidos por las páginas. -->
                        <form method="post" action="<?= e(url('controllers/gestion_sesion.php')) ?>"><?php campo_csrf();
    // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
    oculto('action', 'cerrar_sesion'); ?><!-- Ofrece el control de acción de este bloque. Ubicación: Encabezado y navegación superior compartidos por las páginas. --><button class="btn btn-outline">Cerrar sesión</button></form>
                    </div>
                </details>
                <?php else: ?><!-- Enlace de este apartado: abre inicio_sesion.php. --><a class="btn btn-outline" href="<?= e(url('inicio_sesion.php')) ?>">Iniciar sesión</a><!-- Enlace de este apartado: abre registro_usuario.php. --><a class="btn btn-primary" href="<?= e(url('registro_usuario.php')) ?>">Registrarme →</a><?php endif; ?>
            </nav>
        </header>
        <!-- Delimita el contenido principal. Ubicación: Encabezado y navegación superior compartidos por las páginas. -->
        <main id="contenido">
            <?php if (isset($fondos[$paginaActual])): ?>
                <!-- Portada panorámica de este apartado; los datos siguen debajo. -->
                <section class="cabecera-espacial fondo-<?= e($fondos[$paginaActual][0]) ?>">
                    <h1><?= e($fondos[$paginaActual][1]) ?></h1>
                </section>
            <?php endif; ?>
            <?php /* Muestra este bloque solo cuando se cumple la condición indicada. */ if ($flash = obtener_mensaje_temporal()): ?><div role="status" class="flash <?= e($flash['type']) ?>">
                <?= e($flash['message']) ?></div><?php endif; ?>

