<?php 

    // A esta página unicamente tendrá acceso el ADMIN (porque es una pagina de edición)

    include 'cabecera.php';



    //protección de la página, si el usuario no es admin, se le redirige a home.php
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }



    //Conexión a ytronhosting
    $conexion = mysqli_connect("localhost", "root", "", "ytronhosting");

    //Seleccionamos los planes
    $sql = "SELECT * FROM plan ORDER BY precio ASC";
    $resultado = mysqli_query($conexion, $sql);
?>




<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Índice Principal</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="David Pintado">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="css/estilos.css">
    </head>

    <body class="layout-body">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h2>Listado de Planes de Hosting</h2>
            
            <a href="nuevo_plan.php" class="btn btn-success">+ Crear Nuevo Plan</a>

        </div>

        <table class="table table-striped table-hover mt-4">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre del Plan</th>
                    <th>Precio (€)</th>
                    <th>RAM (MB)</th>
                    <th>CPU (%)</th>
                    <th>Acciones</th> 
                </tr>
            </thead>
            
            <tbody>
                <?php if (mysqli_num_rows($resultado) > 0): ?>
                    <?php while ($p = mysqli_fetch_assoc($resultado)): ?>
                        <tr>

                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($p['precio']); ?></td>
                            <td><?php echo htmlspecialchars($p['ram_mb']); ?></td>
                            <td><?php echo htmlspecialchars($p['cpu_pct']); ?></td>
                            
                            <td>
                                <a href="editar_plan.php?id=<?php echo $p['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                
                                <a href="borrar_plan.php?id=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este plan?')">Borrar</a>
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


        <?php 
            mysqli_close($conexion);
            include 'footer.php';
        ?>
        
    </body>
</html>