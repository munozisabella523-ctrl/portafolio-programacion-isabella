<?php
/*
 * ARCHIVO: controllers/acciones.php
 * PARA QUÉ SIRVE: Recibe formularios, comprueba permisos y ejecuta la acción solicitada dentro de una transacción.
 * DÓNDE SE UTILIZA: Resultados de guardar, moderar, calificar, comentar y participar; redirige a la pantalla correspondiente.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Exige respetar los tipos declarados en los argumentos; es una regla interna de PHP.
declare(strict_types=1);
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';
// Exige una petición POST con el token de la sesión. Se ejecuta antes de procesar los formularios.
validar_csrf();
// Prepara la cuenta o el identificador que se utiliza en esta operación.
$u = requiere_login();
// Lee la acción enviada para decidir qué operación debe ejecutarse.
$accion = $_POST['accion'] ?? '';
// Indica a qué página se volverá al terminar esta operación.
$destino = 'inicio.php';
$GLOBALS['archivos_nuevos'] = [];
// Agrupa operaciones que pueden fallar para poder informar del problema y recuperar el estado.
try {
    // Inicia una transacción: los cambios de este bloque se confirman juntos.
    base_datos()->begin_transaction();
    // Selecciona la operación solicitada por el formulario o la petición.
    switch ($accion) {
        // Guarda un experimento como borrador o pendiente de revisión, junto con sus fotografías. Se activa desde Mis proyectos.
        case 'experimento_guardar':
            // Obtiene el identificador del registro sobre el que se trabajará.
            $id = (int) ($_POST['id'] ?? 0);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'gestion_experimentos.php';
            // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
            if ($id) {
                // Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
                $e = fila('SELECT * FROM experimentos WHERE id=? FOR UPDATE', [$id]);
                // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
                if (!$e || !puede_editar_experimento($u, $e))denegar();
            }
            // Recoge o prepara el título que corresponde a este apartado o registro.
            $titulo = texto('titulo', 160);
            // Recoge la categoría asociada al contenido.
            $categoria = texto('categoria', 80);
            // Recoge la explicación del proyecto, avance o curiosidad.
            $descripcion = texto('descripcion');
            // Determina el estado que se guardará o se mostrará en esta operación.
            $estado = ($_POST['estado'] ?? '') === 'borrador' ? 'borrador' : 'pendiente';
            // Recoge las imágenes validadas que se asociarán a este registro.
            $fotos = subir_imagenes('imagenes', 'experimentos');
            // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
            if ($id)consulta('UPDATE experimentos SET titulo=?,categoria=?,descripcion=?,estado=? WHERE id=?', [$titulo, $categoria, $descripcion, $estado, $id]);
            else {
                // Añade información a experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
                consulta('INSERT INTO experimentos(usuario_id,titulo,categoria,descripcion,estado) VALUES(?,?,?,?,?)', [$u['id'], $titulo, $categoria, $descripcion, $estado]);
                // Obtiene el identificador del registro sobre el que se trabajará.
                $id = base_datos()->insert_id;
            }
            // Consulta experimento_imagenes. Fotografías y orden de cada experimento. Alimenta portadas y galerías.
            $orden = (int) valor('SELECT COALESCE(MAX(orden),0) FROM experimento_imagenes WHERE experimento_id=?', [$id]);
            // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
            foreach ($fotos as $f)consulta('INSERT INTO experimento_imagenes(experimento_id,archivo,orden) VALUES(?,?,?)', [$id, $f, ++$orden]);
            // Registra cambios realizados sobre un experimento. Alimenta el historial de proyectos.
            registrar_actividad_experimento((int) $u['id'], 'Proyecto guardado: ' . $estado, $id);
            // Comprueba los permisos de administración antes de continuar con esta acción.
            if (puede_moderar($u))registrar_actividad_administrador((int) $u['id'], 'Editó proyecto #' . $id);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'experimento.php?id=' . $id;
            // Prepara el aviso que verá el usuario después de enviar el formulario.
            $mensaje = $estado === 'borrador' ? 'Borrador guardado.' : 'Proyecto enviado para revisión.';
            break;
        // Quita la referencia de una imagen del experimento después de comprobar permisos. Se activa desde la edición del proyecto.
        case 'imagen_eliminar':
            // Consulta experimento_imagenes. Fotografías y orden de cada experimento. Alimenta portadas y galerías.
            $i = fila('SELECT * FROM experimento_imagenes WHERE id=?', [(int) ($_POST['id'] ?? 0)]);
            // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
            if (!$i)denegar(404);
            // Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
            $e = fila('SELECT * FROM experimentos WHERE id=? FOR UPDATE', [$i['experimento_id']]);
            // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
            if (!$e || !puede_editar_experimento($u, $e))denegar();
            // Elimina los registros seleccionados de experimento_imagenes. Fotografías y orden de cada experimento. Alimenta portadas y galerías.
            consulta('DELETE FROM experimento_imagenes WHERE id=?', [$i['id']]);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'gestion_experimentos.php?editar=' . $e['id'];
            break;
        // Elimina el experimento autorizado y registra la acción. Se activa con Eliminar proyecto.
        case 'experimento_eliminar':
            // Prepara $e con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $e = experimento((int) ($_POST['id'] ?? 0));
            // Reserva esta decisión a los permisos de superadministrador.
            if (!es_super_admin($u) && !((int) $e['usuario_id'] === (int) $u['id'] && puede_editar_experimento($u, $e)))denegar();
            // Registra cambios realizados sobre un experimento. Alimenta el historial de proyectos.
            registrar_actividad_experimento((int) $u['id'], 'Eliminación de proyecto', (int) $e['id']);
            // Reserva esta decisión a los permisos de superadministrador.
            if (es_super_admin($u))registrar_actividad_administrador((int) $u['id'], 'Eliminó proyecto #' . $e['id']);
            // Elimina los registros seleccionados de experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
            consulta('DELETE FROM experimentos WHERE id=?', [$e['id']]);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'gestion_experimentos.php';
            break;
        // Guarda el estado y la observación de revisión y avisa al autor. Se activa desde la moderación del experimento.
        case 'moderar':
            // Comprueba los permisos de administración antes de continuar con esta acción.
            if (!puede_moderar($u))denegar();
            // Prepara $e con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $e = experimento((int) ($_POST['id'] ?? 0));
            // Determina el estado que se guardará o se mostrará en esta operación.
            $estado = opcion('estado', ['aprobado', 'rechazado', 'cambios_solicitados', 'oculto']);
            // Prepara $observacion con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $observacion = texto('observacion', 3000, in_array($estado, ['rechazado', 'cambios_solicitados'], true));
            // Actualiza experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
            consulta('UPDATE experimentos SET estado=?,moderado_por=?,fecha_moderacion=NOW(),observacion_moderacion=? WHERE id=?', [$estado, $u['id'], $observacion, $e['id']]);
            // Añade información a moderaciones. Decisiones y observaciones de revisión de experimentos. Se utilizan en la moderación.
            consulta('INSERT INTO moderaciones(experimento_id,administrador_id,accion,observacion) VALUES(?,?,?,?)', [$e['id'], $u['id'], $estado, $observacion]);
            // Guarda acciones administrativas y la cuenta afectada, cuando corresponde. Se consultan en el historial administrativo.
            registrar_actividad_administrador((int) $u['id'], 'Proyecto #' . $e['id'] . ': ' . $estado, (int) $e['usuario_id']);
            // Guarda un aviso dirigido a una cuenta. Se muestra en Notificaciones.
            notificar((int) $e['usuario_id'], 'moderacion', 'Tu proyecto: ' . str_replace('_', ' ', $estado), $observacion ?: $e['titulo'], 'experimento.php?id=' . $e['id']);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'experimento.php?id=' . $e['id'];
            break;
        // Activa o desactiva el destacado del proyecto. Afecta sus etiquetas y los listados que muestran destacados.
        case 'destacar':
            // Comprueba los permisos de administración antes de continuar con esta acción.
            if (!puede_moderar($u))denegar();
            // Prepara $e con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $e = experimento((int) ($_POST['id'] ?? 0));
            // Actualiza experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
            consulta('UPDATE experimentos SET destacado=1-destacado WHERE id=?', [$e['id']]);
            // Guarda acciones administrativas y la cuenta afectada, cuando corresponde. Se consultan en el historial administrativo.
            registrar_actividad_administrador((int) $u['id'], 'Cambió destacado #' . $e['id']);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'experimento.php?id=' . $e['id'];
            break;
        // Guarda o actualiza la nota y el comentario del profesor. Se activa con Guardar evaluación en el proyecto.
        case 'calificar':
            // Consulta experimentos. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
            $e = fila('SELECT * FROM experimentos WHERE id=? FOR UPDATE', [(int) ($_POST['id'] ?? 0)]);
            // Muestra o permite la evaluación solo cuando la cuenta cumple los requisitos de profesor.
            if (!$e || !puede_calificar($u, $e))denegar();
            // Prepara $nota con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $nota = filter_var($_POST['nota'] ?? '', FILTER_VALIDATE_FLOAT);
            // Rechaza datos que no cumplen la validación y prepara un aviso para el usuario.
            if ($nota === false || $nota < NOTA_MIN || $nota > NOTA_MAX)throw new DomainException('La nota debe estar entre ' . NOTA_MIN . ' y ' . NOTA_MAX . '.');
            // Prepara $comentario con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $comentario = texto('comentario', 3000);
            // Añade información a calificaciones. Notas y comentarios del profesor vinculados a proyecto y estudiante. Se ven en Evaluaciones y Mi perfil.
            consulta('INSERT INTO calificaciones(experimento_id,profesor_id,estudiante_id,nota,comentario) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE nota=VALUES(nota),comentario=VALUES(comentario),fecha_actualizacion=NOW()', [$e['id'], $u['id'], $e['usuario_id'], round($nota, 2), $comentario]);
            // Guarda acciones de una cuenta. Se consultan en Mi panel e Historial.
            registrar_actividad_usuario((int) $u['id'], 'Calificó proyecto #' . $e['id'] . ' con ' . round($nota, 2));
            // Guarda un aviso dirigido a una cuenta. Se muestra en Notificaciones.
            notificar((int) $e['usuario_id'], 'calificacion', 'Nueva evaluación', $u['nombre'] . ' actualizó tu nota a ' . round($nota, 2), 'experimento.php?id=' . $e['id']);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'experimento.php?id=' . $e['id'];
            break;
        // Publica un comentario asociado al experimento o al avance permitido. Se activa desde el formulario de comentarios.
        case 'comentar':
            // Obtiene el identificador del registro sobre el que se trabajará.
            $id = (int) ($_POST['id'] ?? 0);
            // Prepara $tipo con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $tipo = opcion('tipo', ['experimento', 'avance']);
            // Prepara $comentario con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $comentario = texto('comentario', 2000);
            // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
            if ($tipo === 'experimento') {
                // Prepara $e con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
                $e = experimento($id);
                // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
                if ($e['estado'] !== 'aprobado')denegar();
                // Lee el identificador del autor para mostrar sus proyectos.
                $autor = (int) $e['usuario_id'];
                // Indica a qué página se volverá al terminar esta operación.
                $destino = 'experimento.php?id=' . $id;
                // Añade información a comentarios. Mensajes asociados a experimentos o avances, con autor y estado de visibilidad.
                consulta('INSERT INTO comentarios(usuario_id,experimento_id,comentario) VALUES(?,?,?)', [$u['id'], $id, $comentario]);
            } else {
                // Consulta avances_robotica. Entradas de progreso de los proyectos de robótica. Se muestran en su detalle.
                $a = fila('SELECT * FROM avances_robotica WHERE id=?', [$id]);
                // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
                if (!$a)denegar(404);
                // Prepara parámetros o datos usados por esta operación.
                $p = robotica((int) $a['proyecto_id']);
                // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
                if ($a['estado'] !== 'visible' || $p['moderacion'] !== 'aprobado')denegar();
                // Lee el identificador del autor para mostrar sus proyectos.
                $autor = (int) $a['usuario_id'];
                // Indica a qué página se volverá al terminar esta operación.
                $destino = 'proyecto_robotica.php?id=' . $p['id'] . '#avance-' . $id;
                // Añade información a comentarios. Mensajes asociados a experimentos o avances, con autor y estado de visibilidad.
                consulta('INSERT INTO comentarios(usuario_id,avance_robotica_id,comentario) VALUES(?,?,?)', [$u['id'], $id, $comentario]);
            }
            // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
            if ($autor !== (int) $u['id'])notificar($autor, 'comentario', 'Nuevo comentario', $u['nombre'] . ' comentó tu publicación.', $destino);
            break;
        // Procesa la retirada de un comentario según los permisos de la cuenta.
        case 'comentario_eliminar':
        // Cambia la visibilidad del comentario. Se activa desde las acciones de moderación.
        case 'comentario_moderar':
            // Consulta comentarios. Mensajes asociados a experimentos o avances, con autor y estado de visibilidad.
            $c = fila('SELECT * FROM comentarios WHERE id=?', [(int) ($_POST['id'] ?? 0)]);
            // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
            if (!$c)denegar(404);
            // Comprueba los permisos de administración antes de continuar con esta acción.
            if ($accion === 'comentario_moderar' && !puede_moderar($u))denegar();
            // Comprueba los permisos de administración antes de continuar con esta acción.
            if ($accion === 'comentario_eliminar' && !puede_moderar($u) && (int) $c['usuario_id'] !== (int) $u['id'])denegar();
            // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
            if ($accion === 'comentario_moderar')consulta('UPDATE comentarios SET estado=? WHERE id=?', [opcion('estado', ['visible', 'pendiente', 'oculto']), $c['id']]);
            else consulta('DELETE FROM comentarios WHERE id=?', [$c['id']]);
            // Comprueba los permisos de administración antes de continuar con esta acción.
            if (puede_moderar($u))registrar_actividad_administrador((int) $u['id'], 'Moderó comentario #' . $c['id']);
            // Consulta avances_robotica. Entradas de progreso de los proyectos de robótica. Se muestran en su detalle.
            $destino = $c['experimento_id'] ? 'experimento.php?id=' . $c['experimento_id'] : 'proyecto_robotica.php?id=' . valor('SELECT proyecto_id FROM avances_robotica WHERE id=?', [$c['avance_robotica_id']]);
            break;
        // Añade o quita un proyecto de los favoritos de la cuenta. Se refleja en el botón y en Mi panel.
        case 'favorito':
            // Prepara $e con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $e = experimento((int) ($_POST['id'] ?? 0));
            // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
            if ($e['estado'] !== 'aprobado')denegar();
            // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
            if (valor('SELECT 1 FROM favoritos WHERE usuario_id=? AND experimento_id=?', [$u['id'], $e['id']]))consulta('DELETE FROM favoritos WHERE usuario_id=? AND experimento_id=?', [$u['id'], $e['id']]);
            else consulta('INSERT INTO favoritos VALUES(?,?)', [$u['id'], $e['id']]);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'experimento.php?id=' . $e['id'];
            break;
        // Marca un aviso como leído. Se activa en Notificaciones.
        case 'notificacion_leer':
            // Actualiza notificaciones. Avisos destinados a una cuenta. Se consultan desde Notificaciones.
            consulta('UPDATE notificaciones SET leida=1 WHERE usuario_id=? AND (id=? OR ?=0)', [$u['id'], (int) ($_POST['id'] ?? 0), (int) ($_POST['id'] ?? 0)]);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'notificaciones.php';
            break;
        // Crea o actualiza un proyecto del Club de Robótica y su portada.
        case 'robotica_guardar':
            // Obtiene el identificador del registro sobre el que se trabajará.
            $id = (int) ($_POST['id'] ?? 0);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'club_robotica.php';
            // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
            if ($id) {
                // Prepara parámetros o datos usados por esta operación.
                $p = robotica($id);
                // Comprueba los permisos de administración antes de continuar con esta acción.
                if (!puede_moderar($u) && (int) $p['creador_id'] !== (int) $u['id'])denegar();
            }
            // Recoge o prepara el título que corresponde a este apartado o registro.
            $titulo = texto('titulo', 160);
            // Recoge la explicación del proyecto, avance o curiosidad.
            $descripcion = texto('descripcion');
            // Prepara $equipo con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $equipo = texto('equipo', 120);
            // Determina el estado que se guardará o se mostrará en esta operación.
            $estado = opcion('estado', ['idea', 'prototipo', 'programacion', 'pruebas', 'terminado']);
            // Prepara $f con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $f = subir_imagenes('portada', 'robotica', 1);
            // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
            if ($id)consulta("UPDATE proyectos_robotica SET titulo=?,descripcion=?,equipo=?,estado=?,portada=COALESCE(?,portada),moderacion='pendiente' WHERE id=?", [$titulo, $descripcion, $equipo, $estado, $f[0] ?? null, $id]);
            else {
                // Añade información a proyectos_robotica. Proyectos del club, sus responsables, portada y estado de revisión.
                consulta('INSERT INTO proyectos_robotica(titulo,descripcion,creador_id,equipo,estado,portada) VALUES(?,?,?,?,?,?)', [$titulo, $descripcion, $u['id'], $equipo, $estado, $f[0] ?? null]);
                // Obtiene el identificador del registro sobre el que se trabajará.
                $id = base_datos()->insert_id;
            }
            // Guarda acciones de una cuenta. Se consultan en Mi panel e Historial.
            registrar_actividad_usuario((int) $u['id'], 'Guardó proyecto de robótica #' . $id);
            // Comprueba los permisos de administración antes de continuar con esta acción.
            if (puede_moderar($u))registrar_actividad_administrador((int) $u['id'], 'Guardó proyecto de robótica #' . $id);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'proyecto_robotica.php?id=' . $id;
            // Prepara el aviso que verá el usuario después de enviar el formulario.
            $mensaje = 'Proyecto de robótica enviado para revisión.';
            break;
        // Gestiona la incorporación de la cuenta al equipo de un proyecto del club.
        case 'robotica_unirse':
            // Prepara parámetros o datos usados por esta operación.
            $p = robotica((int) ($_POST['id'] ?? 0));
            // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
            if ($p['moderacion'] !== 'aprobado')denegar();
            // Prepara SQL, asocia los valores de sus signos ? y lo ejecuta. Evita concatenar datos del formulario dentro de la consulta.
            consulta('INSERT IGNORE INTO miembros_robotica VALUES(?,?)', [$p['id'], $u['id']]);
            // Guarda un aviso dirigido a una cuenta. Se muestra en Notificaciones.
            notificar((int) $p['creador_id'], 'robotica', 'Nuevo integrante', $u['nombre'] . ' se unió al equipo.', 'proyecto_robotica.php?id=' . $p['id']);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'proyecto_robotica.php?id=' . $p['id'];
            break;
        // Guarda un avance de robótica y sus fotografías. Aparece en el detalle del proyecto según su estado.
        case 'avance_guardar':
            // Prepara parámetros o datos usados por esta operación.
            $p = robotica((int) ($_POST['id'] ?? 0));
            // Comprueba los permisos de administración antes de continuar con esta acción.
            if (!miembro_robotica($u, $p) && !puede_moderar($u))denegar();
            // Lee el filtro de fecha recibido por la dirección.
            $fecha = texto('fecha_avance', 10);
            // Prepara $d con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $d = DateTime::createFromFormat('!Y-m-d', $fecha);
            // Rechaza datos que no cumplen la validación y prepara un aviso para el usuario.
            if (!$d || $d->format('Y-m-d') !== $fecha)throw new DomainException('La fecha no es válida.');
            // Recoge o prepara el título que corresponde a este apartado o registro.
            $titulo = texto('titulo', 160);
            // Prepara $desc con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $desc = texto('descripcion');
            // Recoge las imágenes validadas que se asociarán a este registro.
            $fotos = subir_imagenes('imagenes', 'robotica');
            // Añade información a avances_robotica. Entradas de progreso de los proyectos de robótica. Se muestran en su detalle.
            consulta('INSERT INTO avances_robotica(proyecto_id,usuario_id,titulo,descripcion,fecha_avance) VALUES(?,?,?,?,?)', [$p['id'], $u['id'], $titulo, $desc, $fecha]);
            // Obtiene el identificador del registro sobre el que se trabajará.
            $id = base_datos()->insert_id;
            // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
            foreach ($fotos as $i => $f)consulta('INSERT INTO avances_robotica_imagenes(avance_id,archivo,orden) VALUES(?,?,?)', [$id, $f, $i]);
            // Guarda acciones de una cuenta. Se consultan en Mi panel e Historial.
            registrar_actividad_usuario((int) $u['id'], 'Publicó avance de robótica #' . $id);
            // Guarda un aviso dirigido a una cuenta. Se muestra en Notificaciones.
            notificar((int) $p['creador_id'], 'robotica', 'Nuevo avance por revisar', $titulo, 'proyecto_robotica.php?id=' . $p['id']);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'proyecto_robotica.php?id=' . $p['id'];
            // Prepara el aviso que verá el usuario después de enviar el formulario.
            $mensaje = 'Avance enviado para revisión.';
            break;
        // Procesa la revisión de un proyecto de robótica.
        case 'robotica_moderar':
        // Procesa la revisión de un avance de robótica.
        case 'avance_moderar':
            // Comprueba los permisos de administración antes de continuar con esta acción.
            if (!puede_moderar($u))denegar();
            // Obtiene el identificador del registro sobre el que se trabajará.
            $id = (int) ($_POST['id'] ?? 0);
            // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
            if ($accion === 'robotica_moderar') {
                // Prepara parámetros o datos usados por esta operación.
                $p = robotica($id);
                // Determina el estado que se guardará o se mostrará en esta operación.
                $estado = opcion('estado', ['aprobado', 'rechazado', 'oculto']);
                // Actualiza proyectos_robotica. Proyectos del club, sus responsables, portada y estado de revisión.
                consulta('UPDATE proyectos_robotica SET moderacion=? WHERE id=?', [$estado, $id]);
                // Lee el identificador del autor para mostrar sus proyectos.
                $autor = $p['creador_id'];
            } else {
                // Consulta avances_robotica. Entradas de progreso de los proyectos de robótica. Se muestran en su detalle.
                $a = fila('SELECT * FROM avances_robotica WHERE id=?', [$id]);
                // Interrumpe la petición si falla esta comprobación de acceso o de existencia del recurso.
                if (!$a)denegar(404);
                // Prepara parámetros o datos usados por esta operación.
                $p = robotica((int) $a['proyecto_id']);
                // Determina el estado que se guardará o se mostrará en esta operación.
                $estado = opcion('estado', ['visible', 'oculto']);
                // Actualiza avances_robotica. Entradas de progreso de los proyectos de robótica. Se muestran en su detalle.
                consulta('UPDATE avances_robotica SET estado=? WHERE id=?', [$estado, $id]);
                // Lee el identificador del autor para mostrar sus proyectos.
                $autor = $a['usuario_id'];
            }
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'proyecto_robotica.php?id=' . $p['id'];
            // Guarda acciones administrativas y la cuenta afectada, cuando corresponde. Se consultan en el historial administrativo.
            registrar_actividad_administrador((int) $u['id'], $accion . ' #' . $id . ': ' . $estado);
            // Guarda un aviso dirigido a una cuenta. Se muestra en Notificaciones.
            notificar((int) $autor, 'robotica', 'Moderación del Club de Robótica', 'Estado actualizado: ' . $estado, $destino);
            break;
        // Guarda una curiosidad, su imagen y su visibilidad. Se activa en Administración > Datos científicos.
        case 'dato_guardar':
            // Comprueba los permisos de administración antes de continuar con esta acción.
            if (!puede_moderar($u))denegar();
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'administracion/datos_cientificos.php';
            // Obtiene el identificador del registro sobre el que se trabajará.
            $id = (int) ($_POST['id'] ?? 0);
            // Prepara $t con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $t = texto('titulo', 160);
            // Prepara $d con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $d = texto('descripcion');
            // Lee la categoría recibida por un enlace de proyectos relacionados.
            $cat = texto('categoria', 80);
            // Prepara $f con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $f = subir_imagenes('imagen', 'ciencia', 1);
            // Prepara $activo con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $activo = isset($_POST['activo']) ? 1 : 0;
            // Comprueba esta condición antes de ejecutar el bloque que sigue; la alternativa conserva el comportamiento previsto.
            if ($id)consulta('UPDATE datos_cientificos SET titulo=?,descripcion=?,categoria=?,activo=?,imagen=COALESCE(?,imagen) WHERE id=?', [$t, $d, $cat, $activo, $f[0] ?? null, $id]);
            else consulta('INSERT INTO datos_cientificos(titulo,descripcion,categoria,activo,imagen,creado_por) VALUES(?,?,?,?,?,?)', [$t, $d, $cat, $activo, $f[0] ?? null, $u['id']]);
            // Guarda acciones administrativas y la cuenta afectada, cuando corresponde. Se consultan en el historial administrativo.
            registrar_actividad_administrador((int) $u['id'], 'Guardó dato científico #' . ($id ?: base_datos()->insert_id));
            break;
        // Registra un reconocimiento del experimento. Se refleja en el proyecto y en los listados correspondientes.
        case 'reconocimiento':
            // Comprueba los permisos de administración antes de continuar con esta acción.
            if (!puede_moderar($u))denegar();
            // Prepara $e con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
            $e = experimento((int) ($_POST['id'] ?? 0));
            // Añade información a reconocimientos. Distinciones asociadas a experimentos. Se muestran en el proyecto y en la feria.
            consulta('INSERT INTO reconocimientos(experimento_id,titulo,descripcion) VALUES(?,?,?)', [$e['id'], texto('titulo', 120), texto('descripcion', 2000)]);
            // Guarda acciones administrativas y la cuenta afectada, cuando corresponde. Se consultan en el historial administrativo.
            registrar_actividad_administrador((int) $u['id'], 'Reconocimiento para #' . $e['id']);
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'experimento.php?id=' . $e['id'];
            break;
        // Guarda opciones autorizadas al superadministrador. Se activa en Administración > Sistema.
        case 'configuracion':
            // Reserva esta decisión a los permisos de superadministrador.
            if (!es_super_admin($u))denegar();
            // Actualiza configuracion. Opciones generales guardadas desde Administración > Sistema.
            consulta("UPDATE configuracion SET valor=? WHERE clave='mensaje_comunidad'", [texto('mensaje_comunidad', 500)]);
            // Guarda acciones administrativas y la cuenta afectada, cuando corresponde. Se consultan en el historial administrativo.
            registrar_actividad_administrador((int) $u['id'], 'Actualizó configuración global');
            // Indica a qué página se volverá al terminar esta operación.
            $destino = 'administracion/configuracion.php';
            break;
        default: denegar(400);
    }
    // Confirma en la base de datos los cambios que han terminado correctamente.
    base_datos()->commit();
    // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
    mensaje_temporal('success', $mensaje ?? 'Cambios guardados correctamente.');
} catch (DomainException $e) {
    // Revierte los cambios de la transacción si la operación no pudo completarse.
    base_datos()->rollback();
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach ($GLOBALS['archivos_nuevos'] as $f)@unlink(ruta_base('uploads/' . $f));
    // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
    mensaje_temporal('error',$e->getMessage());
} catch (Throwable $e) {
    // Revierte los cambios de la transacción si la operación no pudo completarse.
    base_datos()->rollback();
    // Recorre los elementos para procesarlos o mostrar uno por uno los registros de este apartado.
    foreach ($GLOBALS['archivos_nuevos'] as $f)@unlink(ruta_base('uploads/' . $f));
    // Registra el error internamente para revisar problemas sin mostrar detalles técnicos al visitante.
    error_log((string) $e);
    // Guarda un aviso en la sesión para mostrarlo una sola vez al volver a una página.
    mensaje_temporal('error','No pudimos guardar los cambios. Revisa los datos e inténtalo nuevamente.');
}
// Construye una dirección interna y redirige hacia ella. Se usa al terminar formularios o comprobar permisos.
ir($destino);
