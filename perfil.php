<?php 
/**
 * Panel de perfil del cliente.
 * Muestra opciones de usuario y carrito.
 */
    include 'cabecera.php'; 

    // bloquea sin sesion activa
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    // anade plan al carrito
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_plan'])) {
        $plan_id = $_POST['plan_id'];
        $sql_verificar = "SELECT nombre, precio FROM plan WHERE id = ?";
        $stmt_v = mysqli_prepare($conexion, $sql_verificar);
        mysqli_stmt_bind_param($stmt_v, "i", $plan_id);
        mysqli_stmt_execute($stmt_v);
        $res_v = mysqli_stmt_get_result($stmt_v);
        if ($plan_db = mysqli_fetch_assoc($res_v)) {
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }
            $_SESSION['carrito'][$plan_id] = [
                'id' => $plan_id,
                'nombre' => $plan_db['nombre'],
                'precio' => $plan_db['precio'],
                'cantidad' => 1
            ];
            header("Location: planes.php?añadido=exito");
            exit();
        }
        mysqli_stmt_close($stmt_v);
    }
?>

<div class="container mt-5 fade-in">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card-yt p-4 text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow yt-profile-avatar">
                    <span class="h2 mb-0 text-black fw-bold"><?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?></span>
                </div>
                <h4 class="fw-bold mb-1 text-yt-cyan"><?php echo htmlspecialchars($_SESSION['usuario']); ?></h4>
                <p class="text-light small mb-4"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                <hr class="border-yt">
                <div class="d-grid gap-3 mt-4">
                    <a href="mis_servidores.php" class="btn btn-yt-primary">
                        <i class="bi bi-terminal-fill me-2"></i> Mis Servidores Activos
                    </a>
                    <button type="button" onclick="mostrarModalPassword()" class="btn btn-yt-outline">
                        <i class="bi bi-key-fill me-2"></i> Cambiar Contraseña
                    </button>
                    <a href="logout.php" class="btn btn-yt-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card-yt p-4 mb-4">
                <h3 class="fw-bold mb-4"><i class="bi bi-cart3 text-yt-teal"></i> Carrito</h3>
                <?php if (!empty($_SESSION['carrito'])): ?>
                    <div class="table-responsive">
                        <table class="table table-yt align-middle">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th class="text-center">Precio</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = 0;
                            foreach ($_SESSION['carrito'] as $id => $item): 
                                $total += $item['precio'];
                            ?>
                            <tr>
                                <td class="fw-bold text-light"><?php echo htmlspecialchars($item['nombre']); ?></td>
                                <td class="text-center text-light"><?php echo number_format($item['precio'], 2); ?> €</td>
                                <td class="text-end">
                                    <a href="quitar_carrito.php?id=<?php echo $id; ?>" class="btn btn-sm btn-outline-danger yt-rounded-8">Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <h4 class="mb-0">Total: <span class="fw-bold text-yt-teal"><?php echo number_format($total, 2); ?> €</span></h4>
                        <form id="formCompra" action="procesar_compra.php" method="POST">
                            <button type="button" onclick="confirmarCompra(<?php echo $total; ?>)" class="btn btn-yt-primary">
                                <i class="bi bi-rocket-takeoff me-2"></i> Confirmar y Desplegar
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-cart-x fs-1 text-light mb-3 d-block"></i>
                        <p class="text-light">No tienes ningún plan seleccionado en este momento.</p>
                        <a href="planes.php" class="btn btn-yt-outline mt-2">Explorar Planes</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-yt p-4">
                <h3 class="fw-bold mb-4"><i class="bi bi-file-earmark-text text-yt-cyan"></i> Facturas</h3>
                <?php 
                    $sql_f = "SELECT id, fecha_vencimiento, pagada FROM factura WHERE usuario_id = ? ORDER BY id DESC";
                    $stmt_f = mysqli_prepare($conexion, $sql_f);
                    mysqli_stmt_bind_param($stmt_f, "i", $_SESSION['usuario_id']);
                    mysqli_stmt_execute($stmt_f);
                    $res_f = mysqli_stmt_get_result($stmt_f);

                    if (mysqli_num_rows($res_f) > 0):
                ?>
                    <div class="table-responsive">
                        <table class="table table-yt">
                            <thead>
                                <tr>
                                    <th>Nº Factura</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                    <th class="text-end">Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($fac = mysqli_fetch_assoc($res_f)): 
                                    $fac_vigente = strtotime($fac['fecha_vencimiento']) > time();
                                    if ($fac['pagada'] == 1 && $fac_vigente) {
                                        $fac_badge = 'badge-yt-success';
                                        $fac_estado = 'Activo';
                                    } elseif ($fac['pagada'] == 0 && $fac_vigente) {
                                        $fac_badge = 'badge-yt-warning';
                                        $fac_estado = 'Pendiente';
                                    } else {
                                        $fac_badge = 'badge-yt-danger';
                                        $fac_estado = 'Cancelado';
                                    }
                                ?>
                                <tr>
                                    <td class="text-light">#<?php echo $fac['id']; ?></td>
                                    <td class="text-light"><?php echo date('d/m/Y', strtotime($fac['fecha_vencimiento'])); ?></td>
                                    <td>
                                        <span class="<?php echo $fac_badge; ?>">
                                            <?php echo $fac_estado; ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="factura.php?id=<?php echo $fac['id']; ?>" class="btn btn-yt-outline btn-sm">Ver Factura</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-receipt fs-1 text-light mb-3 d-block"></i>
                        <p class="text-light small">No se han registrado transacciones previas en tu cuenta.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarCompra(total) {
        Swal.fire({
            title: '¿Confirmar Pedido?',
            text: "Se procederá al cobro y al aprovisionamiento automático de tu infraestructura de Minecraft por un total de " + total.toFixed(2) + " €.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00e5a0',
            cancelButtonColor: '#ff1744',
            confirmButtonText: 'Sí, desplegar ahora',
            cancelButtonText: 'Cancelar',
            background: '#12121c',
            color: '#e8eaf6'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    html: 'Aprovisionando servidores (Zero-Touch).<br>Por favor, no cierres esta ventana.',
                    allowOutsideClick: false,
                    background: '#12121c',
                    color: '#e8eaf6',
                    didOpen: () => {
                        Swal.showLoading();
                        document.getElementById('formCompra').submit();
                    }
                });
            }
        });
    }

    function mostrarModalPassword() {
        Swal.fire({
            title: 'Cambiar Contraseña',
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label text-light text-opacity-75 small fw-bold">CONTRASEÑA ACTUAL</label>
                    <input type="password" id="swal-pass-actual" class="search-yt w-100" placeholder="Contraseña Actual">
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label text-light text-opacity-75 small fw-bold">NUEVA CONTRASEÑA</label>
                    <input type="password" id="swal-pass-nueva" class="search-yt w-100" placeholder="Nueva Contraseña (mín. 8 chars)">
                </div>
                <div class="mb-2 text-start">
                    <label class="form-label text-light text-opacity-75 small fw-bold">CONFIRMAR CONTRASEÑA</label>
                    <input type="password" id="swal-pass-confirmar" class="search-yt w-100" placeholder="Confirmar Nueva Contraseña">
                </div>
            `,
            background: '#12121c',
            color: '#e8eaf6',
            confirmButtonColor: '#00e5a0',
            confirmButtonText: 'Actualizar',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const actual = document.getElementById('swal-pass-actual').value;
                const nueva = document.getElementById('swal-pass-nueva').value;
                const confirmar = document.getElementById('swal-pass-confirmar').value;

                if (!actual || !nueva || !confirmar) {
                    Swal.showValidationMessage('Todos los campos son obligatorios');
                    return false;
                }
                if (nueva.length < 8) {
                    Swal.showValidationMessage('La nueva contraseña debe tener al menos 8 caracteres');
                    return false;
                }
                if (nueva !== confirmar) {
                    Swal.showValidationMessage('Las contraseñas no coinciden');
                    return false;
                }

                return fetch('cambiar_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        password_actual: actual,
                        nueva_password: nueva,
                        confirmar_password: confirmar
                    })
                })
                .then(response => {
                    if (!response.ok) { throw new Error(response.statusText); }
                    return response.json();
                })
                .catch(error => {
                    Swal.showValidationMessage(`Error en la petición: ${error}`);
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizada!',
                        text: result.value.message,
                        background: '#12121c',
                        color: '#e8eaf6',
                        confirmButtonColor: '#00e5a0'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.value.message,
                        background: '#12121c',
                        color: '#e8eaf6',
                        confirmButtonColor: '#ff1744'
                    });
                }
            }
        });
    }
</script>

<?php 
    mysqli_close($conexion);
    include 'footer.php'; 
?>