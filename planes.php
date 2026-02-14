<?php 
    include 'cabecera.php'; 

    //Conexión a ytronhosting
    $conexion = mysqli_connect("localhost", "root", "", "ytronhosting");

    //Preparar la sentencia, en esta no hay problema porque solo extrae y muestra informacion de la bd
    $sql = "SELECT id, nombre, precio, ram_mb, cpu_pct FROM plan";
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
                                </p>
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
                        
                        <form action="perfil.php" method="POST">    <!-- Se utiliza un array multidimensional dentro de la variable global ($_SESSION) para almacenar de forma persistente y temporal los datos de los planes (ID, nombre y precio) que el usuario selecciona -->
                            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                            
                            <input type="hidden" name="nombre_plan" value="<?php echo htmlspecialchars($plan['nombre']); ?>">
                            
                            <input type="hidden" name="precio_plan" value="<?php echo $plan['precio']; ?>">
                            
                            <button type="submit" name="agregar_plan" class="w-100 btn btn-lg btn-outline-primary mt-auto">
                                Seleccionar Plan
                            </button>
                        </form>                
                    
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>



<!-- script de control de la ventana emergente, lee la URL buscando "?añadido=exito" para mostrar la ventana --> 
<?php if (isset($_GET['añadido']) && $_GET['añadido'] == 'exito'): ?>
<script>
    Swal.fire({
        title: "¡Plan Seleccionado!",
        text: "El plan se ha añadido a tu carrito correctamente.",
        icon: "success",
        showConfirmButton: true,
        confirmButtonColor: "blue",
        confirmButtonText: "Ver mi carrito",
        showCancelButton: true,
        cancelButtonText: "Seguir mirando",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "perfil.php";
        }
        //Limpiamos la URL para que el mensaje no se repita al refrescar manualmente
        window.history.replaceState({}, document.title, window.location.pathname);
    });
</script>
<?php endif; ?>


<?php 
    include 'footer.php'; 
?>