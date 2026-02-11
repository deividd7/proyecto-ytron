<?php
    session_start();

    // Si no existe la variable de sesión usuario, redirigimos a login
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Ytron Hosting</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="David Pintado">    
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> 
        <link rel="stylesheet" type="text/css" href="css/estilos.css">
    </head>

    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 shadow-sm">
            <div class="container-fluid">

                <a class="navbar-brand" href="index.php">
                    <img src="Logo.png" alt="Ytron Logo" style="height: 50px; width: auto;">
                </a>                


                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="d-flex align-items-center ms-auto">
                        <span class="me-3 text-secondary">
                            Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></strong>
                        </span>
                        <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
                    </div>
                </div>

            </div>
        </nav>

        <div class="container"> <!-- Necesario para el tema de cuadrar la visualización de la pagina con css -->
    </body>
</html>