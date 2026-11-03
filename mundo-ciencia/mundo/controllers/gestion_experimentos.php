<?php
/*
 * ARCHIVO: controllers/gestion_experimentos.php
 * PARA QUÉ SIRVE: Traduce nombres de acciones anteriores y las envía al controlador común.
 * DÓNDE SE UTILIZA: Compatibilidad interna de formularios de experimentos; no es una pantalla.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Compatibilidad de rutas; todas las acciones pasan por permisos y CSRF centralizados.
// Relaciona las acciones antiguas con las acciones del controlador común.
$mapa = ['guardar_experimento' => 'experimento_guardar', 'actualizar_experimento' => 'experimento_guardar', 'eliminar_experimento' => 'experimento_eliminar'];
$_POST['accion'] = $mapa[$_POST['action'] ?? $_GET['action'] ?? ''] ?? '';
// Carga las funciones o la conexión compartida que necesita este archivo.
require __DIR__ . '/acciones.php';
