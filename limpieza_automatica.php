<?php
/**
 * limpieza_automatica.php
 * Cron de limpieza de contenedores expirados.
 * Libera recursos destruyendo servidores caducados.
 */
require_once __DIR__ . '/db_conexion.php';
$conexion = getDbConnection();

// busca servidores vencidos bd
$sql = "SELECT s.id as servidor_id, s.uuid, f.id as factura_id
        FROM servidor s
        JOIN factura f ON s.usuario_id = f.usuario_id
        JOIN plan p ON s.plan_id = p.id
        JOIN linea l ON l.factura_id = f.id AND l.concepto = p.nombre
        WHERE f.fecha_vencimiento < CURDATE()";

$resultado = mysqli_query($conexion, $sql);

$log_msgs = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $servidor_id = $row['servidor_id'];
    $container_name = "srv_" . $servidor_id;

    // destruye contenedor por ssh
    $cmd_ssh = "HOME=/var/www /usr/bin/ssh -o BatchMode=yes -o ConnectTimeout=5 ytronadm@10.10.40.10 'sudo /usr/bin/docker rm -f " . escapeshellarg($container_name) . "' 2>&1";
    exec($cmd_ssh, $output, $return_code);

    $log_line = date('Y-m-d H:i:s') . " | CRON_CLEANUP | Servidor ID: {$servidor_id} | Retorno: {$return_code} | Salida: " . implode(" ", $output) . "\n";
    file_put_contents('/var/log/ytron_deploy.log', $log_line, FILE_APPEND);

    // borra servidor base datos
    $stmt_del = mysqli_prepare($conexion, "DELETE FROM servidor WHERE id = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $servidor_id);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);

    // anula factura del servidor
    $stmt_upd = mysqli_prepare($conexion, "UPDATE factura SET pagada = 0 WHERE id = ?");
    mysqli_stmt_bind_param($stmt_upd, "i", $row['factura_id']);
    mysqli_stmt_execute($stmt_upd);
    mysqli_stmt_close($stmt_upd);

    $log_msgs[] = "Eliminado servidor ID " . $servidor_id;
}

mysqli_close($conexion);

echo json_encode([
    'status' => 'success', 
    'message' => empty($log_msgs) ? 'No hay servidores expirados para limpiar.' : implode(", ", $log_msgs)
]);
?>
