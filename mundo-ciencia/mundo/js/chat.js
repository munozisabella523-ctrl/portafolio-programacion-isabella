/* ARCHIVO: js/chat.js
 * CONTROLES DEL ASISTENTE: conecta chat.php con controllers/chat.php.
 * Orden de lectura: controles, peticiones, historial, eventos e inicio.
 * Las claves id, texto, csrf e identidad forman parte del contrato con PHP.
 */
(() => {
    // 1. CONTROLES: referencias a los elementos existentes en chat.php.
    const contenedorMensajes = document.getElementById('mensajes');
    const selectorConversaciones = document.getElementById('conversaciones');
    const campoPregunta = document.getElementById('texto');
    const avisoEstado = document.getElementById('estado');
    // 2. ESTADO: historial cargado y bloqueo durante una petición.
    let datosConversacion = null;
    let solicitudEnCurso = false;
    // Evita que se envíen dos preguntas a la vez desde esta ventana.
    function bloquearControles(valor) {
        solicitudEnCurso = valor;
        ['enviar', 'nueva', 'conversaciones'].forEach(id => document.getElementById(id).disabled = valor);
    }
    // 3. COMUNICACIÓN: solicita JSON y convierte errores HTTP en avisos.
    async function solicitarDatosChat(opciones = {}, id = null) {
        const respuestaServidor = await fetch(chatEndpoint + (id !== null ? '?id=' + encodeURIComponent(id) : ''), {cache: 'no-store', ...opciones});
        const datosRespuesta = await respuestaServidor.json();
        if (!respuestaServidor.ok) throw new Error(datosRespuesta.error || 'No se pudo cargar el chat.');
        return datosRespuesta;
    }
    // 4. HISTORIAL: actualiza el selector y dibuja cada mensaje como texto seguro.
    async function cargarConversacion(id = null) {
        datosConversacion = await solicitarDatosChat({}, id);
        selectorConversaciones.replaceChildren();
        if (!datosConversacion.conversaciones.length) selectorConversaciones.add(new Option('Nueva conversación', '0'));
        datosConversacion.conversaciones.forEach(conversacion => selectorConversaciones.add(new Option(conversacion.titulo, conversacion.id)));
        selectorConversaciones.value = String(datosConversacion.id);
        contenedorMensajes.replaceChildren();
        datosConversacion.mensajes.forEach(mensaje => {
            const parrafoMensaje = document.createElement('p');
            parrafoMensaje.className = 'mensaje ' + mensaje.rol;
            parrafoMensaje.textContent = mensaje.contenido;
            contenedorMensajes.append(parrafoMensaje);
        });
        if (!datosConversacion.mensajes.length) contenedorMensajes.textContent = '¡Hola! ¿Qué quieres descubrir hoy?';
        contenedorMensajes.scrollTop = contenedorMensajes.scrollHeight;
    }
    // Adjunta la identidad y el token de la sesión a las acciones de escritura.
    async function enviarAccionChat(cuerpo) {
        return solicitarDatosChat({method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({...cuerpo, csrf:datosConversacion.csrf, identidad:datosConversacion.identidad})});
    }
    // 5. EVENTOS: enviar una pregunta, crear conversación y consultar anteriores.
    document.getElementById('formulario').addEventListener('submit', async evento => {
        evento.preventDefault();
        if (solicitudEnCurso || !datosConversacion || !campoPregunta.value.trim()) return;
        bloquearControles(true);
        avisoEstado.textContent = 'Guardando tu pregunta y preparando la respuesta…';
        try {
            const respuestaServidor = await enviarAccionChat({id:datosConversacion.id, texto:campoPregunta.value.trim()});
            campoPregunta.value = '';
            await cargarConversacion(respuestaServidor.id);
            avisoEstado.textContent = respuestaServidor.error || '';
            if (window.parent !== window) window.parent.postMessage({tipo:'mc-chat-actualizado'}, location.origin);
        } catch (error) { avisoEstado.textContent = error.message; }
        finally {
            bloquearControles(false);
        }
    });
    document.getElementById('nueva').addEventListener('click', async () => {
        if (solicitudEnCurso || !datosConversacion) return;
        bloquearControles(true);
        try {
            const respuestaServidor = await enviarAccionChat({accion:'nueva'});
            await cargarConversacion(respuestaServidor.id);
            avisoEstado.textContent = '';
        }
        catch(error) {
            avisoEstado.textContent = error.message;
        }
        finally {
            bloquearControles(false);
        }
    });
    selectorConversaciones.addEventListener('change', async () => {
        bloquearControles(true);
        try {
            await cargarConversacion(selectorConversaciones.value);
            avisoEstado.textContent = '';
        }
        catch(error) {
            avisoEstado.textContent = error.message;
        }
        finally {
            bloquearControles(false);
        }
    });
    campoPregunta.addEventListener('keydown', evento => {
        if (evento.key === 'Enter' && !evento.shiftKey) { evento.preventDefault(); document.getElementById('formulario').requestSubmit(); }
    });
    // 6. INICIO: recupera el historial al abrir la ventana.
    bloquearControles(true);
    cargarConversacion().catch(error => { avisoEstado.textContent = error.message; }).finally(() => bloquearControles(false));
})();

