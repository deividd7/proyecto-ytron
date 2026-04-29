<?php
    $mensaje = "";

    //Página donde se registra por primera vez y por lo tanto de libre acceso


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = $_POST['nombre'] ?? ''; 
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';

        if (!empty(trim($email)) && !empty(trim($pass))) {
            //Para conectar por localhost a la BD
            //$conexion = mysqli_connect("localhost", "root", "", "ytronhosting");
        
            //Para conectar a la VM en la que se encuentra alojada la BD
            $conexion = mysqli_connect("10.10.30.10", "root", "", "ytronhosting");


            //Cifrado de la contraseña: Ciframos la contraseña (creamos la huella, el hash)
            $pass_segura = password_hash($pass, PASSWORD_DEFAULT);

            //Sentencia preparada para evitar inyecciones SQL
            $sql = "INSERT INTO usuario (nombre, email, password) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $nombre, $email, $pass_segura);


                            


            //Utilizamos un try catch para capturar el error fatal que ocurre cuendo intentamos registrarnos (crear un nuevo usuario) que ya se encuentra en la bd, la idea es pasar este error por una ventana emergente
            try {
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: login.php?registro=exito");  //Redirigimos a login con la url  ?registro=exito  para mostrar ventana emergente de cuenta creada
                    exit();
                }
            } catch (mysqli_sql_exception $e) {
                // Si llegamos aquí, es porque PHP lanzó el error fatal y lo hemos "atrapado"
                if ($e->getCode() == 1062) {
                    $mensaje = "El correo electrónico '$email' ya está registrado. Por favor, usa otro o inicia sesión.";
                } else {
                    $mensaje = "Lo sentimos, hubo un error técnico: " . $e->getMessage();
                }
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Librería externa que transforma los mensajes del navegador el ventanas emergentes -->
    </head>

        
    <body class="d-flex justify-content-center align-items-center vh-100 bg-light">
        <div class="card p-4 shadow" style="width: 350px;">
            <h2 class="text-center mb-4">Crear Cuenta</h2>
                        
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


        <script>    //script de control de la ventana emergente, si hay un error en el php, mostramos esta ventana emergente
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($mensaje): ?>
                    Swal.fire({
                        title: '¡Vaya! Algo ha fallado',
                        text: '<?php echo addslashes($mensaje); ?>',
                        icon: 'error',
                        confirmButtonColor: 'green', 
                        confirmButtonText: 'Reintentar'
                    });
                <?php endif; ?>
            });
        </script>


    </body>
</html>





