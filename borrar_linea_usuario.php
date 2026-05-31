<?php
/**
 * Destruye contenedor docker expirado vía SSH.
 * Elimina registro de BD y marca factura cancelada.
 */
    require_once __DIR__ . '/sesion_db.php';
    session_start();
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) { header("Location: home.php"); exit(); }

    $linea_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

    if ($linea_id) {
        require_once __DIR__ . '/db_conexion.php';
        $conexion = getDbConnection();

        // obtiene datos linea antes borrar
        $sql_info = "SELECT l.id as linea_id, l.concepto, l.factura_id, f.usuario_id 
                     FROM linea l 
                     JOIN factura f ON l.factura_id = f.id 
                     WHERE l.id = ?";
        $stmt_info = mysqli_prepare($conexion, $sql_info);
        mysqli_stmt_bind_param($stmt_info, "i", $linea_id);
        mysqli_stmt_execute($stmt_info);
        $linea_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_info));
        mysqli_stmt_close($stmt_info);

        if ($linea_data) {
            // busca servidor asociado al plan
            $sql_find = "SELECT s.id 
                         FROM servidor s 
                         JOIN plan p ON s.plan_id = p.id 
                         WHERE s.usuario_id = ? AND p.nombre = ? 
                         LIMIT 1";
            $stmt_f = mysqli_prepare($conexion, $sql_find);
            mysqli_stmt_bind_param($stmt_f, "is", $linea_data['usuario_id'], $linea_data['concepto']);
            mysqli_stmt_execute($stmt_f);
            $res = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_f));
            mysqli_stmt_close($stmt_f);

            if ($res) {
                $servidor_id = $res['id'];
                $container_name = "srv_" . $servidor_id;
                
                // destruye contenedor docker por ssh
                // asegura entorno ssh web
                $cmd_ssh = "HOME=/var/www /usr/bin/ssh -o BatchMode=yes -o ConnectTimeout=5 ytronadm@10.10.40.10 'sudo /usr/bin/docker rm -f " . escapeshellarg($container_name) . "' 2>&1";
                exec($cmd_ssh, $output, $return_code);

                // escribe log de auditoria
                $log_line = date('Y-m-d H:i:s') . " | DELETE_SERVER | Servidor ID: {$servidor_id} | Retorno: {$return_code} | Salida: " . implode(" ", $output) . "\n";
                file_put_contents('/var/log/ytron_deploy.log', $log_line, FILE_APPEND);

                // borra registro servidor base datos
                $stmt_del_srv = mysqli_prepare($conexion, "DELETE FROM servidor WHERE id = ?");
                mysqli_stmt_bind_param($stmt_del_srv, "i", $servidor_id);
                mysqli_stmt_execute($stmt_del_srv);
                mysqli_stmt_close($stmt_del_srv);
            }

            // mantiene linea factura historial
            // marca factura como no pagada
            $stmt_upd = mysqli_prepare($conexion, "UPDATE factura SET pagada = 0 WHERE id = ?");
            mysqli_stmt_bind_param($stmt_upd, "i", $linea_data['factura_id']);
            mysqli_stmt_execute($stmt_upd);
            mysqli_stmt_close($stmt_upd);
        }

        mysqli_close($conexion);
        header("Location: lista_planes_usuarios.php?eliminado=exito");
        exit();
    } else {
        header("Location: lista_planes_usuarios.php?error=fallo");
        exit();
    }
?>