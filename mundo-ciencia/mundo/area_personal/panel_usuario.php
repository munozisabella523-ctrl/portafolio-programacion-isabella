<?php
/*
 * ARCHIVO: area_personal/panel_usuario.php
 * PARA QUÉ SIRVE: Comprueba la sesión y redirige al panel principal.
 * DÓNDE SE UTILIZA: Ruta de entrada al panel después de iniciar sesión.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';
// Exige una cuenta activa; si no hay sesión, dirige a Iniciar sesión.
requiere_login();
// Construye una dirección interna y redirige hacia ella. Se usa al terminar formularios o comprobar permisos.
ir('dashboard.php');
