/* ARCHIVO: js/juego.js
 * Orden de lectura: preguntas, estado de la partida, controles, funciones y eventos.
 * Los nombres de variables describen su función; los id del HTML se conservan.
 */
// JUEGO: cinco pistas. La respuesta correcta se cuenta desde cero: 0, 1 o 2.
const preguntas = [
    { pista: "Permito observar cosas tan pequeñas que tus ojos no pueden ver. ¿Qué soy?", opciones: ["Telescopio", "Microscopio", "Periscopio"], correcta: 1 },
    { pista: "Transformo la luz del Sol en electricidad. ¿Qué soy?", opciones: ["Panel solar", "Brújula", "Termómetro"], correcta: 0 },
    { pista: "Mi aguja te ayuda a encontrar el norte. ¿Qué soy?", opciones: ["Reloj", "Balanza", "Brújula"], correcta: 2 },
    { pista: "Creo objetos capa por capa a partir de un diseño digital. ¿Qué soy?", opciones: ["Cámara", "Impresora 3D", "Proyector"], correcta: 1 },
    { pista: "Acercar planetas y estrellas a tu vista es mi especialidad. ¿Qué soy?", opciones: ["Telescopio", "Microscopio", "Radar"], correcta: 0 }
];

// Estado de la partida: pregunta actual, aciertos y si ya se respondió.
let indicePreguntaActual = 0;
let puntosAcumulados = 0;
let preguntaRespondida = false;

// Elementos que se ven dentro de la tarjeta de juego.php.
const etiquetaRonda = document.getElementById('ronda');
const etiquetaPuntuacion = document.getElementById('puntos');
const etiquetaPista = document.getElementById('pista');
const contenedorOpciones = document.getElementById('opciones');
const mensajeResultado = document.getElementById('respuesta');
const botonSiguiente = document.getElementById('siguiente');
const botonReiniciar = document.getElementById('reiniciar');

// Dibuja una pregunta y crea sus tres botones de respuesta.
function mostrarPregunta() {
    preguntaRespondida = false;
    etiquetaRonda.textContent = 'Pista ' + (indicePreguntaActual + 1) + ' de ' + preguntas.length;
    etiquetaPuntuacion.textContent = 'Puntos: ' + puntosAcumulados;
    etiquetaPista.textContent = preguntas[indicePreguntaActual].pista;
    mensajeResultado.textContent = '';
    contenedorOpciones.replaceChildren();
    botonSiguiente.hidden = true;
    botonReiniciar.hidden = true;
    preguntas[indicePreguntaActual].opciones.forEach(function (texto, indice) {
        const boton = document.createElement('button');
        boton.textContent = texto;
        boton.addEventListener('click', function () { comprobarRespuesta(indice); });
        contenedorOpciones.appendChild(boton);
    });
}

// Comprueba una sola respuesta por ronda y muestra la solución.
function comprobarRespuesta(eleccion) {
    if (preguntaRespondida) return;
    preguntaRespondida = true;
    const correcta = preguntas[indicePreguntaActual].correcta;
    if (eleccion === correcta) puntosAcumulados++;
    etiquetaPuntuacion.textContent = 'Puntos: ' + puntosAcumulados;
    mensajeResultado.textContent = eleccion === correcta ? '¡Correcto! Un punto más.' : 'La respuesta era: ' + preguntas[indicePreguntaActual].opciones[correcta] + '.';
    Array.from(contenedorOpciones.children).forEach(function (boton, indice) {
        boton.disabled = true;
        if (indice === correcta) boton.classList.add('correcta');
        else if (indice === eleccion) boton.classList.add('incorrecta');
    });
    botonSiguiente.textContent = indicePreguntaActual === preguntas.length - 1 ? 'Ver resultado →' : 'Siguiente pista →';
    botonSiguiente.hidden = false;
    botonSiguiente.focus();
}

// Avanza o muestra el resultado final al terminar las cinco pistas.
botonSiguiente.addEventListener('click', function () {
    indicePreguntaActual++;
    if (indicePreguntaActual < preguntas.length) mostrarPregunta();
    else {
        etiquetaRonda.textContent = 'Reto completado';
        etiquetaPista.textContent = puntosAcumulados === preguntas.length ? '¡Eres un gran inventor!' : '¡Buen intento! Sigue explorando.';
        contenedorOpciones.replaceChildren();
        mensajeResultado.textContent = 'Acertaste ' + puntosAcumulados + ' de ' + preguntas.length + ' pistas.';
        botonSiguiente.hidden = true;
        botonReiniciar.hidden = false;
    }
    etiquetaPista.focus();
});

// Reinicia los contadores para jugar otra vez sin recargar la página.
botonReiniciar.addEventListener('click', function () {
    indicePreguntaActual = 0;
    puntosAcumulados = 0;
    mostrarPregunta();
    etiquetaPista.focus();
});

mostrarPregunta();
