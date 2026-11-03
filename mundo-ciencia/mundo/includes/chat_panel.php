<?php
/* ARCHIVO: includes/chat_panel.php
 * RESUMEN DEL CHAT EN EL PANEL
 * Aplica los permisos del usuario, cuenta sus conversaciones visibles y muestra las cinco recientes.
 * Los enlaces abren historial_chat.php; aquí no se envían mensajes al modelo.
 */
require_once __DIR__ . '/chat_historial.php';
[$condicionConversaciones, $parametrosConversaciones] = chat_filtro_panel($viewer);
$totalConversaciones = valor("SELECT COUNT(*) FROM chat_conversaciones c LEFT JOIN usuarios u ON u.id=c.usuario_id WHERE $condicionConversaciones", $parametrosConversaciones);
$conversacionesRecientes = filas("SELECT c.id,c.titulo,c.fecha_actualizacion,u.nombre,c.rol_origen FROM chat_conversaciones c LEFT JOIN usuarios u ON u.id=c.usuario_id WHERE $condicionConversaciones ORDER BY c.fecha_actualizacion DESC,c.id DESC LIMIT 5", $parametrosConversaciones);
?>
<section class="info-card" id="resumen-chat">
    <div class="section-heading"><h2>Conversaciones del asistente</h2><a class="btn btn-outline" href="<?= e(url('historial_chat.php')) ?>">Ver historial (<?= (int) $totalConversaciones ?>)</a></div>
    <?php if (!$conversacionesRecientes): ?><p>Todavía no hay conversaciones para mostrar. Abre el asistente para comenzar.</p><?php endif; ?>
    <?php foreach ($conversacionesRecientes as $conversacion): ?>
    <p><a href="<?= e(url('historial_chat.php?id=' . $conversacion['id'])) ?>"><?= e($conversacion['titulo']) ?></a><br><small><?= e(($conversacion['nombre'] ?? ($conversacion['rol_origen'] === 'visitante' ? 'Visitante' : 'Cuenta eliminada')) . ' · ' . $conversacion['fecha_actualizacion']) ?></small></p>
    <?php endforeach; ?>
</section>
