<?php 
    // Página de error
    // Usamos la cabecera para mantener la sesión y estilos
    include 'cabecera.php';
        
    // Extraemos el mensaje de error de la URL
    $error_msg = $_GET['mensaje'] ?? 'Error desconocido'; 
?>



<!DOCTYPE html>
<html lang="es">
    <head>
        <title>David</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="David Pintado">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="css/estilos.css">
    </head>

    <body>

        <div class="alert alert-danger mt-5">
            <h2>Error en la operación</h2>
            <p>
                <?php 
                    echo htmlspecialchars($error_msg);           //Mostramos el contenido de la variable error
                ?>
            </p>
            
            <a href="nuevo_plan.php" class="btn btn-primary">Volver al formulario</a>
        </div>

     
    </body>
</html>