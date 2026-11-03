/* ARCHIVO: js/confirmaciones_interfaz.js
 * PARA QUÉ SIRVE: conecta los controles de la interfaz con sus interacciones.
 * DÓNDE SE VE: menú móvil, curiosidades, carrusel, fotografías ampliadas, confirmaciones y tablas.
 * Este archivo se carga desde el footer compartido; no guarda información en la base de datos.
 */
// Activa el modo estricto de JavaScript para detectar usos incorrectos del lenguaje.
'use strict';
// Busca el botón del menú móvil compartido por todas las páginas.
const botonMenu = document.querySelector('.menu-toggle');
// Abre o cierra el menú al pulsar su botón. Si no existe, no realiza ninguna acción.
botonMenu?.addEventListener('click', () => {
    // Obtiene el contenedor de enlaces de navegación superior.
    const navegacionPrincipal = document.querySelector('#navegacion');
    // Alterna la clase que muestra el menú y guarda si quedó abierto.
    const menuAbierto = navegacionPrincipal.classList.toggle('open');
    // Informa del estado abierto o cerrado a las tecnologías de accesibilidad.
    botonMenu.setAttribute('aria-expanded', String(menuAbierto));
});
// Activa las tarjetas de ¿Sabías que? del inicio, de Datos y de los paneles que las reutilizan.
document.querySelectorAll('.flip-card').forEach(card => {
    // Define cómo alternar entre la portada y la respuesta de una curiosidad.
    const girarTarjeta = () => card.classList.toggle('flipped');
    // Permite girar la tarjeta al pulsarla, conservando el funcionamiento de sus enlaces.
    card.addEventListener('click', e => {
        // Gira solo cuando la pulsación no corresponde a un enlace de la tarjeta.
        if (!e.target.closest('a')) /* Alterna la cara visible de la tarjeta. */ girarTarjeta();
    });
    // Permite descubrir la curiosidad utilizando el teclado.
    card.addEventListener('keydown', e => {
        // Responde a Enter o espacio cuando el foco está sobre la propia tarjeta.
        if (e.target === card && (e.key === 'Enter' || e.key === ' ')) {
            // Evita la acción normal del evento para realizar primero la interacción definida aquí.
            e.preventDefault();
            // Alterna la cara visible de la tarjeta.
            girarTarjeta();
        }
    });
});
// Prepara cada carrusel del inicio y de la feria de forma independiente.
document.querySelectorAll('.carousel').forEach(el => {
    // Recoge las imágenes del carrusel y los puntos que permiten seleccionarlas.
    const slides = [...el.querySelectorAll('.slides img')],
        dots = [...el.querySelectorAll('[data-slide]')];
    // Guarda la posición actual y los motivos para detener el avance; respeta la preferencia de movimiento reducido.
    let index = 0,
        paused = matchMedia('(prefers-reduced-motion: reduce)').matches,
        hover = false,
        focused = false;
    // Define el cambio de fotografía y la actualización del punto seleccionado.
    const show = i => {
        // Calcula una posición válida: al superar un extremo vuelve al otro.
        index = (i + slides.length) % slides.length;
        // Muestra únicamente la fotografía elegida y oculta las demás.
        slides.forEach((s, n) => s.hidden = n !== index);
        // Actualiza o conecta los puntos que representan las fotografías del carrusel.
        dots.forEach((d, n) => {
            // Identifica mediante aria-current el punto de la fotografía visible.
            if (n === index) /* Marca el punto del carrusel como seleccionado. */ d.setAttribute('aria-current', 'true');
            else /* Retira la marca de selección de los puntos no activos. */ d.removeAttribute('aria-current');
        });
    };
    // Conecta la flecha izquierda con la fotografía anterior.
    el.querySelector('.prev').addEventListener('click', () => show(index - 1));
    // Conecta la flecha derecha con la fotografía siguiente.
    el.querySelector('.next').addEventListener('click', () => show(index + 1));
    // Actualiza o conecta los puntos que representan las fotografías del carrusel.
    dots.forEach(d => d.addEventListener('click', () => show(Number(d.dataset.slide))));
    // Busca el botón para pausar y reanudar el carrusel.
    const pause = el.querySelector('.pause');
    // Define cómo actualizar el símbolo y el nombre accesible del botón de pausa.
    const label = () => {
        // Muestra el símbolo de reproducir o de pausar según el estado.
        pause.textContent = paused ? '▶' : 'Ⅱ';
        // Describe correctamente la acción disponible para lectores de pantalla.
        pause.setAttribute('aria-label', paused ? 'Reanudar carrusel' : 'Pausar carrusel');
    };
    // Actualiza el botón para reflejar el estado actual.
    label();
    // Cambia entre reproducción y pausa cuando se pulsa el control.
    pause.addEventListener('click', () => {
        // Invierte el estado de pausa del carrusel.
        paused = !paused;
        // Actualiza el botón para reflejar el estado actual.
        label();
    });
    // Detiene el avance automático mientras el cursor está sobre el carrusel.
    el.addEventListener('mouseenter', () => hover = true);
    // Permite continuar el avance cuando el cursor sale, si no hay otro motivo de pausa.
    el.addEventListener('mouseleave', () => hover = false);
    // Detiene el avance mientras se utilizan los controles con el teclado.
    el.addEventListener('focusin', () => focused = true);
    // Comprueba si el foco continúa dentro del carrusel antes de permitir el avance.
    el.addEventListener('focusout', e => focused = el.contains(e.relatedTarget));
    // Comprueba cada 5,5 segundos si debe avanzar a la siguiente fotografía.
    setInterval(() => {
        // Avanza solo si no hay pausa, cursor o foco dentro, y la pestaña está visible.
        if (!paused && !hover && !focused && !document.hidden) show(index + 1);
    }, 5500);
});
// Localiza la ventana compartida para ampliar las fotografías de proyectos y avances.
const visor = document.querySelector('#visor');
// Conecta las miniaturas de las galerías con la ventana de imagen ampliada.
document.querySelectorAll('.lightbox-trigger').forEach(b => b.addEventListener('click', () => {
    // Carga en la ventana la fotografía indicada por la miniatura pulsada.
    visor.querySelector('img').src = b.dataset.image;
    // Abre la fotografía ampliada sobre la página.
    visor.showModal();
}));
// Conecta el botón Cerrar de la fotografía ampliada.
visor?.querySelector('.cerrar-visor').addEventListener('click', () => visor.close());
// Conecta botones con la ventana identificada en data-dialog, como formularios emergentes.
document.querySelectorAll('[data-dialog]').forEach(b => b.addEventListener('click', () => document.getElementById(b
    .dataset.dialog).showModal()));
// Permite cerrar la ventana más cercana al botón pulsado.
document.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => b.closest('dialog').close()));
// Busca el diálogo compartido de confirmación de acciones.
const confirmacion = document.querySelector('#confirmacion');
// Reserva un lugar para recordar el formulario que espera confirmación.
let pendiente = null;
// Revisa los envíos de formularios que requieren confirmación antes de continuar.
document.addEventListener('submit', e => {
    // Recoge el botón, el formulario y el texto de confirmación configurado en sus atributos.
    const b = e.submitter,
        form = e.target,
        message = b?.dataset.confirm || form.dataset.confirm;
    // Interrumpe el envío solo si hay un mensaje y todavía no se confirmó esta operación.
    if (message && !form.dataset.confirmado) {
        // Evita la acción normal del evento para realizar primero la interacción definida aquí.
        e.preventDefault();
        // Conserva el formulario y el botón que originaron la operación.
        pendiente = {
            form,
            b
        };
        // Muestra en el diálogo la pregunta correspondiente a la acción solicitada.
        document.querySelector('#confirmacion-texto').textContent = message;
        // Abre el diálogo antes de permitir el envío del formulario.
        confirmacion.showModal();
    }
});
// Revisa si el usuario confirmó o canceló al cerrar el diálogo.
confirmacion?.addEventListener('close', () => {
    // Continúa únicamente cuando se eligió Confirmar y hay un formulario pendiente.
    if (confirmacion.returnValue === 'aceptar' && pendiente) {
        // Recupera el formulario y su botón a partir de la operación pendiente.
        const {
            form,
            b
        } = pendiente;
        // Marca el formulario para que no vuelva a pedir la misma confirmación al enviarse.
        form.dataset.confirmado = '1';
        // Envía el formulario con su botón original, conservando las validaciones del navegador.
        form.requestSubmit(b);
    }
    // Limpia la operación pendiente para no reutilizarla por accidente.
    pendiente = null;
    // Limpia la respuesta del diálogo antes de otra operación.
    confirmacion.returnValue = '';
});
// Prepara etiquetas de columna para que las tablas de gestión se comprendan en celular.
document.querySelectorAll('table').forEach(t => {
    // Lee los títulos de las columnas de la tabla.
    const headers = [...t.querySelectorAll('thead th')].map(h => h.textContent);
    // Asocia cada celda a su encabezado mediante data-label; el CSS lo muestra en pantallas pequeñas.
    t.querySelectorAll('tbody tr').forEach(r => [...r.cells].forEach((c, i) => c.dataset.label = headers[i] ||
        ''));
});
