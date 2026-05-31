<?php
/**
 * Endpoint de diagnóstico máquina-máquina.
 * Usado por HAProxy/pfSense para balanceo.
 */
// restringe ip solo local
$ip_cliente = $_SERVER['REMOTE_ADDR'];
if (strpos($ip_cliente, '192.168.128.') !== 0 && strpos($ip_cliente, '10.10.') !== 0 && $ip_cliente !== '127.0.0.1') {
    http_response_code(403);
    exit('403 Forbidden - M2M Endpoint Only');
}

// fuerza salida limpia json
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

$estado = [];

// verifica cluster base datos
require_once __DIR__ . '/db_conexion.php';
$conn = @getDbConnection();
if ($conn) {
    $result = @mysqli_query($conn, "SELECT COUNT(*) as total FROM servidor WHERE estado='activo'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $estado['database_cluster'] = 'ok';
        $estado['active_containers'] = (int)$row['total'];
    } else {
        $estado['database_cluster'] = 'degraded';
    }
    mysqli_close($conn);
} else {
    $estado['database_cluster'] = 'error';
}

// verifica log auditoria local
$estado['audit_log_io'] = is_writable('/var/log/ytron_deploy.log') ? 'ok' : 'error';

// verifica llaves ssh locales
$key_ok = file_exists('/var/www/.ssh/id_ed25519');
$hosts_ok = file_exists('/var/www/.ssh/known_hosts');
$estado['ssh_identity_vault'] = ($key_ok && $hosts_ok) ? 'ok' : 'error';

// adjunta metadatos del nodo
$estado['timestamp'] = date('c'); // Formato estándar ISO 8601
$estado['node_id'] = gethostname();

// evalua estado salud general
$todo_ok = !in_array('error', $estado);

// emite codigo http proxy
http_response_code($todo_ok ? 200 : 503);

// renderiza json estado nodo
echo json_encode([
    'system_status' => $todo_ok ? 'healthy' : 'failing', 
    'components' => $estado
], JSON_PRETTY_PRINT);
?>