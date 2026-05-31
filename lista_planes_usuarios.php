<?php 
/**
 * Panel de administración de suscripciones.
 * Monitoriza los planes contratados por usuarios.
 */
    include 'cabecera.php';

    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }

    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    // cruza datos facturas servidores
    $sql = "SELECT u.id as usuario_id, u.nombre as usuario, u.email, 
                   l.id as linea_id, l.concepto as plan, 
                   f.id as factura_id, f.fecha_vencimiento, f.pagada,
                   s.id as servidor_id, s.uuid as docker_name, s.estado as estado_docker
            FROM usuario u 
            JOIN factura f ON u.id = f.usuario_id 
            JOIN linea l ON f.id = l.factura_id 
            LEFT JOIN plan p ON l.concepto = p.nombre
            LEFT JOIN servidor s ON s.usuario_id = u.id AND s.plan_id = p.id
            ORDER BY f.id DESC";

    $resultado = mysqli_query($conexion, $sql);
    $planes = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $planes[] = $row;
    }
?>

<div class="container mt-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="bi bi-receipt text-yt-cyan"></i> Planes Contratados</h2>
            <p class="text-light text-opacity-75 small mb-0">Gestión de suscripciones y facturación de usuarios.</p>
        </div>
        <span class="badge-yt-info"><?php echo count($planes); ?> planes listados</span>
    </div>
    
    <div class="card-yt p-3 mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute yt-search-icon"></i>
                    <input type="text" id="buscar-planes" class="search-yt text-light" placeholder="Buscar por usuario, email, plan o factura...">
                </div>
            </div>
            <div class="col-md-6 text-end">
                <select id="filtro-orden" class="form-select search-yt d-inline-block w-auto text-light ps-3">
                    <option value="fecha-desc">Vencimiento más lejano</option>
                    <option value="fecha-asc">Vencimiento más cercano</option>
                    <option value="usuario-asc">Usuario A-Z</option>
                    <option value="factura-desc">Factura más reciente</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card-yt">
        <div class="table-responsive">
            <table class="table table-yt mb-0" id="tabla-planes">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Plan Contratado</th>
                        <th>Estado Plan</th>
                        <th>Factura Ref.</th>
                        <th>Docker Name</th>
                        <th>Estado Docker</th>
                        <th>Vencimiento</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($planes as $fila): 
                        $docker_name = $fila['servidor_id'] ? 'srv_' . $fila['servidor_id'] : '';
                    ?>
                    <tr data-usuario="<?php echo htmlspecialchars(strtolower($fila['usuario'])); ?>"
                        data-email="<?php echo htmlspecialchars(strtolower($fila['email'])); ?>"
                        data-plan="<?php echo htmlspecialchars(strtolower($fila['plan'])); ?>"
                        data-factura="<?php echo $fila['factura_id']; ?>"
                        data-docker="<?php echo strtolower($docker_name); ?>"
                        data-fecha="<?php echo strtotime($fila['fecha_vencimiento']); ?>">
                        <td>
                            <a href="admin_usuarios.php?buscar=<?php echo urlencode($fila['usuario']); ?>" class="text-decoration-none text-light fw-bold">
                                <i class="bi bi-person me-1 text-yt-cyan"></i><?php echo htmlspecialchars($fila['usuario']); ?>
                            </a>
                        </td>
                        <td class="text-light text-opacity-75 small"><?php echo htmlspecialchars($fila['email']); ?></td>
                        <td><span class="badge-yt-info"><?php echo htmlspecialchars($fila['plan']); ?></span></td>
                        <td>
                            <?php 
                                $es_vigente = strtotime($fila['fecha_vencimiento']) > time();
                                if ($fila['pagada'] == 1 && $es_vigente) {
                                    echo "<span class='badge-yt-success'>ACTIVO</span>";
                                } elseif ($fila['pagada'] == 0 && $es_vigente) {
                                    echo "<span class='badge-yt-warning'>PENDIENTE</span>";
                                } else {
                                    echo "<span class='badge-yt-danger'>CANCELADO</span>";
                                }
                            ?>
                        </td>
                        <td class="text-light text-opacity-75">
                            <a href="factura.php?id=<?php echo $fila['factura_id']; ?>" class="text-decoration-none text-info fw-bold">
                                <i class="bi bi-file-earmark-text me-1"></i>#FA-<?php echo $fila['factura_id']; ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($fila['servidor_id']): ?>
                                <a href="admin_servidores.php?search=<?php echo urlencode($docker_name); ?>" class="text-decoration-none font-monospace text-light text-opacity-75 yt-text-md" title="Ver en monitor de servidores">
                                    <i class="bi bi-box me-1 text-yt-cyan"></i><?php echo htmlspecialchars($docker_name); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-light text-opacity-50 small">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($fila['servidor_id']): 
                                $st = strtolower($fila['estado_docker']);
                                $bdg = $st == 'activo' ? 'success' : ($st == 'error' ? 'danger' : 'warning');
                            ?>
                                <span class="badge bg-<?php echo $bdg; ?> bg-opacity-25 text-<?php echo $bdg; ?> border border-<?php echo $bdg; ?> rounded-pill yt-text-xs">
                                    <?php echo strtoupper($st); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-light text-opacity-50 small">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                                $fecha_venc = strtotime($fila['fecha_vencimiento']);
                                $hoy = time();
                                $dias_restantes = round(($fecha_venc - $hoy) / (60 * 60 * 24));
                                
                                echo "<span class='text-light text-opacity-75'>" . date('d/m/Y', $fecha_venc) . "</span>";
                                if ($dias_restantes < 0) {
                                    echo " <span class='badge bg-danger ms-1 small'>Expirado</span>";
                                } elseif ($dias_restantes <= 5) {
                                    echo " <span class='badge bg-warning text-dark ms-1 small'>Pronto</span>";
                                }
                            ?>
                        </td>
                        <td class="text-center">
                            <button onclick="confirmarEliminarLinea(<?php echo $fila['linea_id']; ?>)" class="btn btn-yt-danger btn-sm" title="Quitar Acceso y Eliminar Servidor">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.getElementById('buscar-planes').addEventListener('input', filtrarTabla);
    document.getElementById('filtro-orden').addEventListener('change', filtrarTabla);

    function filtrarTabla() {
        const busqueda = document.getElementById('buscar-planes').value.toLowerCase();
        const orden = document.getElementById('filtro-orden').value;
        const tbody = document.querySelector('#tabla-planes tbody');
        const filas = Array.from(tbody.querySelectorAll('tr'));

        filas.forEach(fila => {
            const usuario = fila.dataset.usuario || '';
            const email = fila.dataset.email || '';
            const plan = fila.dataset.plan || '';
            const factura = fila.dataset.factura || '';
            const docker = fila.dataset.docker || '';

            let visible = true;
            if (busqueda && !usuario.includes(busqueda) && !email.includes(busqueda) && !plan.includes(busqueda) && !factura.includes(busqueda) && !docker.includes(busqueda)) {
                visible = false;
            }
            fila.style.display = visible ? '' : 'none';
        });

        const filasVisibles = filas.filter(f => f.style.display !== 'none');
        filasVisibles.sort((a, b) => {
            switch (orden) {
                case 'fecha-asc': return parseInt(a.dataset.fecha) - parseInt(b.dataset.fecha);
                case 'fecha-desc': return parseInt(b.dataset.fecha) - parseInt(a.dataset.fecha);
                case 'usuario-asc': return (a.dataset.usuario || '').localeCompare(b.dataset.usuario || '');
                case 'factura-desc': return parseInt(b.dataset.factura) - parseInt(a.dataset.factura);
                default: return 0;
            }
        });
        filasVisibles.forEach(f => tbody.appendChild(f));
    }

    function confirmarEliminarLinea(id) {
        Swal.fire({
            title: '¿Eliminar Acceso al Servidor?',
            text: "Esta acción destruirá físicamente el contenedor Docker y lo desvinculará del usuario. La factura original se mantendrá para el historial.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff1744',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, destruir',
            cancelButtonText: 'Cancelar',
            background: '#12121c',
            color: '#e8eaf6'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "borrar_linea_usuario.php?id=" + id;
            }
        })
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('eliminado') === 'exito') {
            Swal.fire({
                title: '¡Plan y Servidor Eliminados!',
                text: 'El servicio ha sido retirado y el contenedor destruido.',
                icon: 'success',
                confirmButtonColor: '#00c853',
                background: '#12121c',
                color: '#e8eaf6'
            }).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }
    });
</script>

<?php 
    mysqli_close($conexion);
    include 'footer.php'; 
?>