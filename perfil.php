<?php 
    include 'cabecera.php'; 


    //Eliminamos las etiquetas de apertura y cierre de body y html porque ya se encuentran en la cabecera y el footer


    //Método de doble seguridad, bloqueo a usuarios no logueados que accedan por URL
    //Inicio de sesión del usuario, si no ha iniciado sesión, te redirige a login. Impide que usuarios no logueados puedan acceder
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    //Conexión con la base de datos, para validar el precio real y evitar manipulaciones en el cliente (evitar que el clinte pueda modificar el precio del plan desde el formulario del html)
    //Para conectar por localhost a la BD
    //$conexion = mysqli_connect("localhost", "root", "", "ytronhosting");
        
    //Para conectar a la VM en la que se encuentra alojada la BD
    $conexion = mysqli_connect("10.10.30.10", "root", "", "ytronhosting");



    //Añadir planes al "carrito" en la sesión
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_plan'])) {
        
        // Obtenemos el ID del plan del formulario
        $plan_id = $_POST['plan_id'];

        //Usamos una sentencia preparada para extraer la información verídica de la base de datos de forma segura
        $sql_verificar = "SELECT nombre, precio FROM plan WHERE id = ?";
        $stmt_v = mysqli_prepare($conexion, $sql_verificar);
        mysqli_stmt_bind_param($stmt_v, "i", $plan_id);
        mysqli_stmt_execute($stmt_v);
        $res_v = mysqli_stmt_get_result($stmt_v);
        $datos_plan = mysqli_fetch_assoc($res_v);

        //Si el plan existe en la BD, lo añadimos a la sesión con los datos oficiales
        if ($datos_plan) {
            $nuevo_plan = [
                'id' => $plan_id,
                'nombre' => $datos_plan['nombre'],
                'precio' => $datos_plan['precio']
            ];

            //Si no existe el carrito en la sesión, lo creamos
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }

            //Añadimos el plan al array
            $_SESSION['carrito'][] = $nuevo_plan;
            
            mysqli_stmt_close($stmt_v);
            

            header("Location: planes.php?añadido=exito"); //Le redirigimos a planes.php para que pueda seguir seleccionando planes y ya de paso mandamos el mensaje por url "?añadido=exito"
            exit();
        } else {
            //En caso de que el ID no sea válido o haya sido manipulado
            header("Location: planes.php?error=plan_invalido");
            exit();
        }

    }



    //Eliminar un plan del perfil
    if (isset($_POST['eliminar_plan'])) {
        $indice = $_POST['indice_carrito'];
        if (isset($_SESSION['carrito'][$indice])) {
            unset($_SESSION['carrito'][$indice]);
            //Reindexamos el array para que no queden huecos en los índices
            $_SESSION['carrito'] = array_values($_SESSION['carrito']);
        }
        //Redirigimos a la misma página para refrescar el total y pasamos por url el mensaje (?eliminado=exito)
        header("Location: perfil.php?eliminado=exito");
        exit();
    }



    $total = 0;


    //Consulta (preparada contra inyecciones SQL) encargada de mostrar (si hay) las facturas del usuario 
    $usuario_id = $_SESSION['usuario_id'];
    $sql_historial = "SELECT id, fecha_vencimiento, pagada FROM factura WHERE usuario_id = ? ORDER BY id DESC";
    $stmt_h = mysqli_prepare($conexion, $sql_historial);
    mysqli_stmt_bind_param($stmt_h, "i", $usuario_id);
    mysqli_stmt_execute($stmt_h);
    $res_historial = mysqli_stmt_get_result($stmt_h);
    mysqli_close($conexion);


?>

<div class="container py-5">

    <h2 class="mb-4">Mi Perfil - Planes Seleccionados</h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm p-4">
                <?php if (!empty($_SESSION['carrito'])): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($_SESSION['carrito'] as $indice => $item): 
                            $total += $item['precio']; ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($item['nombre']); ?></h5>     <!-- Utilizamos "htmlspecialchars" como método de seguridad contra Vulnerabilidades XSS -->
                                    <small class="text-muted">Servidor Minecraft</small>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <span class="fw-bold me-3"><?php echo number_format($item['precio'], 2); ?> €</span>
                                    
                                    <form action="perfil.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="indice_carrito" value="<?php echo $indice; ?>">
                                        <button type="submit" name="eliminar_plan" class="btn btn-outline-danger btn-sm perfil-boton">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="mb-3">No has seleccionado ningún plan todavía.</p>
                        <a href="planes.php" class="btn btn-primary perfil-boton">Ver planes disponibles</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm p-4 bg-light">
                <h4>Resumen</h4>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <span>Total a pagar:</span>
                    <span class="h4 text-primary"><?php echo number_format($total, 2); ?> €</span>
                </div>
                
                <?php if ($total > 0): ?>
                    <form action="procesar_compra.php" method="POST" id="formCompra">
                        <button type="button" onclick="confirmarCompra()" class="btn btn-success w-100 btn-lg shadow-sm perfil-boton">
                            Comprar y emitir factura
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <!-- En esta sección se muestran las facturas (cuando se ha generado alguna) del usuario -->
    <div class="card shadow-sm mt-4 p-4">
        <h4>Mi Historial de Facturas</h4>
        <hr>
        <?php if (mysqli_num_rows($res_historial) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th># ID</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($f = mysqli_fetch_assoc($res_historial)): ?>
                            <tr>
                                <td><?php echo $f['id']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($f['fecha_vencimiento'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $f['pagada'] ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                        <?php echo $f['pagada'] ? 'Pagada' : 'Pendiente'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="factura.php?id=<?php echo $f['id']; ?>" class="btn btn-sm btn-outline-primary perfil-boton">
                                        <i class="bi bi-eye"></i> Ver Factura
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">Aún no has realizado ninguna compra.</p>
        <?php endif; ?>
    </div>
</div>




<!-- script de control de la ventana emergente, lee la URL buscando "?eliminado=exito" para mostrar la ventana --> 
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        //Si detecta ?eliminado=exito en la URL
        if (urlParams.get('eliminado') === 'exito') {
            Swal.fire({
                title: '¡Plan eliminado!',
                text: 'El plan ha sido quitado de tu selección correctamente.',
                icon: 'success',
                confirmButtonColor: 'black', 
                confirmButtonText: 'Entendido'
            }).then(() => {
                //Limpiamos la URL para que no vuelva a salir el mensaje al refrescar
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }
    });
</script>



<!-- script de control de la ventana emergente, antes de enviar el formulario (y procesar la compra) con los planes que el usuario ha seleccionado, se le pregunta si está seguro  --> 
<script>
    function confirmarCompra() {
        Swal.fire({
            title: '¿Confirmar compra?',
            text: "Se generará una factura con los planes seleccionados por un total de <?php echo number_format($total, 2); ?> €.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: 'green', // Verde success
            cancelButtonColor: 'red',     // Rojo danger
            confirmButtonText: 'Sí, comprar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario hace clic en "Sí", enviamos el formulario
                document.getElementById('formCompra').submit();
            }
        });
    }
</script>


<!-- script de control de la ventana emergente, lee la URL buscando "?error=acceso_denegado" para mostrar la ventana --> 
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Si detecta ?error=acceso_denegado en la URL
        if (urlParams.get('error') === 'acceso_denegado') {
            Swal.fire({
                title: 'Acceso Denegado',
                text: 'No tienes permiso para ver esta factura o no existe.',
                icon: 'error',
                confirmButtonColor: 'red', // Color rojo
                confirmButtonText: 'Entendido'
            }).then(() => {
                // Limpiamos la URL para que el mensaje no se repita al refrescar
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }
    });
</script>

<?php 
    include 'footer.php'; 
?>