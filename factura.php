<?php 
/**
 * Visualizador de facturas.
 * Muestra detalles y permite cancelación.
 */
    include 'cabecera.php'; 
    if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }

    $factura_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1) {
        $sql = "SELECT f.*, l.id as linea_id, l.concepto, l.precio_ud FROM factura f 
                JOIN linea l ON f.id = l.factura_id WHERE f.id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $factura_id);
    } else {
        $sql = "SELECT f.*, l.id as linea_id, l.concepto, l.precio_ud FROM factura f 
                JOIN linea l ON f.id = l.factura_id WHERE f.id = ? AND f.usuario_id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $factura_id, $_SESSION['usuario_id']);
    }
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($resultado) == 0) { header("Location: perfil.php"); exit(); }
    $factura = mysqli_fetch_assoc($resultado);

    $es_vigente = strtotime($factura['fecha_vencimiento']) > time();
    $estado_badge = "";
    $estado_texto = "";
    if ($factura['pagada'] == 1 && $es_vigente) {
        $estado_badge = "badge-yt-success";
        $estado_texto = "ACTIVA";
    } elseif ($factura['pagada'] == 0 && $es_vigente) {
        $estado_badge = "badge-yt-warning";
        $estado_texto = "PENDIENTE";
    } else {
        $estado_badge = "badge-yt-danger";
        $estado_texto = "CANCELADA";
    }
?>

<div class="container py-5 fade-in">
    <div class="card-yt p-5">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-4 border-yt">
            <div>
                <img src="imagenes/Logo.png" alt="Ytron Logo" class="yt-logo-sm mb-3" onerror="this.style.display='none'">
                <h2 class="text-white fw-bold mb-1">Recibo de Compra</h2>
                <p class="text-light font-monospace mb-0">Factura #<?php echo $factura['id']; ?></p>
            </div>
            <div class="text-end">
                <span class="<?php echo $estado_badge; ?> mb-2 d-inline-block">
                    <?php echo $estado_texto; ?>
                </span>
                <p class="mb-0 text-light small">Vencimiento / Renovación</p>
                <h5 class="text-white mb-0"><strong><?php echo date('d/m/Y', strtotime($factura['fecha_vencimiento'])); ?></strong></h5>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <table class="table table-yt">
                <thead>
                    <tr>
                        <th>Concepto / Plan</th>
                        <th class="text-end">Precio Ud.</th>
                        <th class="text-end">Total</th>
                        <th class="text-center d-print-none">Gestión</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <i class="bi bi-box me-2 text-yt-cyan"></i>
                            <strong class="text-white"><?php echo htmlspecialchars($factura['concepto']); ?></strong>
                        </td>
                        <td class="text-end text-light"><?php echo number_format($factura['precio_ud'], 2); ?> €</td>
                        <td class="text-end fw-bold text-white"><?php echo number_format($factura['precio_ud'], 2); ?> €</td>
                        <td class="text-center d-print-none">
                            <?php if ($factura['pagada']): ?>
                                <button onclick="confirmarCancelacion(<?php echo $factura['linea_id']; ?>)" class="btn btn-yt-danger btn-sm" title="Dar de baja suscripción">
                                    <i class="bi bi-x-circle me-1"></i> Cancelar Plan
                                </button>
                            <?php else: ?>
                                <span class="text-light small"><i class="bi bi-info-circle"></i> Vencerá pronto</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end text-light"><strong>Total Abonado:</strong></td>
                        <td class="text-end h4 fw-bold mb-0 text-yt-teal"><?php echo number_format($factura['precio_ud'], 2); ?> €</td>
                        <td class="d-print-none"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-5 d-print-none text-center">
            <button onclick="window.print();" class="btn btn-yt-outline me-2 mb-2">
                <i class="bi bi-printer"></i> Imprimir Recibo
            </button>
            <a href="perfil.php" class="btn btn-yt-primary me-2 mb-2">
                <i class="bi bi-arrow-left me-1"></i> Volver a Perfil
            </a>
            <?php if ($factura['pagada'] == 1): ?>
                <button onclick="confirmarCancelacion(<?php echo $factura['linea_id']; ?>)" class="btn btn-yt-danger mb-2">
                    <i class="bi bi-x-circle"></i> Cancelar Suscripción
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function confirmarCancelacion(lineaId) {
        Swal.fire({
            title: '¿Cancelar Suscripción?',
            text: "El cobro automático se desactivará. Tu servidor seguirá funcionando hasta el día de su vencimiento.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff1744',
            cancelButtonColor: '#1a1a2e',
            confirmButtonText: 'Sí, cancelar plan',
            cancelButtonText: 'Volver',
            background: '#12121c',
            color: '#e8eaf6'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `cancelar_suscripcion.php?id=${lineaId}`;
            }
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('cancelado') === 'exito') {
        Swal.fire({
            title: 'Suscripción Cancelada',
            text: 'El servicio ha sido marcado para no renovar.',
            icon: 'success',
            confirmButtonColor: '#00d4ff',
            background: '#12121c',
            color: '#e8eaf6'
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
</script>

<?php include 'footer.php'; ?>