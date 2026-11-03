<?php
/* ARCHIVO: tools/recuperar_experimentos.php
 * RECUPERACIÓN SELECTIVA DESDE TERMINAL
 * Lee el respaldo en tablas temporales y relaciona autores, proyectos, fotos y comentarios.
 * Sin --aplicar simula la recuperación y revierte la transacción; con --aplicar la confirma.
 */
// Recuperación selectiva del respaldo local; nunca reemplaza tablas ni registros.
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/base_datos.php';
$db = base_datos();
$sql = file_get_contents(__DIR__ . '/../ciencia (2).sql');
$tablas = ['usuarios', 'experimentos', 'experimento_imagenes', 'comentarios', 'moderaciones'];
foreach ($tablas as $tabla) {
    $db->query("CREATE TEMPORARY TABLE recuperacion_$tabla LIKE $tabla");
    if (preg_match('/^INSERT INTO `' . $tabla . '` .*?;\s*$/ms', $sql, $m)) {
        $db->query(str_replace('INSERT INTO `' . $tabla . '`', 'INSERT INTO `recuperacion_' . $tabla . '`', $m[0]));
    }
}
function buscar(string $sql, array $valores): ?array {
    global $db;
    $sentencia = $db->prepare($sql);
    $sentencia->execute($valores);
    return $sentencia->get_result()->fetch_assoc();
}
function insertar(string $tabla, array $fila): int {
    global $db;
    unset($fila['id']);
    $columnas = '`' . implode('`,`', array_keys($fila)) . '`';
    $marcas = implode(',', array_fill(0, count($fila), '?'));
    $sentencia = $db->prepare("INSERT INTO $tabla ($columnas) VALUES ($marcas)");
    $sentencia->execute(array_values($fila));
    return (int) $db->insert_id;
}
$usuarios = [];
function recuperar_usuario(int $id): int {
    global $usuarios;
    if (isset($usuarios[$id])) return $usuarios[$id];
    $usuario = buscar('SELECT * FROM recuperacion_usuarios WHERE id=?', [$id]);
    if (!$usuario) throw new RuntimeException('Falta un autor en el respaldo');
    $porUsuario = buscar('SELECT * FROM usuarios WHERE usuario=?', [$usuario['usuario']]);
    $porCorreo = buscar('SELECT * FROM usuarios WHERE correo=?', [$usuario['correo']]);
    if (($porUsuario || $porCorreo) && (!$porUsuario || !$porCorreo || $porUsuario['id'] !== $porCorreo['id'])) {
        throw new RuntimeException('Conflicto de identidad: revisar antes de recuperar');
    }
    return $usuarios[$id] = $porUsuario ? (int) $porUsuario['id'] : insertar('usuarios', $usuario);
}
$db->begin_transaction();
try {
    $mapa = [];
    $nuevos = 0;
    foreach ($db->query('SELECT * FROM recuperacion_experimentos ORDER BY id')->fetch_all(MYSQLI_ASSOC) as $e) {
        $original = (int) $e['id'];
        $e['usuario_id'] = recuperar_usuario((int) $e['usuario_id']);
        if ($e['moderado_por'] !== null) $e['moderado_por'] = recuperar_usuario((int) $e['moderado_por']);
        $existente = buscar('SELECT id FROM experimentos WHERE usuario_id=? AND titulo=? AND fecha_creacion=?', [$e['usuario_id'], $e['titulo'], $e['fecha_creacion']]);
        $mapa[$original] = $existente ? (int) $existente['id'] : insertar('experimentos', $e);
        if (!$existente) $nuevos++;
    }
    foreach (['experimento_imagenes', 'comentarios', 'moderaciones'] as $tabla) {
        foreach ($db->query("SELECT * FROM recuperacion_$tabla ORDER BY id")->fetch_all(MYSQLI_ASSOC) as $fila) {
            if (!isset($mapa[$fila['experimento_id']])) continue;
            $fila['experimento_id'] = $mapa[$fila['experimento_id']];
            if ($tabla === 'experimento_imagenes') {
                if (!is_file(__DIR__ . '/../uploads/' . $fila['archivo'])) throw new RuntimeException('Falta una imagen original');
                $existe = buscar('SELECT id FROM experimento_imagenes WHERE experimento_id=? AND archivo=?', [$fila['experimento_id'], $fila['archivo']]);
            } elseif ($tabla === 'comentarios') {
                if ($fila['avance_robotica_id'] !== null) throw new RuntimeException('Comentario con referencia adicional');
                $fila['usuario_id'] = recuperar_usuario((int) $fila['usuario_id']);
                $existe = buscar('SELECT id FROM comentarios WHERE experimento_id=? AND usuario_id=? AND comentario=? AND fecha_creacion=?', [$fila['experimento_id'], $fila['usuario_id'], $fila['comentario'], $fila['fecha_creacion']]);
            } else {
                $fila['administrador_id'] = recuperar_usuario((int) $fila['administrador_id']);
                $existe = buscar('SELECT id FROM moderaciones WHERE experimento_id=? AND administrador_id=? AND accion=? AND fecha=?', [$fila['experimento_id'], $fila['administrador_id'], $fila['accion'], $fila['fecha']]);
            }
            if (!$existe) insertar($tabla, $fila);
        }
    }
    if (in_array('--aplicar', $argv, true)) {
        $db->commit();
        echo "Recuperación confirmada: $nuevos experimentos nuevos.\n";
    } else {
        $db->rollback();
        echo "Simulación correcta: $nuevos experimentos recuperables. Usa --aplicar para confirmar.\n";
    }
} catch (Throwable $e) {
    $db->rollback();
    throw $e;
}

