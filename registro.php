<?php
    $mensaje = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = $_POST['nombre'] ?? ''; 
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';

        if (!empty(trim($email)) && !empty(trim($pass))) {
            $conexion = mysqli_connect("localhost", "root", "", "ytronhosting");

            // Seguridad 1: Ciframos la contraseña (creamos la huella)
            $pass_segura = password_hash($pass, PASSWORD_DEFAULT);

            // Seguridad 2: Sentencia preparada para evitar inyecciones SQL
            $sql = "INSERT INTO usuario (nombre, email, password) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $nombre, $email, $pass_segura);

            if (mysqli_stmt_execute($stmt)) {
                // ÉXITO: Redirigimos al login con un aviso
                header("Location: login.php?registro=exito");
                exit();
            } else {
                $mensaje = "Error: El email ya existe o hay un problema con la base de datos.";
            }
            mysqli_close($conexion);
        } else {
            $mensaje = "Por favor, rellena todos los campos.";
        }
    }
?>


<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Página de Registro</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="David Pintado">       
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> 
        <link rel="stylesheet" type="text/css" href="css/estilos.css">
    </head>

        
    <body class="d-flex justify-content-center align-items-center vh-100 bg-light">
        <div class="card p-4 shadow" style="width: 350px;">
            <h2 class="text-center mb-4">Crear Cuenta</h2>
            
            <?php if ($mensaje) echo "<div class='alert alert-danger'>$mensaje</div>"; ?>
            
            <form action="registro.php" method="POST">

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombre de Usuario</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100">Registrarme</button>
                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none">¿Ya tienes cuenta? Entra aquí</a>
                </div>

            </form>
        </div>
    </body>
</html>





