<?php
/**
 * Panel de administración de usuarios.
 * Permite visualizar, buscar y suspender cuentas.
 */
    include 'cabecera.php';

    // comprueba si es admin
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }

    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    // obtiene usuarios y servidores
    $sql = "SELECT u.id, u.nombre, u.email, u.activo, u.admin,
                   COUNT(DISTINCT s.id) as total_servidores,
                   COUNT(DISTINCT f.id) as total_facturas
            FROM usuario u
            LEFT JOIN servidor s ON u.id = s.usuario_id
            LEFT JOIN factura f ON u.id = f.usuario_id
            GROUP BY u.id
            ORDER BY u.id DESC";
    $resultado = mysqli_query($conexion, $sql);
    $usuarios = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $usuarios[] = $row;
    }
?>

<div class="container mt-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="bi bi-people-fill text-yt-cyan"></i> Gestión de Usuarios</h2>
            <p class="text-light small mb-0">Administra cuentas, suspende usuarios y gestiona permisos del sistema.</p>
        </div>
        <span class="badge-yt-info"><?php echo count($usuarios); ?> usuarios registrados</span>
    </div>

    <!-- barra busqueda y filtros -->
    <div class="card-yt p-3 mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute yt-search-icon"></i>
                    <input type="text" id="buscar-usuarios" class="search-yt" placeholder="Buscar por nombre, email o ID..." 
                           value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select id="filtro-estado" class="form-select search-yt ps-3">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activos</option>
                    <option value="suspendido">Suspendidos</option>
                    <option value="admin">Administradores</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filtro-orden" class="form-select search-yt ps-3">
                    <option value="id-desc">Más recientes</option>
                    <option value="id-asc">Más antiguos</option>
                    <option value="nombre-asc">Nombre A-Z</option>
                    <option value="nombre-desc">Nombre Z-A</option>
                    <option value="servidores-desc">Más servidores</option>
                </select>
            </div>
        </div>
    </div>

    <!-- tabla de usuarios -->
    <div class="card-yt">
        <div class="table-responsive">
            <table class="table table-yt mb-0" id="tabla-usuarios">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Servidores</th>
                        <th>Facturas</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr data-nombre="<?php echo htmlspecialchars(strtolower($u['nombre'])); ?>"
                        data-email="<?php echo htmlspecialchars(strtolower($u['email'])); ?>"
                        data-id="<?php echo $u['id']; ?>"
                        data-activo="<?php echo $u['activo']; ?>"
                        data-admin="<?php echo $u['admin']; ?>"
                        data-servidores="<?php echo $u['total_servidores']; ?>">
                        <td class="text-light">#<?php echo $u['id']; ?></td>
                        <td>
                            <strong class="text-white"><?php echo htmlspecialchars($u['nombre']); ?></strong>
                            <?php if ($u['admin'] == 1): ?>
                                <span class="badge-yt-warning ms-1 yt-text-xs">ADMIN</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-light small"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span class="badge-yt-info"><?php echo $u['total_servidores']; ?></span></td>
                        <td><span class="badge-yt-secondary"><?php echo $u['total_facturas']; ?></span></td>
                        <td>
                            <?php if ($u['activo'] == 1): ?>
                                <span class="badge-yt-success">Activo</span>
                            <?php else: ?>
                                <span class="badge-yt-danger">Suspendido</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="admin_servidores.php?usuario=<?php echo $u['id']; ?>" class="btn btn-yt-outline btn-sm" title="Ver servidores">
                                    <i class="bi bi-hdd-rack"></i>
                                </a>
                                <?php if ($u['admin'] != 1): ?>
                                <button onclick="toggleUsuario(<?php echo $u['id']; ?>, <?php echo $u['activo']; ?>)" 
                                        class="btn <?php echo $u['activo'] ? 'btn-yt-danger' : 'btn-yt-outline'; ?> btn-sm" 
                                        title="<?php echo $u['activo'] ? 'Suspender' : 'Activar'; ?>">
                                    <i class="bi <?php echo $u['activo'] ? 'bi-person-x' : 'bi-person-check'; ?>"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // filtrado en tiempo real
    document.getElementById('buscar-usuarios').addEventListener('input', filtrarTabla);
    document.getElementById('filtro-estado').addEventListener('change', filtrarTabla);
    document.getElementById('filtro-orden').addEventListener('change', filtrarTabla);

    function filtrarTabla() {
        const busqueda = document.getElementById('buscar-usuarios').value.toLowerCase();
        const estado = document.getElementById('filtro-estado').value;
        const orden = document.getElementById('filtro-orden').value;
        const tbody = document.querySelector('#tabla-usuarios tbody');
        const filas = Array.from(tbody.querySelectorAll('tr'));

        filas.forEach(fila => {
            const nombre = fila.dataset.nombre || '';
            const email = fila.dataset.email || '';
            const id = fila.dataset.id || '';
            const activo = fila.dataset.activo;
            const admin = fila.dataset.admin;

            let visible = true;
            // aplica busqueda texto
            if (busqueda && !nombre.includes(busqueda) && !email.includes(busqueda) && !id.includes(busqueda)) {
                visible = false;
            }
            // aplica filtro estado
            if (estado === 'activo' && activo !== '1') visible = false;
            if (estado === 'suspendido' && activo !== '0') visible = false;
            if (estado === 'admin' && admin !== '1') visible = false;

            fila.style.display = visible ? '' : 'none';
        });

        // ordena filas visibles
        const filasVisibles = filas.filter(f => f.style.display !== 'none');
        filasVisibles.sort((a, b) => {
            switch (orden) {
                case 'id-asc': return parseInt(a.dataset.id) - parseInt(b.dataset.id);
                case 'id-desc': return parseInt(b.dataset.id) - parseInt(a.dataset.id);
                case 'nombre-asc': return (a.dataset.nombre || '').localeCompare(b.dataset.nombre || '');
                case 'nombre-desc': return (b.dataset.nombre || '').localeCompare(a.dataset.nombre || '');
                case 'servidores-desc': return parseInt(b.dataset.servidores) - parseInt(a.dataset.servidores);
                default: return 0;
            }
        });
        filasVisibles.forEach(f => tbody.appendChild(f));
    }

    // autofiltra por parametro url
    if (document.getElementById('buscar-usuarios').value) {
        filtrarTabla();
    }

    // funcion suspende o activa
    function toggleUsuario(userId, activo) {
        const accion = activo ? 'suspender' : 'activar';
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} usuario #${userId}?`,
            text: activo ? 'El usuario no podrá iniciar sesión.' : 'El usuario recuperará el acceso.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: activo ? '#ff1744' : '#00e5a0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            background: '#12121c',
            color: '#e8eaf6'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('admin_toggle_usuario.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${userId}`
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({ title: '¡Hecho!', text: data.message, icon: 'success', background: '#12121c', color: '#e8eaf6', confirmButtonColor: '#00d4ff' })
                        .then(() => location.reload());
                    } else {
                        Swal.fire({ title: 'Error', text: data.message, icon: 'error', background: '#12121c', color: '#e8eaf6' });
                    }
                });
            }
        });
    }
</script>

<?php
    mysqli_close($conexion);
    include 'footer.php';
?>
