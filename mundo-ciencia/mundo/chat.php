<?php
/* ARCHIVO: chat.php
 * ASISTENTE: CONTENIDO DE LA VENTANA
 * Carga funciones comunes, define el documento, presenta los controles y conecta js/chat.js.
 * El HTML da nombre a los controles; JavaScript gestiona eventos; controllers/chat.php guarda mensajes.
 */
require_once __DIR__ . '/includes/funciones.php';
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Asistente · Mundo Ciencia</title>
<style>
/* Medidas: incluye bordes y relleno dentro del tamaño de cada elemento. */
* {
    box-sizing:border-box
}

/* Distribución vertical: historial flexible y formulario al final. */
body {
    margin:0;
    height:100dvh;
    display:flex;
    flex-direction:column;
    font:14px system-ui;
    background:#fff;
    color:#16323e
}

/* Cabecera: selector de conversaciones y botón para iniciar otra. */
header {
    padding:10px;
    border-bottom:1px solid #d4e4e8
}

/* Selector de las conversaciones guardadas. */
select {
    width:100%;
    padding:7px;
    margin-bottom:6px
}

/* Botones: apariencia compartida y estado deshabilitado. */
button {
    cursor:pointer;
    border:0;
    border-radius:8px;
    padding:9px;
    background:#245c78;
    color:white
}

button:disabled {
    opacity:.5
}

/* Historial: permite desplazarse sin mover el formulario. */
#mensajes {
    flex:1;
    overflow:auto;
    padding:12px;
    display:flex;
    flex-direction:column;
    gap:12px
}

/* Burbujas: conserva saltos de línea y ajusta palabras largas. */
.mensaje {
    white-space:pre-wrap;
    overflow-wrap:anywhere;
    padding:10px;
    border-radius:12px;
    max-width:90%;
    margin:0
}

/* Pregunta enviada por la persona que usa el chat. */
.user {
    align-self:flex-end;
    background:#dcefe1
}

/* Respuesta del asistente. */
.assistant {
    background:#edf3f7
}

/* Aviso cuando el servicio no puede responder. */
.error {
    background:#fff0ec;
    color:#8c3020
}

/* Mensaje temporal de carga o error. */
#estado {
    padding:0 12px;
    font-size:12px
}

/* Formulario: pregunta y botón de envío en una fila. */
form {
    display:flex;
    gap:8px;
    padding:10px;
    border-top:1px solid #d4e4e8
}

/* Campo de escritura de la pregunta. */
textarea {
    flex:1;
    min-width:0;
    resize:vertical;
    font:inherit;
    padding:8px;
    border:1px solid #b8cdd4;
    border-radius:8px
}

/* Nota sobre cómo se conserva el historial. */
.aviso {
    font-size:11px;
    color:#526a73;
    margin:6px 0 0
}

</style></head><body>
<header><label for="conversaciones">Tus conversaciones</label><select id="conversaciones" aria-label="Conversación guardada"></select><button id="nueva" type="button">Nueva conversación</button><p class="aviso">El historial se guarda. El personal autorizado puede consultarlo. Sin sesión se conserva en este navegador mientras mantengas su cookie.</p></header>
<div id="mensajes" role="log" aria-label="Mensajes"></div><p id="estado" role="status"></p>
<form id="formulario"><textarea id="texto" aria-label="Tu pregunta" placeholder="Escribe tu pregunta…" maxlength="4000" required rows="2"></textarea><button id="enviar">Enviar</button></form>
<script>const chatEndpoint = <?= json_encode(url('controllers/chat.php'), JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="<?= e(url('js/chat.js?v=' . md5_file(__DIR__ . '/js/chat.js'))) ?>"></script>
</body></html>
