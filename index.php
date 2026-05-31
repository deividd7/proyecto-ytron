<?php 
/**
 * Panel central de administración de planes.
 * Permite crear, listar, editar y eliminar planes.
 */

    // solo admin gestiona planes
    include 'cabecera.php';



    
    // valida sesion de admin
    // verifica usuario logueado
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    // redirige si no admin
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }



    // conecta base de datos
    // usa conexion local configurada
        
    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();


    // selecciona planes registrados bd
    $sql = "SELECT * FROM plan ORDER BY precio ASC";
    $resultado = mysqli_query($conexion, $sql);
?>


<!-- html heredado de cabecera -->



<div class="container mt-5 fade-in">
    <div class="card-yt p-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 border-yt">
            <h2 class="fw-bold mb-0"><i class="bi bi-gear text-yt-cyan"></i> Gestión de Planes de Hosting</h2>
            <a href="nuevo_plan.php" class="btn btn-yt-primary"><i class="bi bi-plus-circle me-1"></i> Crear Nuevo Plan</a>
        </div>

        <div class="table-responsive">
            <table class="table table-yt align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Plan</th>
                        <th>Precio (€)</th>
                        <th>RAM (MB)</th>
                        <th>CPU (Cores)</th>
                        <th class="text-end">Acciones</th> 
                    </tr>
                </thead>
                
                <tbody>
                    <?php if (mysqli_num_rows($resultado) > 0): ?>
                        <?php while ($p = mysqli_fetch_assoc($resultado)): ?>
                            <tr>
                                <td class="text-light">#<?php echo $p['id']; ?></td>
                                <td class="fw-bold text-white"><?php echo htmlspecialchars($p['nombre']); ?></td>
                                <td class="text-light"><?php echo htmlspecialchars($p['precio']); ?> €</td>
                                <td class="text-light"><?php echo htmlspecialchars($p['ram_mb']); ?></td>
                                <td class="text-light"><?php echo htmlspecialchars($p['cpu_pct']); ?></td>
                                
                                <td class="text-end">
                                    <a href="editar_plan.php?id=<?php echo $p['id']; ?>" class="btn btn-yt-outline btn-sm">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </a>
                                    
                                    <button class="btn btn-yt-danger btn-sm ms-1" onclick="confirmarEliminacion(<?php echo $p['id']; ?>)">
                                        <i class="bi bi-trash"></i> Borrar
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 mb-2 d-block"></i> No hay planes creados aún.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script>    // muestra confirmacion borrado visual
    function confirmarEliminacion(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Estás a punto de eliminar el plan ID: " + id,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff1744',
            cancelButtonColor: '#1a1a2e',
            confirmButtonText: 'Sí, borrar plan',
            cancelButtonText: 'Cancelar',
            background: '#12121c',
            color: '#e8eaf6',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // ejecuta borrado si confirma
                window.location.href = "borrar_plan.php?id=" + id;
            }
        })
    }
</script>


<script>   // muestra alerta plan creado
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('creado') === 'exito') {
        Swal.fire({
            title: '¡Plan Creado!',
            text: 'El nuevo plan de hosting se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: 'blue',
            confirmButtonText: 'Genial'
        }).then(() => {
            // limpia parametros url recarga
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
</script>


<script>   // muestra alerta plan editado
    if (urlParams.get('editado') === 'exito') {
        Swal.fire({
            title: '¡Actualizado!',
            text: 'Los cambios en el plan se han guardado correctamente.',
            icon: 'success',
            confirmButtonColor: 'blue',
            confirmButtonText: 'Entendido'
        }).then(() => {
            // limpia parametros url recarga
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
</script>

<?php 
    mysqli_close($conexion);
    include 'footer.php';
?>

