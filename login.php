<?php
/**
 * Inicio de sesión centralizado.
 * Valida credenciales e inicia sesiones seguras.
 */
    require_once __DIR__ . '/sesion_db.php';
    session_start();
    $error = "";    
    $exito = "";    

    if (isset($_SESSION['usuario'])) {
        header("Location: perfil.php");
        exit();
    }

    if (isset($_GET['registro']) && $_GET['registro'] == 'exito') {
        $exito = "¡Registro completado! Ya puedes iniciar sesión.";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email'] ?? ''); 
        $pass = $_POST['password'] ?? '';

        if (empty($email) || empty($pass)) {
            $error = "Por favor, introduce tu correo electrónico y contraseña.";
        } else {
            require_once __DIR__ . '/db_conexion.php';
            $conexion = getDbConnection();

            $consulta = "SELECT id, nombre, email, password, admin FROM usuario WHERE email = ? AND activo = 1";
            $stmt = mysqli_prepare($conexion, $consulta);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                $resultado = mysqli_stmt_get_result($stmt);

                if ($fila = mysqli_fetch_assoc($resultado)) {
                    if (password_verify($pass, $fila['password'])) {
                        session_regenerate_id(true);
                        $_SESSION['usuario_id'] = $fila['id'];
                        $_SESSION['usuario'] = $fila['nombre'];
                        $_SESSION['email'] = $fila['email'];
                        $_SESSION['es_admin'] = $fila['admin'];
                        
                        header("Location: perfil.php");
                        exit();
                    } else {
                        $error = "Contraseña incorrecta.";
                    }
                } else {
                    $error = "No existe una cuenta activa con ese correo.";
                }
                mysqli_stmt_close($stmt);
            }
            mysqli_close($conexion);
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Ytron Hosting - Iniciar Sesión</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="css/estilos.css?v=2.1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-dark text-white d-flex align-items-center justify-content-center yt-auth-wrapper">

    <div class="container d-flex justify-content-center align-items-center flex-grow-1 fade-in">
        <div class="card-yt p-5 shadow-lg w-100 yt-auth-card">
            <div class="text-center mb-4">
                <a href="home.php">
                    <img src="imagenes/Logo.png" alt="Ytron Logo" class="yt-auth-logo mb-3" onerror="this.style.display='none'">
                </a>
                <h2 class="fw-bold text-white mb-2">Iniciar Sesión</h2>
                <p class="text-light small">Accede a tu panel de control de Ytron Hosting</p>
            </div>
            
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label text-light text-opacity-75 small fw-bold">CORREO ELECTRÓNICO</label>
                    <input type="email" name="email" class="search-yt" placeholder="tu@correo.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-light text-opacity-75 small fw-bold">CONTRASEÑA</label>
                    <input type="password" name="password" class="search-yt" placeholder="••••••••" required>
                </div>

                <div class="text-end mb-4">
                    <a href="recuperar_password.php" class="text-decoration-none small fw-bold yt-auth-link"><i class="bi bi-unlock me-1"></i>¿Olvidaste tu contraseña?</a>
                </div>

                <div class="d-grid mt-2">
                    <button type="submit" class="btn btn-yt-primary py-2 fw-bold">
                        ENTRAR <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted small mb-0">¿No tienes cuenta? <a href="registro.php" class="text-decoration-none fw-bold text-yt-cyan">Regístrate aquí</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($error): ?>
                Swal.fire({
                    title: '¡Error de acceso!',
                    text: '<?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>',
                    icon: 'error',
                    background: '#12121c',
                    color: '#e8eaf6',
                    confirmButtonColor: '#ff1744',
                    confirmButtonText: 'Intentar de nuevo'
                });
            <?php endif; ?>

            <?php if ($exito): ?>
                Swal.fire({
                    title: '¡Bienvenido!',
                    text: '<?php echo htmlspecialchars($exito, ENT_QUOTES, 'UTF-8'); ?>',
                    icon: 'success',
                    background: '#12121c',
                    color: '#e8eaf6',
                    confirmButtonColor: '#00e5a0',
                    confirmButtonText: 'Genial'
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>