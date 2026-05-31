<?php
/**
 * Registro de nuevos usuarios.
 * Valida contraseñas y crea cuentas.
 */
    $mensaje = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = $_POST['nombre'] ?? ''; 
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';

        $regex_password = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/';

        if (empty(trim($nombre)) || empty(trim($email)) || empty(trim($pass))) {
            $mensaje = "Por favor, rellene todos los campos obligatorios.";
        } elseif (!preg_match($regex_password, $pass)) {
            $mensaje = "La contraseña debe tener un mínimo de 8 caracteres e incluir al menos una letra mayúscula, una minúscula y un número.";
        } else {
            require_once __DIR__ . '/db_conexion.php';
            $conexion = getDbConnection();

            $pass_segura = password_hash($pass, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuario (nombre, email, password) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $nombre, $email, $pass_segura);

            try {
                if (mysqli_stmt_execute($stmt)) {
                    require_once __DIR__ . '/correo_helper.php';
                    enviar_correo_ytron($email, "¡Bienvenido a Ytron Hosting!", "bienvenida", ['nombre' => $nombre]);

                    mysqli_stmt_close($stmt);
                    mysqli_close($conexion);
                    header("Location: login.php?registro=exito");
                    exit();
                } else {
                    $mensaje = "Error al procesar el registro en el servidor.";
                }
            } catch (Exception $e) {
                $mensaje = "El correo electrónico introducido ya se encuentra registrado.";
            }
            
            if (isset($stmt) && $stmt !== false) {
                mysqli_stmt_close($stmt);
            }
            mysqli_close($conexion);
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Ytron Hosting - Registro</title>
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
                <h2 class="fw-bold text-white mb-2">Crear Cuenta</h2>
                <p class="text-light small">Únete a la élite del hosting Zero-Touch</p>
            </div>

            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label text-light text-opacity-75 small fw-bold">NOMBRE COMPLETO</label>
                    <input type="text" name="nombre" class="search-yt" placeholder="Tu nombre" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label text-light text-opacity-75 small fw-bold">CORREO ELECTRÓNICO</label>
                    <input type="email" name="email" class="search-yt" placeholder="tu@correo.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label text-light text-opacity-75 small fw-bold">CONTRASEÑA</label>
                    <input type="password" name="password" class="search-yt" placeholder="••••••••" 
                           pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"
                           title="La contraseña debe tener un mínimo de 8 caracteres, incluir al menos una letra mayúscula, una minúscula y un número." required>
                    <div class="form-text text-light opacity-50 yt-auth-form-text">
                        Requisitos: Mín. 8 caracteres, 1 mayúscula y 1 número.
                    </div>
                </div>

                <div class="d-grid mt-2">
                    <button type="submit" class="btn btn-yt-primary py-2 fw-bold">
                        REGISTRARME <i class="bi bi-person-plus ms-2"></i>
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-muted small mb-0">¿Ya tienes cuenta? <a href="login.php" class="text-decoration-none fw-bold text-yt-cyan">Entra aquí</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($mensaje): ?>
                Swal.fire({
                    title: '¡Atención!',
                    text: '<?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>',
                    icon: 'warning',
                    confirmButtonColor: '#00e5a0', 
                    background: '#12121c',
                    color: '#e8eaf6'
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>