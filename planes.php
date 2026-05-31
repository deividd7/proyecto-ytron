<?php 
/**
 * Catálogo de planes.
 * Muestra los planes disponibles para contratar.
 */
    include 'cabecera.php'; 

    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    // prepara query anti inyeccion
    $sql = "SELECT id, nombre, precio, ram_mb, cpu_pct FROM plan";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $planes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
?>

<div class="container py-5 fade-in">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-white mb-3">Nuestros Planes de Hosting</h1>
        <p class="lead text-light mx-auto yt-text-max-600">Potencia tus proyectos con el mejor rendimiento. Nodos ultra rápidos, protección DDoS y Zero-Touch provisioning.</p>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
        <?php foreach ($planes as $plan): ?>
            <div class="col">
                <div class="plan-tarjeta h-100 shadow-sm d-flex flex-column">
                    <div class="plan-tarjeta-header text-center">
                        <h3 class="my-0 fw-bold"><?php echo htmlspecialchars($plan['nombre']); ?></h3>
                    </div>
                    
                    <div class="plan-tarjeta-body text-center d-flex flex-column flex-grow-1">
                        <h2 class="pricing-card-title display-5 mb-4">
                            <?php echo $plan['precio']; ?>€<small class="text-light fw-light fs-5">/mes</small>
                        </h2>
                        
                        <ul class="list-unstyled mt-2 mb-4 text-start mx-auto flex-grow-1">
                            <li class="mb-3">
                                <i class="bi bi-cpu me-2 text-yt-cyan"></i>
                                <span class="text-white"><strong>CPU:</strong> <?php echo $plan['cpu_pct']; ?> Cores</span>
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-memory me-2 text-yt-teal"></i>
                                <span class="text-white"><strong>RAM:</strong> 
                                <?php 
                                    echo ($plan['ram_mb'] >= 1024) ? ($plan['ram_mb'] / 1024) . " GB" : $plan['ram_mb'] . " MB"; 
                                ?></span>
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-hdd-network me-2 text-primary"></i>
                                <span class="text-light">SSD NVMe RAID 10</span>
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-headset me-2 text-warning"></i>
                                <span class="text-light">Soporte Premium 24/7</span>
                            </li>
                        </ul>
                        
                        <form action="perfil.php" method="POST" class="mt-auto">
                            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                            <input type="hidden" name="nombre_plan" value="<?php echo htmlspecialchars($plan['nombre']); ?>">
                            <input type="hidden" name="precio_plan" value="<?php echo $plan['precio']; ?>">
                            
                            <button type="submit" name="agregar_plan" class="w-100 btn btn-lg btn-yt-primary">
                                Seleccionar Plan
                            </button>
                        </form>                
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- muestra confirmacion plan añadido --> 
<?php if (isset($_GET['añadido']) && $_GET['añadido'] == 'exito'): ?>
<script>
    Swal.fire({
        title: "¡Plan Seleccionado!",
        text: "El plan se ha añadido a tu carrito correctamente.",
        icon: "success",
        showConfirmButton: true,
        confirmButtonColor: "#00d4ff",
        confirmButtonText: "Ver mi carrito",
        showCancelButton: true,
        cancelButtonColor: "#16213e",
        cancelButtonText: "Seguir mirando",
        reverseButtons: true,
        background: '#12121c',
        color: '#e8eaf6'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "perfil.php";
        }
        window.history.replaceState({}, document.title, window.location.pathname);
    });
</script>
<?php endif; ?>

<?php 
    mysqli_close($conexion);
    include 'footer.php'; 
?>