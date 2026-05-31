<?php
/**
 * control_servidor.php
 * Pasarela de control de contenedores en red GAME.
 * Envía señales de inicio/parada por SSH.
 */
    header('Content-Type: application/json');
    require_once __DIR__ . '/sesion_db.php';
    session_start();

    // valida sesion y entrada
    if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Acceso denegado. Sesión no válida.']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Método de transferencia no permitido.']);
        exit();
    }

    $server_id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $accion    = $_POST['accion'] ?? '';

    if (!$server_id || !in_array($accion, ['start', 'stop'])) {
        echo json_encode(['status' => 'error', 'message' => 'Parámetros de invocación corruptos o insuficientes.']);
        exit();
    }

    // comprueba propiedad base datos
    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    // comprueba pertenencia servidor
    if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1) {
        $sql = "SELECT s.id, s.estado FROM servidor s WHERE s.id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $server_id);
    } else {
        $sql = "SELECT s.id, s.estado FROM servidor s WHERE s.id = ? AND s.usuario_id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $server_id, $_SESSION['usuario_id']);
    }
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $servidor = mysqli_fetch_assoc($resultado);

    if (!$servidor) {
        echo json_encode(['status' => 'error', 'message' => 'La instancia solicitada no existe o no dispone de privilegios sobre ella.']);
        mysqli_stmt_close($stmt);
        mysqli_close($conexion);
        exit();
    }
    mysqli_stmt_close($stmt);

    // ejecuta instruccion por ssh
    // patron nombre contenedor docker
    $container_name = "srv_" . $server_id;
    $nodo_game_ip   = "10.10.40.10";

    // prepara comando docker seguro
    if ($accion === 'start') {
        $cmd_docker = "sudo /usr/bin/docker start " . escapeshellarg($container_name);
        $nuevo_estado = "activo";
    } else {
        $cmd_docker = "sudo /usr/bin/docker stop " . escapeshellarg($container_name);
        $nuevo_estado = "apagado";
    }

    // construye tunel ssh blindado
    $cmd_ssh = "HOME=/var/www /usr/bin/ssh -o BatchMode=yes -o ConnectTimeout=5 "
             . escapeshellarg($nodo_game_ip) . " "
             . $cmd_docker
             . " 2>&1";

    $output = [];
    $return_code = 0;
    
    // ejecuta shell aislado
    exec($cmd_ssh, $output, $return_code);

    // guarda log auditoria local
    $log_line = date('Y-m-d H:i:s') . " | MANUAL_CONTROL | Servidor ID: {$server_id} | Acción: {$accion} | Código Retorno: {$return_code} | Salida: " . implode(" ", $output) . "\n";
    file_put_contents('/var/log/ytron_deploy.log', $log_line, FILE_APPEND);

    // procesa actualiza estado bd
    if ($return_code === 0) {
        // sincroniza estado en bd
        $sql_update = "UPDATE servidor SET estado = ? WHERE id = ?";
        $stmt_u = mysqli_prepare($conexion, $sql_update);
        mysqli_stmt_bind_param($stmt_u, "si", $nuevo_estado, $server_id);
        mysqli_stmt_execute($stmt_u);
        mysqli_stmt_close($stmt_u);

        $msg_final = $accion === 'start' ? 'El servidor se ha encendido correctamente.' : 'El servidor se ha apagado y detenido.';
        echo json_encode(['status' => 'success', 'message' => $msg_final]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'El Nodo de Juego denegó o falló al procesar la instrucción de control Docker. Revisa /var/log/ytron_deploy.log']);
    }

    mysqli_close($conexion);
    exit();
?>