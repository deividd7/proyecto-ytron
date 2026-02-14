<?php 
    include 'cabecera.php'; 


    //Eliminamos las etiquetas de apertura y cierre de body y html porque ya se encuentran en la cabecera y el footer

    //Esta página es de libre acceso para usuarios no logueados


    //Método de doble seguridad, bloqueo a usuarios no logueados que accedan por URL
    //Inicio de sesión del usuario, si no ha iniciado sesión, te redirige a login. Impide que usuarios no logueados puedan acceder
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }



    //Añadir planes al "carrito" en la sesión (no corremos riesgo de inyecciones sql porque solo extraemos informacion de la sesión y no de la bd)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_plan'])) {
        $nuevo_plan = [
            'id' => $_POST['plan_id'],
            'nombre' => $_POST['nombre_plan'],
            'precio' => $_POST['precio_plan']
        ];

        //Si no existe el carrito en la sesión, lo creamos
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        //Añadimos el plan al array
        $_SESSION['carrito'][] = $nuevo_plan;
        header("Location: planes.php?añadido=exito");     //Le redirigimos a planes.php para que pueda seguir seleccionando planes y ya de paso mandamos el mensaje por url "?añadido=exito"
        exit();

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
                                    <h5 class="mb-0"><?php echo htmlspecialchars($item['nombre']); ?></h5>
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
                    <button class="btn btn-success w-100 btn-lg shadow-sm perfil-boton">
                        Comprar y emitir factura
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>




<!-- script de control de la ventana emergente, lee la URL buscando "?eliminado=exito" para mostrar la ventana --> 
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Si detecta ?eliminado=exito en la URL
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


<?php 
    include 'footer.php'; 
?>