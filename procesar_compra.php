<?php 
/**
 * Motor transaccional de compras.
 * Procesa pagos y aprovisiona servidores (Zero-Touch).
 */
    require_once __DIR__ . '/sesion_db.php';
    session_start();
    if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }
    if (empty($_SESSION['carrito'])) { header("Location: perfil.php"); exit(); }

    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    $usuario_id = $_SESSION['usuario_id'];
    $fecha_vencimiento = date('Y-m-d', strtotime('+1 month'));

    mysqli_begin_transaction($conexion);

    try {
        // marca factura como pagada
        $sql_factura = "INSERT INTO factura (fecha_vencimiento, pagada, usuario_id) VALUES (?, 1, ?)";
        $stmt_f = mysqli_prepare($conexion, $sql_factura);
        mysqli_stmt_bind_param($stmt_f, "si", $fecha_vencimiento, $usuario_id);
        mysqli_stmt_execute($stmt_f);
        $factura_id = mysqli_insert_id($conexion);
        $planes_comprados = [];

        foreach ($_SESSION['carrito'] as $id_plan => $item) {
            $planes_comprados[] = $item['nombre'];
            $sql_linea = "INSERT INTO linea (factura_id, concepto, cantidad, precio_ud) VALUES (?, ?, 1, ?)";
            $stmt_l = mysqli_prepare($conexion, $sql_linea);
            mysqli_stmt_bind_param($stmt_l, "isd", $factura_id, $item['nombre'], $item['precio']);
            mysqli_stmt_execute($stmt_l);

            // crea registro servidor bd
            $nodo_id = 1;
            $uuid = bin2hex(random_bytes(16));
            $plan_id = $id_plan; 

            $sql_servidor = "
                INSERT INTO servidor (uuid, ip, puerto, estado, usuario_id, nodo_id, plan_id)
                VALUES (?, NULL, NULL, 'provisionando', ?, ?, ?)
            ";
            $stmt_s = mysqli_prepare($conexion, $sql_servidor);
            mysqli_stmt_bind_param($stmt_s, "siii", $uuid, $usuario_id, $nodo_id, $plan_id);
            mysqli_stmt_execute($stmt_s);

            $servidor_id = mysqli_insert_id($conexion);

            // asigna puerto logico dinamico
            $puerto_host = 25500 + $servidor_id;

            $sql_update_puerto = "UPDATE servidor SET puerto = ? WHERE id = ?";
            $stmt_p = mysqli_prepare($conexion, $sql_update_puerto);
            mysqli_stmt_bind_param($stmt_p, "ii", $puerto_host, $servidor_id);
            mysqli_stmt_execute($stmt_p);
            
            // ajusta ram segun plan
            switch ($plan_id) {
                case 1: 
                case 2: 
                    $ram_entorno_vbox = "512m"; 
                    break;
                case 3: 
                case 4: 
                    $ram_entorno_vbox = "1g"; 
                    break;
                default:
                    $ram_entorno_vbox = "512m";
            }

            $ram_final = $ram_entorno_vbox; 

            // ejecuta orquestador ansible
            $cmd = "HOME=/var/www /usr/bin/ansible-playbook /var/www/html/PlaybookAnsible.yml"
                 . " -e \"servidor_id={$servidor_id} puerto_host={$puerto_host} ram_limite={$ram_final}\""
                 . " 2>&1";

            // control timeout ejecucion ansible
            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ];

            $proceso = proc_open($cmd, $descriptors, $pipes);

            if (!is_resource($proceso)) {
                $return_code = 1;
                $output = ['Error crítico: No se pudo invocar el orquestador Ansible.'];
            } else {
                fclose($pipes[0]); // cierra entrada estandar
                stream_set_blocking($pipes[1], false); // configura lectura no bloqueante

                $tiempo_inicio = time();
                $timeout = 55; // limita ejecucion maxima 55s
                $output_raw = '';

                while (true) {
                    $estado = proc_get_status($proceso);
                    if (!$estado['running']) {
                        break; // proceso finalizado sin errores
                    }
                    
                    if ((time() - $tiempo_inicio) > $timeout) {
                        proc_terminate($proceso, 9); // fuerza cierre proceso
                        $return_code = 124;
                        $output = ['Error: Timeout de aprovisionamiento superado (55s).'];
                        fclose($pipes[1]);
                        fclose($pipes[2]);
                        proc_close($proceso);
                        
                        // registra error por timeout
                        $log_timeout = date('Y-m-d H:i:s') . " | ERROR | Timeout de Ansible superado para servidor ID: {$servidor_id}\n";
                        file_put_contents('/var/log/ytron_deploy.log', $log_timeout, FILE_APPEND);
                        break;
                    }
                    
                    $output_raw .= stream_get_contents($pipes[1]);
                    usleep(100000); // pausa para evitar saturacion
                }

                if (isset($pipes[1]) && is_resource($pipes[1])) {
                    $output_raw .= stream_get_contents($pipes[1]); // lee buffer salida restante
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    if (!isset($return_code) || $return_code !== 124) {
                        $return_code = $estado['exitcode'];
                        $output = explode("\n", trim($output_raw));
                    }
                }
            }

            // guarda log ejecucion local
            $log_line = date('Y-m-d H:i:s')
                      . " | servidor_id={$servidor_id} | ram_asignada={$ram_final}"
                      . " | exit_code={$return_code}"
                      . " | output=" . implode("\n", $output) . "\n";
            file_put_contents('/var/log/ytron_deploy.log', $log_line, FILE_APPEND);

            // actualiza estado despliegue bd
            if ($return_code !== 0) {
                mysqli_query($conexion, "UPDATE servidor SET estado='error' WHERE id = $servidor_id");
            } else {
                mysqli_query($conexion, "UPDATE servidor SET estado='activo' WHERE id = $servidor_id");
            }
            
            unset($output);
        }

        mysqli_commit($conexion);

        // envia email factura cliente
        require_once __DIR__ . '/correo_helper.php';
        enviar_correo_ytron($_SESSION['email'], "Factura #" . $factura_id . " - Ytron Hosting", "factura_compra", [
            'plan_nombre' => implode(", ", $planes_comprados),
            'factura_id' => $factura_id,
            'precio' => array_sum(array_column($_SESSION['carrito'], 'precio'))
        ]);

        $_SESSION['carrito'] = [];
        header("Location: factura.php?id=" . $factura_id . "&compra=exito");
    } catch (Exception $e) {    
        mysqli_rollback($conexion);
        header("Location: error.php?mensaje=" . urlencode($e->getMessage()));
    }
?>