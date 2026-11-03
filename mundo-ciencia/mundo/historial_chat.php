<?php
/* ARCHIVO: historial_chat.php
 * CONSULTA DEL HISTORIAL
 * Primero comprueba sesión y permisos. Sin id muestra la lista; con id muestra los mensajes.
 * Ambas vistas tienen paginación y escapan el texto antes de mostrarlo.
 */
require_once __DIR__ . '/includes/chat_historial.php';
$viewer = requiere_login();
[$condicionConversaciones, $parametrosConversaciones] = chat_filtro_panel($viewer);
$conversacionId = max(0, (int) ($_GET['id'] ?? 0));
$conversacion = null;
if ($conversacionId) {
    $conversacion = fila("SELECT c.*,u.nombre FROM chat_conversaciones c LEFT JOIN usuarios u ON u.id=c.usuario_id WHERE c.id=? AND ($condicionConversaciones)", [$conversacionId, ...$parametrosConversaciones]);
    if (!$conversacion) denegar(404);
}
$pageTitle = 'Historial del asistente';
require __DIR__ . '/includes/encabezado_gestion.php';
?>
<section class="container">
<?php if ($conversacion):
    $total = (int) valor('SELECT COUNT(*) FROM chat_mensajes WHERE conversacion_id=?', [$conversacionId]);
    $paginas = max(1, (int) ceil($total / 100));
    $pagina = min($paginas, max(1, (int) ($_GET['pagina'] ?? 1)));
    $mensajes = filas('SELECT * FROM chat_mensajes WHERE conversacion_id=? ORDER BY id LIMIT 100 OFFSET ?', [$conversacionId, ($pagina - 1) * 100]);
?>
    <a href="<?= e(url('historial_chat.php')) ?>">← Todas las conversaciones</a>
    <h2><?= e($conversacion['titulo']) ?></h2>
    <p><?= e($conversacion['nombre'] ?? ($conversacion['rol_origen'] === 'visitante' ? 'Visitante' : 'Cuenta eliminada')) ?> · <?= e($conversacion['fecha_creacion']) ?></p>
    <?php if (!$mensajes): ?><p>Esta conversación todavía no tiene mensajes.</p><?php endif; ?>
    <?php foreach ($mensajes as $mensaje): ?><article class="comment-card"><strong><?= e(['user' => 'Pregunta', 'assistant' => 'Asistente', 'error' => 'Aviso del servicio'][$mensaje['rol']]) ?></strong> <small><?= e($mensaje['fecha_creacion']) ?></small><p style="white-space:pre-wrap;overflow-wrap:anywhere"><?= e($mensaje['contenido']) ?></p></article><?php endforeach; ?>
<?php else:
    $total = (int) valor("SELECT COUNT(*) FROM chat_conversaciones c LEFT JOIN usuarios u ON u.id=c.usuario_id WHERE $condicionConversaciones", $parametrosConversaciones);
    $paginas = max(1, (int) ceil($total / 20));
    $pagina = min($paginas, max(1, (int) ($_GET['pagina'] ?? 1)));
    $conversaciones = filas("SELECT c.*,u.nombre FROM chat_conversaciones c LEFT JOIN usuarios u ON u.id=c.usuario_id WHERE $condicionConversaciones ORDER BY c.fecha_actualizacion DESC,c.id DESC LIMIT 20 OFFSET ?", [...$parametrosConversaciones, ($pagina - 1) * 20]);
?>
    <h2><?= puede_moderar($viewer) ? 'Conversaciones disponibles para tu rol' : 'Mis conversaciones' ?></h2>
    <p><?= $total ?> conversaciones guardadas.</p>
    <?php foreach ($conversaciones as $conversacionListado): ?><article class="comment-card"><a href="<?= e(url('historial_chat.php?id=' . $conversacionListado['id'])) ?>"><?= e($conversacionListado['titulo']) ?></a><p><?= e(($conversacionListado['nombre'] ?? ($conversacionListado['rol_origen'] === 'visitante' ? 'Visitante' : 'Cuenta eliminada')) . ' · ' . $conversacionListado['fecha_actualizacion']) ?></p></article><?php endforeach; ?>
<?php endif; ?>
<nav class="pagination" aria-label="Páginas del historial">
<?php for ($numeroPagina = max(1, $pagina - 2); $numeroPagina <= min($paginas, $pagina + 2); $numeroPagina++): ?><a class="btn btn-outline" <?= $numeroPagina === $pagina ? 'aria-current="page"' : '' ?> href="?<?= e(http_build_query(['id' => $conversacionId, 'pagina' => $numeroPagina])) ?>"><?= $numeroPagina ?></a><?php endfor; ?>
</nav></section>
<?php require __DIR__ . '/includes/pie_pagina.php'; ?>
