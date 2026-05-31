<?php
/**
 * Panel de diagnóstico de infraestructura HA.
 * Revisa nodos, DB, permisos y Grafana.
 */
    include 'cabecera.php';
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php");
        exit();
    }
?>
<div class="container mt-5 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white"><i class="bi bi-wrench"></i> Diagnóstico de Infraestructura HA</h2>
            <p class="text-light text-opacity-75 small mb-0">Monitorización de nodos, pruebas de sistema y estado de red.</p>
        </div>
        <a href="https://192.168.128.128:3443/login" target="_blank" class="btn btn-yt-primary text-black fw-bold">
            <i class="bi bi-graph-up-arrow me-1"></i> Ir a Métricas Grafana
        </a>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="p-4 rounded shadow-sm card-yt h-100 border-left-yt-teal">
                <h3><i class="bi bi-server text-info"></i> yt-mgmt-01 (Master principal)</h3>
                <p class="mt-3 text-light text-opacity-75 mb-2">IP LAN: <code class="text-white bg-dark px-2 py-1 rounded">10.10.30.10</code></p>
                <?php
                    $ping1 = @fsockopen("10.10.30.10", 22, $errno, $errstr, 1);
                    if ($ping1) {
                        fclose($ping1);
                        echo "<span class='badge-yt-success d-inline-block mt-2'><i class='bi bi-check-circle me-1'></i> OPERATIVO (SSH OK)</span>";
                    } else {
                        echo "<span class='badge-yt-danger d-inline-block mt-2'><i class='bi bi-x-circle me-1'></i> INACCESIBLE</span>";
                    }
                ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="p-4 rounded shadow-sm card-yt h-100 border-left-yt-teal">
                <h3><i class="bi bi-server text-info"></i> yt-mgmt-02 (Backup Activo)</h3>
                <p class="mt-3 text-light text-opacity-75 mb-2">IP LAN: <code class="text-white bg-dark px-2 py-1 rounded">10.10.30.11</code></p>
                <?php
                    $ping2 = @fsockopen("10.10.30.11", 22, $errno, $errstr, 1);
                    if ($ping2) {
                        fclose($ping2);
                        echo "<span class='badge-yt-success d-inline-block mt-2'><i class='bi bi-check-circle me-1'></i> OPERATIVO (SSH OK)</span>";
                    } else {
                        echo "<span class='badge-yt-danger d-inline-block mt-2'><i class='bi bi-x-circle me-1'></i> INACCESIBLE</span>";
                    }
                ?>
            </div>
        </div>

        <div class="col-12">
            <h4 class="mt-4 mb-3 text-white"><i class="bi bi-activity text-success me-2"></i> Estado del Sistema Web</h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card-yt p-3">
                        <h6 class="text-info"><i class="bi bi-code-slash"></i> Entorno PHP</h6>
                        <p class="mb-1 text-light small">Versión: <strong class="text-white"><?php echo phpversion(); ?></strong></p>
                        <p class="mb-1 text-light small">SAPI: <strong class="text-white"><?php echo php_sapi_name(); ?></strong></p>
                        <p class="mb-0 text-light small">Extensión MySQLi: 
                            <?php echo extension_loaded('mysqli') ? "<span class='text-success'>Cargada</span>" : "<span class='text-danger'>Falta</span>"; ?>
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-yt p-3">
                        <h6 class="text-info"><i class="bi bi-database"></i> Base de Datos (MariaDB)</h6>
                        <?php 
                            require_once __DIR__ . '/db_conexion.php';
                            $db = @getDbConnection();
                            if ($db) {
                                echo "<p class='text-success small mb-1'><i class='bi bi-check'></i> Conexión Exitosa</p>";
                                $res = mysqli_query($db, "SELECT COUNT(*) as t FROM usuario");
                                $c = mysqli_fetch_assoc($res);
                                echo "<p class='text-light small mb-0'>Usuarios registrados: <strong class='text-white'>{$c['t']}</strong></p>";
                                mysqli_close($db);
                            } else {
                                echo "<p class='text-danger small mb-0'><i class='bi bi-x'></i> Falla conexión DB</p>";
                            }
                        ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-yt p-3">
                        <h6 class="text-info"><i class="bi bi-shield-check"></i> Sesiones / Permisos</h6>
                        <?php
                            $sp = ini_get('session.save_path') ?: sys_get_temp_dir();
                            $sp_ok = is_writable($sp);
                            echo "<p class='small mb-1 text-light'>Directorio: <code class='bg-dark text-white p-1'>$sp</code></p>";
                            echo "<p class='small mb-0 text-light'>Escritura: " . ($sp_ok ? "<span class='text-success'>OK</span>" : "<span class='text-danger'>Denegada</span>") . "</p>";
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-4 mb-5">
            <div class="card-yt p-0 overflow-hidden yt-grafana-container">
                <div class="card-header-yt d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-graph-up me-2"></i> Monitorización en Tiempo Real (Grafana)</span>
                </div>
                <div class="position-absolute top-50 start-50 translate-middle text-center yt-grafana-placeholder">
                    <i class="bi bi-shield-lock fs-1 mb-2 d-block"></i>
                    <p class="small">Si Grafana no se carga, verifica el cortafuegos o accede directamente a la IP.</p>
                </div>
                <iframe src="https://192.168.128.128:3443/login" width="100%" height="100%" frameborder="0" class="yt-grafana-iframe"></iframe>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>