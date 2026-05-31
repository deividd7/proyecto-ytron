<?php
/**
 * Panel de administración de servidores.
 * Monitorea estado de contenedores y consumo.
 */
    include 'cabecera.php';

    // valida si es admin
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }

    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    $usuario_filtro = filter_var($_GET['usuario'] ?? null, FILTER_VALIDATE_INT);
    $search_query = $_GET['search'] ?? '';

    // obtiene servidores plan activo
    if ($usuario_filtro) {
        $sql = "SELECT s.id, s.uuid, s.ip, s.puerto, s.estado, s.usuario_id, 
                       u.nombre as usuario, u.email as usuario_email, p.nombre as plan_nombre, p.ram_mb
                FROM servidor s 
                JOIN usuario u ON s.usuario_id = u.id 
                JOIN plan p ON s.plan_id = p.id 
                JOIN factura f ON f.usuario_id = s.usuario_id
                JOIN linea l ON l.factura_id = f.id AND l.concepto = p.nombre
                WHERE s.usuario_id = ? AND (f.pagada = 1 OR f.fecha_vencimiento > CURDATE())
                GROUP BY s.id
                ORDER BY s.id DESC";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuario_filtro);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
    } else {
        $sql = "SELECT s.id, s.uuid, s.estado, s.puerto, 
                       u.nombre as usuario, u.email as usuario_email, u.id as usuario_id,
                       p.nombre as plan_nombre, p.ram_mb
                FROM servidor s
                JOIN usuario u ON s.usuario_id = u.id
                JOIN plan p ON s.plan_id = p.id
                JOIN factura f ON f.usuario_id = s.usuario_id
                JOIN linea l ON l.factura_id = f.id AND l.concepto = p.nombre
                WHERE (f.pagada = 1 OR f.fecha_vencimiento > CURDATE())
                GROUP BY s.id
                ORDER BY s.id DESC";
        $resultado = mysqli_query($conexion, $sql);
    }

    $servidores = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $servidores[] = $row;
    }
?>

<div class="container mt-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><i class="bi bi-cpu-fill text-yt-cyan"></i> Panel Global de Servidores</h2>
            <p class="text-light text-opacity-75 small mb-0">Monitorización y administración del estado de los contenedores Docker activos.</p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($usuario_filtro): ?>
                <a href="admin_servidores.php" class="btn btn-sm btn-secondary"><i class="bi bi-x-circle me-1"></i> Quitar Filtro</a>
            <?php endif; ?>
            <span class="badge-yt-info"><span id="server-count"><?php echo count($servidores); ?></span> Instancias visibles</span>
        </div>
    </div>

    <!-- filtros y busqueda -->
    <div class="card-yt p-3 mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" id="searchInput" class="search-yt w-100" 
                       placeholder="Buscar por nombre Docker, UUID o Propietario..." 
                       value="<?php echo htmlspecialchars($search_query); ?>"
                       onkeyup="filterServers()">
            </div>
            <div class="col-md-4">
                <select id="statusFilter" class="form-select bg-dark text-white border-secondary" onchange="filterServers()">
                    <option value="todos">Todos los estados</option>
                    <option value="activo">ONLINE (Encendidos)</option>
                    <option value="apagado">APAGADOS</option>
                    <option value="error">CON ERRORES</option>
                </select>
            </div>
        </div>
    </div>

    <div id="servers-container">

    <?php if (empty($servidores)): ?>
        <div class="text-center py-5 shadow-sm server-card">
            <i class="bi bi-hdd-network text-light text-opacity-50 display-4 mb-3 d-block"></i>
            <p class="text-light text-opacity-75 mb-0">No se detectan servidores desplegados en este momento.</p>
        </div>
    <?php else: ?>
        <?php foreach ($servidores as $servidor): ?>
            <div class="server-card mb-4 mx-auto server-card-item w-100" data-status="<?php echo strtolower($servidor['estado']); ?>">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center justify-content-between g-4"> 
                        
                        <div class="col-12 col-md-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="status-indicator yt-status-indicator <?php echo $servidor['estado'] === 'activo' ? 'bg-success' : ($servidor['estado'] === 'error' ? 'bg-danger' : 'bg-secondary animate-pulse'); ?>"></div>
                                <div>
                                    <span class="d-block text-white fw-bold">srv_<?php echo $servidor['id']; ?></span>
                                    <span class="text-light text-opacity-50 d-block font-monospace yt-uuid-text" title="<?php echo $servidor['uuid']; ?>">
                                        <?php echo $servidor['uuid']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-2 text-md-center">
                            <span class="text-light text-opacity-75 small d-block mb-1">PROPIETARIO</span>
                            <a href="admin_usuarios.php?buscar=<?php echo urlencode($servidor['usuario']); ?>" class="text-decoration-none fw-semibold small text-yt-cyan">
                                <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($servidor['usuario']); ?>
                            </a>
                        </div>

                        <div class="col-6 col-md-2 text-md-center">
                            <span class="text-light text-opacity-75 small d-block mb-1">IP / PUERTO</span>
                            <span class="badge bg-transparent border text-white font-monospace px-2 py-1 border-yt">
                                10.10.40.10:<?php echo $servidor['puerto']; ?>
                            </span>
                        </div>

                        <div class="col-6 col-md-2 text-md-center">
                            <span class="text-light text-opacity-75 small d-block mb-1">PLAN / RECURSOS</span>
                            <span class="text-white small fw-bold d-block"><?php echo htmlspecialchars($servidor['plan_nombre']); ?></span>
                            <span class="text-light text-opacity-75 d-block mt-1 yt-text-sm"><?php echo ($servidor['ram_mb']/1024); ?> GB RAM</span>
                        </div>

                        <div class="col-6 col-md-3 d-flex flex-column flex-md-row justify-content-md-end align-items-center gap-2">
                            <?php if ($servidor['estado'] === 'activo'): ?>
                                <button class="btn btn-sm btn-outline-danger w-100 w-md-auto" onclick="ejecutarAccion(<?php echo $servidor['id']; ?>, 'stop')">
                                    <i class="bi bi-stop-fill me-1"></i> Apagar
                                </button>
                                <a href="consola.php?id=<?php echo $servidor['id']; ?>" class="btn btn-sm btn-yt-primary w-100 w-md-auto">
                                    <i class="bi bi-terminal me-1"></i> Consola
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-success w-100 w-md-auto" onclick="ejecutarAccion(<?php echo $servidor['id']; ?>, 'start')">
                                    <i class="bi bi-play-fill me-1"></i> Encender
                                </button>
                                <button class="btn btn-sm btn-secondary w-100 w-md-auto" disabled>
                                    <i class="bi bi-terminal me-1"></i> Consola
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
</div>

<script>
    // auto filtra por url
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('searchInput').value) {
            filterServers();
        }
    });

    function filterServers() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
        const serverCards = document.querySelectorAll('.server-card-item');
        let count = 0;

        serverCards.forEach(card => {
            const textContent = card.innerText.toLowerCase();
            const status = card.getAttribute('data-status');
            
            const matchesSearch = textContent.includes(searchInput);
            const matchesStatus = statusFilter === 'todos' || status === statusFilter;

            if (matchesSearch && matchesStatus) {
                card.style.display = 'block';
                count++;
            } else {
                card.style.display = 'none';
            }
        });
        document.getElementById('server-count').innerText = count;
    }

    function ejecutarAccion(id, accion) {
        const titulo = accion === 'start' ? '¿Encender Servidor?' : '¿Apagar Servidor?';
        const texto = accion === 'start' ? 'Se iniciará el contenedor Docker asociado.' : 'Se detendrá el proceso del servidor de Minecraft.';
        
        Swal.fire({
            title: titulo,
            text: texto,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: accion === 'start' ? '#00e5a0' : '#ff1744',
            cancelButtonColor: '#1a1a2e',
            confirmButtonText: accion === 'start' ? 'Sí, iniciar' : 'Sí, detener',
            cancelButtonText: 'Cancelar',
            background: '#12121c',
            color: '#e8eaf6'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Enviando comando...',
                    text: 'Contactando con la granja de servidores',
                    allowOutsideClick: false,
                    background: '#12121c',
                    color: '#e8eaf6',
                    didOpen: () => Swal.showLoading()
                });

                fetch('control_servidor.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&accion=${accion}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({ title: '¡Éxito!', text: data.message, icon: 'success', background: '#12121c', color: '#e8eaf6' })
                        .then(() => location.reload());
                    } else {
                        Swal.fire({ title: 'Error', text: data.message, icon: 'error', background: '#12121c', color: '#e8eaf6' });
                    }
                })
                .catch(() => {
                    Swal.fire({ title: 'Error de Red', text: 'No se pudo contactar con la granja.', icon: 'error', background: '#12121c', color: '#e8eaf6' });
                });
            }
        });
    }
</script>

<?php 
    mysqli_close($conexion);
    include 'footer.php'; 
?>