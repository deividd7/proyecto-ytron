<?php 

    //A esta página unicamente tendrá acceso el ADMIN (porque es una pagina de edición)

    include 'cabecera.php'; 
    $id = $_GET['id'] ?? '';

    //protección de la página, si el usuario no es admin, se le redirige a home.php
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }

    if (empty($id)) {
        header("Location: index.php");
        exit();
    }

    $conexion = mysqli_connect("localhost", "root", "", "ytronhosting");
    
    //Obtenemos los datos actuales del plan
    $sql = "SELECT * FROM plan WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $plan = mysqli_fetch_assoc($resultado);

    if (!$plan) {
        header("Location: index.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Editar Planes</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="David Pintado">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="css/estilos.css">
    </head>

    <body>
        <div class="container mt-4">
            <h2>Editar Plan: <?php echo htmlspecialchars($plan['nombre']); ?></h2>
            
            <form action="actualizar_plan.php" method="POST" style="max-width: 500px;">
                <input type="hidden" name="id" value="<?php echo $plan['id']; ?>">

                <div class="mb-3">
                    <label class="form-label">Nombre del Plan:</label>
                    <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($plan['nombre']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Precio Mensual (€):</label>
                    <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $plan['precio']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">RAM (MB):</label>
                    <input type="number" name="ram_mb" class="form-control" value="<?php echo $plan['ram_mb']; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">CPU Limit (%):</label>
                    <input type="number" name="cpu_pct" class="form-control" value="<?php echo $plan['cpu_pct']; ?>">
                </div>

                <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </body>
</html>