<?php 
    include 'cabecera.php';

    //Eliminamos las etiquetas de apertura y cierre de body y html porque ya se encuentran en la cabecera y el footer

    //A esta página unicamente tendrá acceso el ADMIN (porque es una pagina donde se muestran los planes contratados de cada usuario)


    //Solo acceso al usuario administrador
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }

    //Para conectar por localhost a la BD
    //$conexion = mysqli_connect("localhost", "root", "", "ytronhosting");
        
    //Para conectar a la VM en la que se encuentra alojada la BD
    $conexion = mysqli_connect("10.10.30.10", "root", "", "ytronhosting");

    
    //Consulta para obtener qué usuario tiene qué plan a través de las líneas de factura (al ser una consulta estática, no hay puerta de entrada para un atacante, imposibilitando así inyecciones SQL)
    $sql = "SELECT u.nombre as usuario, u.email, l.id as linea_id, l.concepto as plan, f.id as factura_id, f.fecha_vencimiento 
            FROM usuario u 
            JOIN factura f ON u.id = f.usuario_id 
            JOIN linea l ON f.id = l.factura_id 
            ORDER BY f.id DESC";

    $resultado = mysqli_query($conexion, $sql);
?>

<div class="container mt-4">
    <h2 class="mb-4"><i class="bi bi-people"></i> Control de Planes de Usuarios</h2>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Plan Contratado</th>
                            <th>Factura Ref.</th>
                            <th>Vencimiento</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($fila['usuario']); ?></strong></td>
                            <td><?php echo htmlspecialchars($fila['email']); ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($fila['plan']); ?></span></td>
                            <td>#<?php echo $fila['factura_id']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($fila['fecha_vencimiento'])); ?></td>
                            <td class="text-center">
                                <button onclick="confirmarEliminarLinea(<?php echo $fila['linea_id']; ?>)" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i> Quitar Plan
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<!-- script de control de la ventana emergente, pide una confirmación antes de eleiminar el plan al usuario --> 
<script>
    function confirmarEliminarLinea(id) {
        Swal.fire({
            title: '¿Quitar este plan?',
            text: "Esta acción eliminará el servicio del perfil del usuario.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'red',
            cancelButtonColor: 'blue',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "borrar_linea_usuario.php?id=" + id;
            }
        })
    }
</script>



<!-- script de control de la ventana emergente, lee la URL buscando "?eliminado=exito" para mostrar la ventana --> 
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.get('eliminado') === 'exito') {
            Swal.fire({
                title: '¡Plan Eliminado!',
                text: 'El servicio ha sido retirado del usuario correctamente.',
                icon: 'success',
                confirmButtonColor: 'green',
                confirmButtonText: 'Entendido'
            }).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }

        //Mensaje de error (por si falla la base de datos o el ID es inválido)
        if (urlParams.get('error') === 'fallo') {
            Swal.fire({
                title: 'Error al eliminar',
                text: 'No se pudo procesar la solicitud. Inténtalo de nuevo.',
                icon: 'error',
                confirmButtonColor: 'red',
                confirmButtonText: 'Cerrar'
            }).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }
    });
</script>

<?php 
    include 'footer.php'; 
?>