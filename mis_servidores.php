<?php
/**
 * Panel de cliente - Mis Servidores.
 * Permite controlar el estado de los contenedores.
 */
    include 'cabecera.php';

    // protege sesion usuario estrictamente
    if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }

    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    // busca servidores del usuario
    // excluye servidores expirados cancelados
    $sql = "SELECT s.id, s.uuid, s.ip, s.puerto, s.estado, p.nombre as plan_nombre, p.ram_mb, p.cpu_pct 
            FROM servidor s 
            JOIN plan p ON s.plan_id = p.id 
            JOIN factura f ON f.usuario_id = s.usuario_id
            JOIN linea l ON l.factura_id = f.id AND l.concepto = p.nombre
            WHERE s.usuario_id = ? AND (f.pagada = 1 OR f.fecha_vencimiento > CURDATE())
            GROUP BY s.id
            ORDER BY s.id DESC";
            
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['usuario_id']);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
?>

<div class="container mt-5 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="bi bi-terminal-fill text-yt-cyan"></i> Mis Servidores</h2>
            <p class="text-muted small mb-0">Controla el estado y accede a la consola de tus contenedores.</p>
        </div>
        <a href="perfil.php" class="btn btn-yt-outline btn-sm"><i class="bi bi-arrow-left me-1"></i> Volver a Perfil</a>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="contenedor-servidores">
        <?php 
        if (mysqli_num_rows($resultado) > 0) {
            while ($srv = mysqli_fetch_assoc($resultado)): 
                // configura estilos segun estado
                $estado = $srv['estado'];
                $badge_class = "badge-yt-danger";
                $border_color = "var(--yt-red)";
                $glow = "";

                if ($estado == 'activo') {
                    $badge_class = "badge-yt-success";
                    $border_color = "var(--yt-green)";
                    $glow = "box-shadow: 0 0 10px rgba(0,200,83,0.2);";
                } elseif ($estado == 'provisionando') {
                    $badge_class = "badge-yt-warning animate-pulse";
                    $border_color = "var(--yt-orange)";
                } elseif ($estado == 'apagado') {
                    $badge_class = "badge-yt-secondary";
                    $border_color = "var(--yt-border)";
                }
            ?>
            <div class="col" id="server-card-<?php echo $srv['id']; ?>">
                <div class="card-yt h-100" style="border-left: 4px solid <?php echo $border_color; ?>; <?php echo $glow; ?>">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1"><i class="bi bi-box me-2 text-yt-cyan"></i>srv_<?php echo $srv['id']; ?></h5>
                            </div>
                            <span class="<?php echo $badge_class; ?>" id="badge-estado-<?php echo $srv['id']; ?>">
                                <?php echo strtoupper($estado); ?>
                            </span>
                        </div>
                        
                        <div class="server-info mb-4 small">
                            <div class="row g-2">
                                <div class="col-6">
                                    <span class="text-muted d-block">Plan Contratado</span>
                                    <span class="text-white"><i class="bi bi-lightning-charge me-1 text-yt-teal"></i><?php echo htmlspecialchars($srv['plan_nombre']); ?></span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block">Puerto Público</span>
                                    <span class="text-info font-monospace"><?php echo $srv['puerto']; ?></span>
                                </div>
                            </div>
                        </div>

                        <hr class="border-yt">

                        <div class="d-flex gap-2 justify-content-between">
                            <div class="btn-group w-50">
                                <button onclick="controlarContenedor(<?php echo $srv['id']; ?>, 'start')" 
                                        class="btn btn-yt-outline btn-sm w-50 btn-control-start" 
                                        title="Encender"
                                        <?php echo ($estado == 'activo' || $estado == 'provisionando') ? 'disabled' : ''; ?>>
                                    <i class="bi bi-play-fill text-success"></i>
                                </button>
                                <button onclick="controlarContenedor(<?php echo $srv['id']; ?>, 'stop')" 
                                        class="btn btn-yt-outline btn-sm w-50 btn-control-stop" 
                                        title="Apagar"
                                        <?php echo ($estado != 'activo') ? 'disabled' : ''; ?>>
                                    <i class="bi bi-stop-fill text-danger"></i>
                                </button>
                            </div>
                            <a href="consola.php?id=<?php echo $srv['id']; ?>" class="btn btn-yt-primary btn-sm w-50 btn-consola <?php echo ($estado != 'activo') ? 'yt-disabled-link' : ''; ?>">
                                <i class="bi bi-terminal"></i> Consola
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
    </div>
        <?php } else { ?>
    </div>
            <div class="container d-flex justify-content-center mt-4">
                <div class="text-center py-5 w-100">
                    <i class="bi bi-hdd-network fs-1 text-light mb-3 d-block"></i>
                    <h4 class="text-light">Aún no tienes servidores contratados</h4>
                    <a href="planes.php" class="btn btn-yt-primary mt-3">Ver Planes de Hosting</a>
                </div>
            </div>
        <?php } ?>
</div>

<script>
    // peticion ajax control contenedor
    function controlarContenedor(serverId, accion) {
        const accionText = accion === 'start' ? 'encendiendo' : 'apagando';
        
        // bloquea ui mientras procesa
        Swal.fire({
            title: 'Procesando...',
            text: `Estamos ${accionText} tu servidor. Por favor, espera.`,
            allowOutsideClick: false,
            background: '#12121c',
            color: '#e8eaf6',
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // envia accion a api
        fetch('control_servidor.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${serverId}&accion=${accion}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Completado!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#12121c',
                    color: '#e8eaf6'
                }).then(() => {
                    // refresca dom nuevo estado
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Ejecución',
                    text: data.message,
                    background: '#12121c',
                    color: '#e8eaf6'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: 'No se pudo contactar con la granja de servidores.',
                background: '#12121c',
                color: '#e8eaf6'
            });
        });
    }
</script>

<?php 
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    include 'footer.php'; 
?>