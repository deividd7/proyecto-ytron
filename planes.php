<?php 
    include 'cabecera.php'; 

    //Conexión a ytronhosting
    $conexion = mysqli_connect("localhost", "root", "", "ytronhosting");

    // 2. Preparar la sentencia (Sentencia Preparada)
    // Aunque aquí no hay variables externas, esta es la estructura profesional:
    $sql = "SELECT nombre, precio, ram_mb, cpu_pct FROM plan";
    $stmt = mysqli_prepare($conexion, $sql);

    // Ejecutar la sentencia
    mysqli_stmt_execute($stmt);

    // Obtener el resultado
    $resultado = mysqli_stmt_get_result($stmt);
    
    // Guardar los datos en un array
    $planes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

    // Cerrar la sentencia
    mysqli_stmt_close($stmt);


?>

<!-- Eliminamos las etiquetas de apertura y cierre de body y html porque ya se encuentran en la cabecera y el footer-->

<!-- Esta página es de libre acceso para usuarios no logueados -->


<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">Nuestros Planes de Hosting</h1>
        <p class="lead">Potencia tus proyectos con el mejor rendimiento.</p>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
        <?php foreach ($planes as $plan): ?>
            <div class="col">
                <div class="plan-tarjeta h-100 shadow-sm">
                    <div class="plan-tarjeta-header bg-primary text-white text-center py-3">
                        <h3 class="my-0 fw-bold"><?php echo htmlspecialchars($plan['nombre']); ?></h3>
                    </div>
                    
                    <div class="plan-tarjeta-body text-center d-flex flex-column">
                        <h2 class="plan-tarjeta-title pricing-card-title display-5">
                            <?php echo $plan['precio']; ?>€<small class="text-muted fw-light">/mes</small>
                        </h2>
                        
                        <ul class="list-unstyled mt-4 mb-4 text-start mx-auto">
                            <li class="mb-2">
                                <p>
                                    <strong>CPU:</strong> <?php echo $plan['cpu_pct']; ?> Cores
                                <p>
                            </li>
                            <li class="mb-2">
                                <p>
                                    <strong>RAM:</strong> 
                                    <?php 
                                        //Convertimos MB a GB para que sea más legible
                                        echo ($plan['ram_mb'] >= 1024) ? ($plan['ram_mb'] / 1024) . " GB" : $plan['ram_mb'] . " MB"; 
                                    ?>
                                </p>
                            </li>
                            <li class="mb-2">
                                <p> Soporte 24/7</p>
                            </li>
                        </ul>
                        
                        <button type="button" class="w-100 btn btn-lg btn-outline-primary mt-auto">Seleccionar Plan</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>





<?php 
    include 'footer.php'; 
?>