<!-- Asistente de IA: se conserva su HTML y JavaScript original. -->
<!-- Estilos del asistente: css/chat_inicio.css, cargados en el encabezado. -->

<button type="button" id="botonChat" onclick="toggleChat()" aria-controls="ventanaChat" aria-expanded="false" aria-label="Abrir el asistente de Mundo Ciencia">
    <span class="icono-chat" aria-hidden="true">💬</span>
    <span class="icono-cerrar" aria-hidden="true">✕</span>
</button>

<div id="ventanaChat">
    <div class="cabecera-chat">
        <span>💬 Asistente · Mundo Ciencia</span>
    </div>
    <iframe id="chatFrame" data-src="<?= e(url('chat.php')) ?>" title="Asistente de Mundo Ciencia"></iframe>
</div>

<script>
    // Alterna el botón y la ventana del asistente. Carga chat.php dentro del
    // iframe solo la primera vez que se abre, para no gastar recursos de más.
    function toggleChat() {
        var boton = document.getElementById('botonChat');
        var ventana = document.getElementById('ventanaChat');
        var marco = document.getElementById('chatFrame');
        var abierta = ventana.classList.toggle('abierta');
        boton.classList.toggle('activo', abierta);
        boton.setAttribute('aria-expanded', String(abierta));
        boton.setAttribute('aria-label', abierta ? 'Cerrar el asistente' : 'Abrir el asistente de Mundo Ciencia');
        if (abierta && !marco.src) {
            marco.src = marco.dataset.src;
        }
    }
</script>

<script>
window.addEventListener('message', async function(event) {
    if (event.origin !== location.origin || event.source !== document.getElementById('chatFrame').contentWindow || event.data?.tipo !== 'mc-chat-actualizado') return;
    const resumen = document.getElementById('resumen-chat');
    if (!resumen) return;
    try {
        const respuesta = await fetch(location.href, {cache: 'no-store'});
        const documento = new DOMParser().parseFromString(await respuesta.text(), 'text/html');
        const actualizado = documento.getElementById('resumen-chat');
        if (actualizado) resumen.replaceWith(actualizado);
    } catch (_) {}
});
</script>

