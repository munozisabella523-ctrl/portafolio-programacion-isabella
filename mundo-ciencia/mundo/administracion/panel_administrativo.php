<?php
/*
 * ARCHIVO: administracion/panel_administrativo.php
 * PARA QUÉ SIRVE: Da acceso al panel reservado a administradores.
 * DÓNDE SE UTILIZA: Administración en el menú del usuario.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';
// Permite continuar solo a administradores y superadministradores. Se usa en Administración.
exigir_administrador();
// Construye una dirección interna y redirige hacia ella. Se usa al terminar formularios o comprobar permisos.
ir('dashboard.php');
