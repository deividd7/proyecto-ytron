<?php
/**
 * API asíncrona para consola RCON.
 * Envía comandos y lee logs vía SSH.
 */
// fuerza salida json limpia
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/sesion_db.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Sesión expirada.']);
    exit();
}

$server_id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$accion = $_POST['accion'] ?? '';

if (!$server_id || !in_array($accion, ['leer', 'comando'])) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Llamada corrupta.']);
    exit();
}

$container_name = "srv_" . $server_id;

// tunel ssh optimizado rcon
$ssh_prefix = "HOME=/var/www /usr/bin/ssh -o ConnectTimeout=2 -o BatchMode=yes -i /var/www/.ssh/id_ed25519 -o StrictHostKeyChecking=no ytronadm@10.10.40.10 ";

if ($accion === 'leer') {
    // obtiene logs de docker
    $cmd = $ssh_prefix . "sudo /usr/bin/docker logs --tail 120 " . escapeshellarg($container_name) . " 2>&1";
    exec($cmd, $output, $return_code);

    ob_clean();
    echo json_encode(['status' => 'success', 'logs' => $output]);
    exit();
}

if ($accion === 'comando') {
    $comando_usuario = trim($_POST['comando'] ?? '');
    if (empty($comando_usuario)) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Línea vacía.']);
        exit();
    }

    // doble escape comando ssh
    // sanitiza input usuario
    $cmd_seguro = escapeshellarg($comando_usuario);
    // prepara exec en docker
    $cmd_remoto = "sudo /usr/bin/docker exec {$container_name} rcon-cli {$cmd_seguro}";
    // encapsula llamada ssh
    $cmd = $ssh_prefix . escapeshellarg($cmd_remoto) . " 2>&1";

    exec($cmd, $output, $return_code);

    ob_clean();
    echo json_encode(['status' => 'success', 'output' => $output]);
    exit();
}
?>