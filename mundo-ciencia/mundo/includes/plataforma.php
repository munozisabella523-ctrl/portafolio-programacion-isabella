<?php
/*
 * ARCHIVO: includes/plataforma.php
 * PARA QUÉ SIRVE: Centraliza consultas, permisos, formularios, tarjetas, imágenes, notas y comentarios.
 * DÓNDE SE UTILIZA: Componentes reutilizados en el inicio, los proyectos, el club y la administración.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Define los cuatro roles que utiliza el control de acceso y el selector de cuentas.
const ROLES = ['estudiante', 'profesor', 'administrador', 'super_administrador'];
// Define 1 como nota mínima admitida en el formulario de evaluación.
const NOTA_MIN = 1;
// Define 5 como nota máxima y como escala mostrada junto a las evaluaciones.
const NOTA_MAX = 5;
// Prepara SQL, asocia los valores de sus signos ? y lo ejecuta. Evita concatenar datos del formulario dentro de la consulta.
function consulta(string $sql, array $parametros = []): mysqli_stmt
{
    // Prepara $s con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $s = base_datos()->prepare($sql);
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($parametros) {
        // Construye los tipos de los parámetros de la consulta preparada.
        $tipos = '';
        // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
        foreach ($parametros as $v) $tipos .= is_int($v) ? 'i' : (is_float($v) ? 'd' : 's');
        // Asocia cada valor a su marcador de la consulta y especifica el tipo esperado.
        $s->bind_param($tipos, ...$parametros);
    }
    // Ejecuta la consulta preparada con sus valores asociados.
    $s->execute();
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $s;
}

// Devuelve todas las filas de una consulta como lista. Alimenta listados de proyectos, usuarios y comentarios.
function filas(string $sql, array $p = []): array
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return consulta($sql, $p)->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Devuelve una sola fila o null. Se usa al abrir un proyecto, una cuenta o una curiosidad.
function fila(string $sql, array $p = []): ?array
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return consulta($sql, $p)->get_result()->fetch_assoc();
}

// Devuelve la primera columna de la primera fila. Se usa para contadores, promedios y comprobaciones.
function valor(string $sql, array $p = [])
{
    // Prepara $r con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $r = fila($sql, $p);
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $r ? array_values($r)[0] : null;
}

// Construye una dirección dentro del sitio, incluso si cambia la carpeta de instalación. Se utiliza en enlaces y recursos.
function url(string $ruta = ''): string
{
    // Obtiene la dirección base de instalación del sitio.
    $base = getenv('MC_BASE_URL');
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($base === false) {
        // Resuelve la carpeta base para comprobar que la ruta pertenece al proyecto.
        $raiz = str_replace('\\', '/', realpath(__DIR__ . '/..'));
        // Obtiene la carpeta pública configurada en el servidor.
        $doc = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
        // Obtiene la dirección base de instalación del sitio.
        $base = $doc && str_starts_with($raiz, $doc) ? substr($raiz, strlen($doc)) : '/mundo';
    }
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return rtrim($base, '/') . '/' . ltrim($ruta, '/');
}

// Construye una dirección interna y redirige hacia ella. Se usa al terminar formularios o comprobar permisos.
function ir(string $ruta): never
{
    // Envía al navegador a otra dirección y termina esta petición. Se utiliza después de guardar o validar formularios.
    redireccionar(url($ruta));
}

// Detiene una petición sin permiso o sin recurso disponible y muestra el aviso correspondiente.
function denegar(int $codigo = 403): never
{
    http_response_code($codigo);
    // Genera HTML para este componente: título principal. Los datos dinámicos se incorporan en esta expresión.
    echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Acceso restringido</title><h1>' . ($codigo === 404 ? 'Contenido no disponible' : 'Acceso denegado') . '</h1><p><a href="' . e(url('inicio.php')) . '">Volver a Mundo Ciencia</a></p></html>';
    exit;
}

// Exige una cuenta activa; si no hay sesión, dirige a Iniciar sesión.
function requiere_login(): array
{
    // Prepara la cuenta o el identificador que se utiliza en esta operación.
    $u = usuario_actual();
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if (!$u)ir('inicio_sesion.php');
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $u;
}

// Compara el rol actual con los roles permitidos. Protege las páginas de gestión.
function requiere_rol(string ...$roles): array
{
    // Prepara la cuenta o el identificador que se utiliza en esta operación.
    $u = requiere_login();
    // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
    if (!in_array($u['rol'], $roles, true))denegar();
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $u;
}

// Indica si la cuenta es administradora o superadministradora. Controla los botones y acciones de revisión.
function puede_moderar(?array $u = null): bool
{
    // Prepara la cuenta o el identificador que se utiliza en esta operación.
    $u = $u ?? usuario_actual();
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $u && in_array($u['rol'], ['administrador', 'super_administrador'], true);
}

// Comprueba el rol de superadministrador. Habilita la gestión de todos los roles y del sistema.
function es_super_admin(?array $u = null): bool
{
    // Prepara la cuenta o el identificador que se utiliza en esta operación.
    $u = $u ?? usuario_actual();
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $u && $u['rol'] === 'super_administrador';
}

// Permite editar a moderadores o al autor cuando el estado del proyecto admite cambios. Controla Mis proyectos.
function puede_editar_experimento(array $u, array $e): bool
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return puede_moderar($u) || ((int) $u['id'] === (int) $e['usuario_id'] && in_array($e['estado'], ['pendiente', 'rechazado', 'borrador', 'cambios_solicitados'], true));
}

// Autoriza a profesores a evaluar proyectos aprobados de estudiantes distintos de ellos. Controla Tu evaluación.
function puede_calificar(array $u, array $e): bool
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $u['rol'] === 'profesor' && $e['estado'] === 'aprobado' && (int) $e['usuario_id'] !== (int) $u['id'] && valor('SELECT rol FROM usuarios WHERE id=?', [$e['usuario_id']]) === 'estudiante';
}

// Permite ver proyectos aprobados; los demás se reservan al autor o a moderadores.
function puede_ver_experimento(?array $u, array $e): bool
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $e['estado'] === 'aprobado' || ($u && ((int) $u['id'] === (int) $e['usuario_id'] || puede_moderar($u)));
}

// Busca el proyecto y su autor, y comprueba que la cuenta pueda verlo antes de mostrar el detalle.
function experimento(int $id): array
{
    // Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
    $e = fila('SELECT e.*,u.nombre autor_nombre,u.avatar,u.rol autor_rol FROM experimentos e JOIN usuarios u ON u.id=e.usuario_id WHERE e.id=?', [$id]);
    // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
    if (!$e || !puede_ver_experimento(usuario_actual(), $e))denegar(404);
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $e;
}

// Crea o recupera un token secreto de sesión para proteger el envío de formularios.
function csrf(): string
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

// Inserta el token en un campo oculto del formulario; no es visible en pantalla.
function campo_csrf(): void
{
    // Genera HTML para este componente: campo de formulario. Los datos dinámicos se incorporan en esta expresión.
    echo '<input type="hidden" name="csrf" value="' . csrf() . '">';
}

// Exige una petición POST con el token de la sesión. Se ejecuta antes de procesar los formularios.
function validar_csrf(): void
{
    // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')denegar(405);
    // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals(csrf(), $_POST['csrf']))denegar();
}

// Lee y recorta un campo enviado; comprueba si es obligatorio y su longitud máxima.
function texto(string $campo, int $max = 5000, bool $obligatorio = true): string
{
    // Prepara $s con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $s = trim((string) ($_POST[$campo] ?? ''));
    // Rechaza datos que no cumplen la validación y prepara un aviso para el usuario.
    if (($obligatorio && $s === '') || mb_strlen($s) > $max)throw new DomainException('Revisa el campo ' . str_replace('_', ' ', $campo) . '.');
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $s;
}

// Comprueba que el valor enviado pertenece a una lista permitida, como roles o estados.
function opcion(string $campo, array $opciones): string
{
    // Prepara $v con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $v = (string) ($_POST[$campo] ?? '');
    // Rechaza datos que no cumplen la validación y prepara un aviso para el usuario.
    if (!in_array($v, $opciones, true))throw new DomainException('La opción seleccionada no es válida.');
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $v;
}

// Guarda un aviso dirigido a una cuenta. Se muestra en Notificaciones.
function notificar(int $u, string $tipo, string $titulo, string $mensaje, string $ruta): void
{
    // Añade información a notificaciones. Avisos destinados a una cuenta. Se consultan desde Notificaciones.
    consulta('INSERT INTO notificaciones(usuario_id,tipo,titulo,mensaje,url) VALUES(?,?,?,?,?)', [$u, $tipo, $titulo, $mensaje, $ruta]);
}

// Genera la dirección protegida de una imagen o usa la portada si falta una. Alimenta tarjetas y galerías.
function imagen_url(?string $archivo, string $tipo = 'experimento'): string
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $archivo ? url('archivo.php?tipo=' . rawurlencode($tipo) . '&archivo=' . rawurlencode($archivo)) : url('imagen_portada.png');
}

// Valida cantidad, tamaño, extensión y contenido de las imágenes; las guarda con nombres aleatorios en uploads.
function subir_imagenes(string $campo, string $carpeta, int $limite = 8): array
{
    // Prepara $f con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
    $f = $_FILES[$campo] ?? null;
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if (!$f)return [];
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if (!is_array($f['name']))foreach ($f as $k => $v)$f[$k] = [$v];
    // Rechaza datos que no cumplen la validación y prepara un aviso para el usuario.
    if (count($f['name']) > $limite)throw new DomainException('Puedes subir hasta ' . $limite . ' imágenes por envío.');
    // Acumula los resultados que devolverá esta función.
    $salida = [];
    // Define los tipos de imagen aceptados y la extensión con la que se guardan.
    $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach ($f['name'] as $i => $nombre) {
        // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
        if ($f['error'][$i] === UPLOAD_ERR_NO_FILE)continue;
        // Rechaza datos que no cumplen la validación y prepara un aviso para el usuario.
        if ($f['error'][$i] !== UPLOAD_ERR_OK || $f['size'][$i] > 5 * 1024 * 1024)throw new DomainException('Cada imagen debe pesar como máximo 5 MB y subirse completamente.');
        // Identifica el tipo real del archivo antes de guardarlo o entregarlo.
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name'][$i]);
        // Prepara $ext con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        // Rechaza datos que no cumplen la validación y prepara un aviso para el usuario.
        if (!isset($permitidos[$mime]) || !in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) || !getimagesize($f['tmp_name'][$i]))throw new DomainException('Solo se admiten imágenes JPG, PNG y WebP válidas.');
        // Construye el nombre o la referencia del archivo utilizado por esta operación.
        $archivo = $carpeta . '/' . bin2hex(random_bytes(20)) . '.' . $permitidos[$mime];
        // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
        if (!is_dir(ruta_base('uploads/' . $carpeta)))mkdir(ruta_base('uploads/' . $carpeta), 0755, true);
        // Rechaza datos que no cumplen la validación y prepara un aviso para el usuario.
        if (!move_uploaded_file($f['tmp_name'][$i], ruta_base('uploads/' . $archivo)))throw new RuntimeException('No se pudo guardar la imagen.');
        $salida[] = $archivo;
        $GLOBALS['archivos_nuevos'][] = $archivo;
    }return $salida;
}

// Dibuja una etiqueta de rol o estado, como aprobado o pendiente, en tarjetas y paneles.
function badge(string $estado): void
{
    // Genera HTML para este componente: etiqueta de texto. Los datos dinámicos se incorporan en esta expresión.
    echo '<span class="badge ' . e($estado) . '">' . e(ucfirst(str_replace('_', ' ', $estado))) . '</span>';
}

// Muestra la foto de la cuenta o su inicial si no tiene imagen. Aparece en menú, perfil y comentarios.
function avatar(array $u): void
{
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if (!empty($u['avatar']))echo '<img class="avatar" src="' . e(imagen_url($u['avatar'], 'avatar')) . '" alt="Avatar de ' . e($u['nombre'] ?? $u['autor_nombre'] ?? 'usuario') . '">';
    else echo '<span class="avatar inicial" aria-hidden="true">' . e(mb_substr($u['nombre'] ?? $u['autor_nombre'] ?? 'MC', 0, 1)) . '</span>';
}

// Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción.
function formulario(string $accion, string $clase = 'form-card'): void
{
    // Genera HTML para este componente: formulario de envío. Los datos dinámicos se incorporan en esta expresión.
    echo '<form class="' . e($clase) . '" method="post" enctype="multipart/form-data" action="' . e(url('controllers/acciones.php')) . '">';
    // Inserta el token en un campo oculto del formulario; no es visible en pantalla.
    campo_csrf();
    // Genera HTML para este componente: campo de formulario. Los datos dinámicos se incorporan en esta expresión.
    echo '<input type="hidden" name="accion" value="' . e($accion) . '">';
}

// Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
function oculto(string $nombre, $valor): void
{
    // Genera HTML para este componente: campo de formulario. Los datos dinámicos se incorporan en esta expresión.
    echo '<input type="hidden" name="' . e($nombre) . '" value="' . e($valor) . '">';
}

// Construye las tarjetas de proyectos con foto, título, categoría, autor y estado. Se usa en listados y paneles.
function tarjetas(array $lista): void
{
    // Muestra el estado vacío cuando no hay registros para este apartado.
    if (!$lista) {
        // Genera HTML para este componente: contenedor de distribución. Los datos dinámicos se incorporan en esta expresión.
        echo '<div class="empty-state"><span class="big-icon">⚗</span><h3>Aquí empieza una nueva idea</h3><p>Aún no hay proyectos para mostrar. Vuelve pronto para descubrir lo que estamos creando.</p></div>';
        // Devuelve el resultado a la página o función que solicitó esta operación.
        return;
    }
    // Genera HTML para este componente: contenedor de distribución. Los datos dinámicos se incorporan en esta expresión.
    echo '<div class="project-grid">';
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach ($lista as $e) {
        // Consulta experimento_imagenes. Fotografías y orden de cada experimento. Alimenta portadas y galerías.
        $foto = valor('SELECT archivo FROM experimento_imagenes WHERE experimento_id=? ORDER BY orden,id LIMIT 1', [$e['id']]);
        // Genera HTML para este componente: tarjeta de un registro. Los datos dinámicos se incorporan en esta expresión.
        echo '<article class="project-card"><a href="' . e(url('experimento.php?id=' . $e['id'])) . '"><img loading="lazy" src="' . e(imagen_url($foto)) . '" alt="' . e($e['titulo']) . '"></a><div class="project-body"><p class="eyebrow">' . e($e['categoria']) . (!empty($e['destacado']) ? ' · ★ Destacado' : '') . '</p><h3><a href="' . e(url('experimento.php?id=' . $e['id'])) . '">' . e($e['titulo']) . '</a></h3><p>' . e(mb_strimwidth($e['descripcion'], 0, 140, '…')) . '</p><p class="meta">' . e($e['autor_nombre'] ?? '') . ' · ' . e(substr($e['fecha_creacion'], 0, 10)) . '</p>';
        // Dibuja una etiqueta de rol o estado, como aprobado o pendiente, en tarjetas y paneles.
        badge($e['estado']);
        // Escribe el resultado o cierra el fragmento HTML que se está construyendo.
        echo '</div></article>';
    }echo '</div>';
}

// Construye hasta seis tarjetas giratorias de curiosidades activas. Se muestran en Inicio, Datos y algunos paneles.
function datos_cards(): void
{
    // Consulta datos_cientificos. Curiosidades, categoría, imagen y visibilidad. Alimentan ¿Sabías que? y Datos científicos.
    $datos = filas('SELECT * FROM datos_cientificos WHERE activo=1 ORDER BY id DESC LIMIT 6');
    // Genera HTML para este componente: contenedor de distribución. Los datos dinámicos se incorporan en esta expresión.
    echo '<div class="project-grid">';
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach ($datos as $d) {
        // Elige una ilustración según el tema; respeta las imágenes de administración.
        $ilustracion = 'portada_laboratorio.png';
        $tema = mb_strtolower($d['titulo'] . ' ' . $d['descripcion']);
        if (str_contains($tema, 'neutrones')) $ilustracion = 'curiosidad_estrella.png';
        elseif (str_contains($tema, 'venus')) $ilustracion = 'curiosidad_venus.png';
        elseif (str_contains($tema, 'tardígrado')) $ilustracion = 'curiosidad_tardigrado.png';
        $foto = $d['imagen'] ? imagen_url($d['imagen'], 'dato') : url('imagenes/' . $ilustracion);
        $descripcionImagen = $d['imagen'] ? $d['titulo'] : 'Ilustración: ' . $d['titulo'];
        // La imagen está en la cara delantera; la respuesta permanece detrás.
        echo '<article class="flip-card flip-con-imagen" tabindex="0" aria-label="' . e($d['titulo']) . '. Pulsa para girar"><div class="flip-inner"><div class="flip-front">';
        // Solo Venus lleva marco de acercamiento; las demás ilustraciones quedan quietas.
        $animarPlaneta = !$d['imagen'] && $ilustracion === 'curiosidad_venus.png';
        if ($animarPlaneta) echo '<span class="marco-planeta">';
        echo '<img src="' . e($foto) . '" alt="' . e($descripcionImagen) . '" width="1536" height="1024" loading="lazy">';
        if ($animarPlaneta) echo '</span>';
        // Genera HTML para este componente: párrafo informativo. Los datos dinámicos se incorporan en esta expresión.
        echo '<p class="eyebrow">' . e($d['categoria']) . '</p><h3>' . e($d['titulo']) . '</h3><span class="meta">Descubre la respuesta ↗</span></div><div class="flip-back"><p>' . e($d['descripcion']) . '</p><a class="btn btn-outline" href="' . e(url('dato.php?id=' . $d['id'])) . '">Descubrir más →</a></div></div></article>';
    }echo '</div>';
}

// Dibuja fotografías con botones para ampliarlas. Se utiliza en proyectos y avances de robótica.
function galeria(array $imagenes, string $tipo = 'experimento'): void
{
    // Genera HTML para este componente: contenedor de distribución. Los datos dinámicos se incorporan en esta expresión.
    echo '<div class="gallery">';
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach ($imagenes as $i)echo '<button type="button" class="lightbox-trigger" data-image="' . e(imagen_url($i['archivo'], $tipo)) . '" aria-label="Ampliar fotografía"><img loading="lazy" src="' . e(imagen_url($i['archivo'], $tipo)) . '" alt="Fotografía del proyecto"></button>';
    // Escribe el resultado o cierra el fragmento HTML que se está construyendo.
    echo '</div>';
}

// Muestra las evaluaciones de un experimento con profesor, nota, comentario y fecha.
function notas(int $id): void
{
    // Consulta calificaciones. Notas y comentarios del profesor vinculados a proyecto y estudiante. Se ven en Evaluaciones y Mi perfil.
    $notas = filas('SELECT c.*,u.nombre profesor FROM calificaciones c JOIN usuarios u ON u.id=c.profesor_id WHERE experimento_id=? ORDER BY fecha_actualizacion DESC', [$id]);
    // Genera HTML para este componente: contenedor de distribución. Los datos dinámicos se incorporan en esta expresión.
    echo '<div class="stack">';
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach ($notas as $n)echo '<article class="comment-card"><strong>' . e($n['nota']) . ' / ' . NOTA_MAX . ' · ' . e($n['profesor']) . '</strong><p>' . nl2br(e($n['comentario'])) . '</p><small>' . e($n['fecha_actualizacion']) . '</small></article>';
    // Muestra el estado vacío cuando no hay registros para este apartado.
    if (!$notas)echo '<p class="meta">Todavía no hay evaluaciones.</p>';
    // Escribe el resultado o cierra el fragmento HTML que se está construyendo.
    echo '</div>';
}

// Busca un proyecto del club y verifica su visibilidad antes de mostrar el detalle.
function robotica(int $id): array
{
    // Consulta proyectos_robotica. Proyectos del club, sus responsables, portada y estado de revisión.
    $p = fila('SELECT p.*,u.nombre FROM proyectos_robotica p JOIN usuarios u ON u.id=p.creador_id WHERE p.id=?', [$id]);
    // Prepara la cuenta o el identificador que se utiliza en esta operación.
    $u = usuario_actual();
    // Comprueba los permisos de administración antes de continuar con esta acción.
    if (!$p || ($p['moderacion'] !== 'aprobado' && !($u && (puede_moderar($u) || miembro_robotica($u, $p)))))denegar(404);
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return $p;
}

// Comprueba si una cuenta creó el proyecto del club o pertenece a su equipo.
function miembro_robotica(array $u, array $p): bool
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return (int) $u['id'] === (int) $p['creador_id'] || (bool) valor('SELECT 1 FROM miembros_robotica WHERE proyecto_id=? AND usuario_id=?', [$p['id'], $u['id']]);
}

// Decide si un avance puede mostrarse por su estado, el estado del proyecto y la cuenta que lo consulta.
function ver_avance(array $a, array $p, ?array $u): bool
{
    // Devuelve el resultado a la página o función que solicitó esta operación.
    return ($p['moderacion'] === 'aprobado' && $a['estado'] === 'visible') || ($u && (puede_moderar($u) || miembro_robotica($u, $p)));
}

// Lista comentarios accesibles y muestra acciones según la cuenta. Añade el formulario de participación si hay sesión.
function comentarios(int $id, string $tipo = 'experimento'): void
{
    // Prepara la cuenta o el identificador que se utiliza en esta operación.
    $u = usuario_actual();
    // Elige si el comentario pertenece a un experimento o a un avance.
    $col = $tipo === 'experimento' ? 'experimento_id' : 'avance_robotica_id';
    // Consulta comentarios. Mensajes asociados a experimentos o avances, con autor y estado de visibilidad.
    $lista = filas("SELECT c.*,u.nombre,u.rol,u.avatar FROM comentarios c JOIN usuarios u ON u.id=c.usuario_id WHERE $col=? AND (c.estado='visible' OR c.usuario_id=? OR ?=1) ORDER BY c.fecha_creacion DESC", [$id, $u['id'] ?? 0, puede_moderar($u) ? 1 : 0]);
    // Genera HTML para este componente: apartado de contenido. Los datos dinámicos se incorporan en esta expresión.
    echo '<section class="comments"><h3>Conversamos sobre ciencia</h3>';
    // Muestra el estado vacío cuando no hay registros para este apartado.
    if (!$lista)echo '<p class="meta">Comparte una pregunta o una idea. Sé respetuoso con tus compañeros.</p>';
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach ($lista as $c) {
        // Genera HTML para este componente: tarjeta de un registro. Los datos dinámicos se incorporan en esta expresión.
        echo '<article class="comment-card">';
        // Muestra la foto de la cuenta o su inicial si no tiene imagen. Aparece en menú, perfil y comentarios.
        avatar($c);
        // Genera HTML para este componente: párrafo informativo. Los datos dinámicos se incorporan en esta expresión.
        echo '<strong>' . e($c['nombre']) . '</strong> <small>' . e($c['rol']) . ' · ' . e($c['fecha_creacion']) . '</small><p>' . nl2br(e($c['comentario'])) . '</p>';
        // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
        if ($c['estado'] !== 'visible')badge($c['estado']);
        // Comprueba los permisos de administración antes de continuar con esta acción.
        if ($u && ((int) $c['usuario_id'] === (int) $u['id'] || puede_moderar($u))) {
            // Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción.
            formulario('comentario_eliminar', 'inline');
            // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
            oculto('id',$c['id']);
            // Genera HTML para este componente: botón de acción. Los datos dinámicos se incorporan en esta expresión.
            echo '<button class="btn btn-outline" data-confirm="¿Eliminar este comentario?">Eliminar</button></form>';
        }echo '</article>';
    }
    // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
    if ($u) {
        // Abre un formulario dirigido al controlador común e incluye los campos ocultos de seguridad y de acción.
        formulario('comentar');
        // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
        oculto('tipo',$tipo);
        // Genera un campo que acompaña al formulario sin mostrarse, por ejemplo el identificador del proyecto.
        oculto('id',$id);
        // Genera HTML para este componente: botón de acción. Los datos dinámicos se incorporan en esta expresión.
        echo '<label>Tu comentario<textarea name="comentario" required maxlength="2000" rows="3"></textarea></label><button class="btn btn-primary">Publicar comentario</button></form>';
    } else echo '<a href="' . e(url('inicio_sesion.php')) . '">Inicia sesión para comentar</a>';
    // Escribe el resultado o cierra el fragmento HTML que se está construyendo.
    echo '</section>';
}
