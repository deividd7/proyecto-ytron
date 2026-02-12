<?php
    session_start();
    $error = "";
    $exito = "";

    //Página donde el usuario inicia sesión y por lo tanto de libre acceso

    //Verificamos si el usuario viene de crear una cuenta
    if (isset($_GET['registro']) && $_GET['registro'] == 'exito') {
        $exito = "¡Registro completado! Ya puedes iniciar sesión.";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'] ?? ''; 
        $pass = $_POST['password'] ?? '';

        //Conexión a la BD
        $conexion = mysqli_connect("localhost", "root", "", "ytronhosting");


        //Consulta
        //Usamos Sentencias Preparadas para evitar SQL Injection
        $consulta = "SELECT id, nombre, email, password, admin FROM usuario WHERE email = ?";

        $stmt = mysqli_prepare($conexion, $consulta);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);



        if ($fila = mysqli_fetch_assoc($resultado)) {

            if (password_verify($pass, $fila['password'])) {   //Graba la contraseña "cifrada" por asi decirlo (la huella, el hash) en la bd y en el proximo login solo lo compara y deja aacceder o no
                $_SESSION['usuario'] = $fila['email']; //Guardamos el email en la sesión
                $_SESSION['usuario_id'] = $fila['id']; //Guardamos también el ID por si acaso
                $_SESSION['nombre'] = $fila['nombre']; //Guardamos también el nombre
                $_SESSION['es_admin'] = $fila['admin']; //Guardamos el booleano admin en la sesion (esto permitira el acceso o no a ciertas partes de la web)
               // header("Location: home.php");   //Al iniciar sesión nos redirigirá por defecto a home.php

                //Mecanismo de persistencia. Redirección dinámica, recibe la variable (from) que almacena el lugar en el que se encontraba el usuario antes de iniciar sesión
                if (isset($_GET['from'])) {
                    //Si existe el parámetro, lo saneamos y redirigimos allí
                    $destino = $_GET['from'];
                } else {
                    //Si no existe, le redirigimos a home.php
                    $destino = "home.php";
                }
        
                header("Location: " . $destino);
                exit();

            } else {
                $error = "Credenciales incorrectas";
            }
        } else {
            $error = "Credenciales incorrectas"; 
        }
        mysqli_close($conexion); 
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Login Hosting</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="David Pintado">       
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> 
        <link rel="stylesheet" type="text/css" href="css/estilos.css">
    </head>

    <body class="d-flex justify-content-center align-items-center vh-100 bg-light">
        <div class="card p-4 shadow" style="width: 300px;">
            <h2 class="text-center mb-4">Acceso</h2>

            <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <?php if ($exito) echo "<div class='alert alert-success'>$exito</div>"; ?>
            
            <form action="login.php<?php echo isset($_GET['from']) ? '?from=' . urlencode($_GET['from']) : ''; ?>" method="POST">  <!-- Mecanismo de persistencia, guarda la variable en la que se ha almacenado de donde venía el usuario y lo redirige allí-->

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombre de Usuario</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Entrar</button>
                <div class="text-center mt-3">
                    <a href="registro.php" class="text-decoration-none">¿No tienes cuenta? Regístrate</a>
                </div>

            </form>
        </div>
    </body>
</html>