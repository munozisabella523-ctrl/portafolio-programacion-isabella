<?php
/* ARCHIVO: tools/probar_chat.php
 * VERIFICACIÓN DEL CHAT DESDE TERMINAL
 * Crea datos de prueba, comprueba permisos por HTTP y prueba una respuesta de Ollama.
 * El bloque finally elimina únicamente los datos y archivos temporales creados por esta prueba.
 */
// Pruebas de permisos y persistencia HTTP. Ejecutar únicamente desde terminal.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
session_save_path(sys_get_temp_dir());
require __DIR__ . '/../includes/chat_historial.php';
session_destroy();
function comprobar(bool $ok, string $mensaje): void { if (!$ok) throw new RuntimeException($mensaje); }
$usuariosPrueba = [];
$conversacionesPrueba = [];
$sesionesPrueba = [];
$cookieFiles = [];
function httpChat(string $ruta, string $cookieFile, ?array $body = null, string $session = ''): array {
    $curl = curl_init('http://localhost/mundo/' . $ruta);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_COOKIEFILE=>$cookieFile, CURLOPT_COOKIEJAR=>$cookieFile, CURLOPT_TIMEOUT=>105]);
    if ($session) curl_setopt($curl, CURLOPT_COOKIE, 'PHPSESSID=' . $session);
    if ($body !== null) {
        $login = str_contains($ruta, 'gestion_sesion');
        curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Content-Type: '.($login?'application/x-www-form-urlencoded':'application/json')],CURLOPT_POSTFIELDS=>$login?http_build_query($body):json_encode($body)]);
    }
    $r = curl_exec($curl); $status = curl_getinfo($curl,CURLINFO_HTTP_CODE); curl_close($curl);
    return [$status, json_decode($r ?: '',true), $r];
}
try {
    $prefijo = 'chat_test_' . bin2hex(random_bytes(6));
    foreach (['estudiante','profesor','administrador','administrador','super_administrador'] as $i=>$rol) {
        $nombre = $prefijo . '_' . $i;
        $password = bin2hex(random_bytes(20));
        consulta('INSERT INTO usuarios(nombre,usuario,correo,contrasena,rol) VALUES(?,?,?,?,?)',[$nombre,$nombre,$nombre.'@example.invalid',password_hash($password,PASSWORD_DEFAULT),$rol]);
        $uid = (int)base_datos()->insert_id; $usuariosPrueba[] = $uid;
        $u = ['id'=>$uid,'rol'=>$rol];
        $cid = chat_crear(['usuario'=>$u,'hash'=>null]); $conversacionesPrueba[] = $cid;
        chat_guardar($cid,'user','Prueba privada ' . $i);
        $sid = '';
        $cookies = tempnam(sys_get_temp_dir(),'mcchat'); $cookieFiles[]=$cookies;
        [$s,$anonimo] = httpChat('controllers/chat.php',$cookies);
        comprobar($s===200 && isset($anonimo['csrf']), 'CSRF de login');
        [$s] = httpChat('controllers/gestion_sesion.php',$cookies,['action'=>'iniciar_sesion','correo'=>$nombre.'@example.invalid','contrasena'=>$password,'csrf'=>$anonimo['csrf']]);
        comprobar($s===302, 'Inicio de sesión de prueba');
        $identidades[] = [$u,$cid,$sid,$cookies];
    }
    $cookies = tempnam(sys_get_temp_dir(),'mcchat'); $cookieFiles[]=$cookies;
    [$s,$guest] = httpChat('controllers/chat.php',$cookies);
    comprobar($s===200 && isset($guest['csrf']), 'Inicialización visitante');
    [$s,$nueva] = httpChat('controllers/chat.php',$cookies,['accion'=>'nueva','csrf'=>$guest['csrf'],'identidad'=>$guest['identidad']]);
    comprobar($s===200 && !empty($nueva['id']), 'Crear conversación visitante');
    $gid=(int)$nueva['id']; $conversacionesPrueba[]=$gid;
    foreach ($identidades as $i=>[$u,$cid,$sid,$jar]) {
        [$s,$datos] = httpChat('controllers/chat.php',$jar,null,$sid);
        comprobar($s===200 && count($datos['conversaciones'])===1 && (int)$datos['id']===$cid, 'Chat propio rol '.$i);
        foreach ($conversacionesPrueba as $j=>$objetivo) {
            [$s] = httpChat('historial_chat.php?id='.$objetivo,$jar,null,$sid);
            $permitido = $i===4 || $j===$i || ($i===2 || $i===3) && in_array($j,[0,1,5],true);
            comprobar($s===($permitido?200:404), 'Detalle por rol '.$i.' objetivo '.$j.' status '.$s);
        }
        [$s] = httpChat('controllers/chat.php?id='.$gid,$jar,null,$sid);
        comprobar($s===404,'API rechaza conversación visitante ajena');
        [$s,,$html] = httpChat('dashboard.php',$jar,null,$sid);
        comprobar($s===200 && str_contains($html,'id="resumen-chat"') && str_contains($html,'id="botonChat"'),'Panel y widget rol '.$i);
        [$s] = httpChat('controllers/chat.php',$jar,['id'=>$gid,'texto'=>'No permitido','csrf'=>$datos['csrf'],'identidad'=>$datos['identidad']],$sid);
        comprobar($s===404,'POST ajeno rechazado');
    }
    [$s] = httpChat('controllers/chat.php',$cookies,['id'=>$gid,'texto'=>'Sin CSRF']);
    comprobar($s===403,'CSRF obligatorio');
    [$s] = httpChat('controllers/chat.php?id='.$conversacionesPrueba[0],$cookies);
    comprobar($s===404,'Visitante aislado');
    [$s,$respuesta] = httpChat('controllers/chat.php',$cookies,['id'=>$gid,'texto'=>'Responde únicamente: Ciencia.','csrf'=>$guest['csrf'],'identidad'=>$guest['identidad']]);
    comprobar($s===200 && empty($respuesta['error']),'Respuesta real de Ollama: '.($respuesta['error']??'HTTP '.$s));
    [$s,$historial] = httpChat('controllers/chat.php?id='.$gid,$cookies);
    comprobar($s===200 && count($historial['mensajes'])===2 && $historial['mensajes'][1]['rol']==='assistant','Historial persistente');
    echo "OK: cinco cuentas, matriz de permisos, paneles, aislamiento visitante, CSRF y respuesta real persistida.\n";
} finally {
    foreach ($conversacionesPrueba as $cid) consulta('DELETE FROM chat_conversaciones WHERE id=?',[$cid]);
    foreach ($usuariosPrueba as $uid) consulta('DELETE FROM usuarios WHERE id=?',[$uid]);
    foreach ($cookieFiles as $archivo) if (is_file($archivo)) unlink($archivo);
}
