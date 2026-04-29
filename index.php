<?php 

    // A esta página unicamente tendrá acceso el ADMIN (porque es una pagina de edición)

    include 'cabecera.php';



    
    //Método de doble seguridad, primer bloqueo a usuarios no logueados y segundo bloqueo a usuarios sin permisos admin
    //Inicio de sesión del usuario, si no ha iniciado sesión, te redirige a login. Impide que usuarios no logueados puedan acceder
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    //protección de la página, si el usuario no es admin, se le redirige a home.php
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }



    //Conexión a ytronhosting
    //Para conectar por localhost a la BD
    //$conexion = mysqli_connect("localhost", "root", "", "ytronhosting");
        
    //Para conectar a la VM en la que se encuentra alojada la BD
    $conexion = mysqli_connect("10.10.30.10", "root", "", "ytronhosting");


    //Seleccionamos los planes
    $sql = "SELECT * FROM plan ORDER BY precio ASC";
    $resultado = mysqli_query($conexion, $sql);
?>


<!-- Eliminamos las etiquetas de apertura y cierre de body y html porque ya se encuentran en la cabecera y el footer-->



<div class="d-flex justify-content-between align-items-center mb-3">

    <h2>Listado de Planes de Hosting</h2>
    
    <a href="nuevo_plan.php" class="btn btn-success index-boton">+ Crear Nuevo Plan</a>

</div>

<table class="table table-striped table-hover mt-4">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nombre del Plan</th>
            <th>Precio (€)</th>
            <th>RAM (MB)</th>
            <th>CPU (Cores)</th>
            <th>Acciones</th> 
        </tr>
    </thead>
    
    <tbody>
        <?php if (mysqli_num_rows($resultado) > 0): ?>
            <?php while ($p = mysqli_fetch_assoc($resultado)): ?>
                <tr>

                    <td><?php echo $p['id']; ?></td>
                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($p['precio']); ?> €</td>
                    <td><?php echo htmlspecialchars($p['ram_mb']); ?></td>
                    <td><?php echo htmlspecialchars($p['cpu_pct']); ?></td>
                    
                    <td>
                        <a href="editar_plan.php?id=<?php echo $p['id']; ?>" class="btn btn-warning btn-sm index-boton">Editar</a>
                        
                        <!-- Boton Borrar, que hace uso de la ventana emergente de la librería que hemos añadido en cabecera.php -->
                        <a href="javascript:void(0);"   
                            class="btn btn-danger btn-sm index-boton" 
                            onclick="confirmarEliminacion(<?php echo $p['id']; ?>)">
                            Borrar
                        </a>
                    </td>

                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No hay planes creados aún.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>



<script>    //script de control de la ventana emergente, detecta el click y frena el borrado preguntando antes si estas seguro
    function confirmarEliminacion(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Estás a punto de eliminar el plan ID: " + id,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'blue',
            cancelButtonColor: 'red',
            confirmButtonText: 'Sí, borrar plan',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                //Si el usuario hace clic en el botón azul, se redirige a borrar_plan.php y se elimina el plan
                window.location.href = "borrar_plan.php?id=" + id;
            }
        })
    }
</script>


<script>   //script de control de la ventana emergente, lee la URL buscando "?creado=exito" para mostrar la ventana
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('creado') === 'exito') {
        Swal.fire({
            title: '¡Plan Creado!',
            text: 'El nuevo plan de hosting se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: 'blue',
            confirmButtonText: 'Genial'
        }).then(() => {
            //Esto limpia la URL para que no salga el mensaje al recargar
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
</script>


<script>   //script de control de la ventana emergente, lee la URL buscando "?editado=exito" para mostrar la ventana
    if (urlParams.get('editado') === 'exito') {
        Swal.fire({
            title: '¡Actualizado!',
            text: 'Los cambios en el plan se han guardado correctamente.',
            icon: 'success',
            confirmButtonColor: 'blue',
            confirmButtonText: 'Entendido'
        }).then(() => {
            //Limpia la URL para evitar que el mensaje se repita al refrescar
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
</script>

<?php 
    mysqli_close($conexion);
    include 'footer.php';
?>

