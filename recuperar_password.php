<?php
/**
 * Solicitud de recuperación.
 * Genera token y envía email de reseteo.
 */
    require_once __DIR__ . '/sesion_db.php';
    session_start();

    $mensaje = "";
    $tipo_alerta = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $mensaje = "Por favor, introduce tu correo electrónico.";
            $tipo_alerta = "warning";
        } else {
            require_once __DIR__ . '/db_conexion.php';
            $conexion = getDbConnection();

            $sql = "SELECT id, nombre FROM usuario WHERE email = ? AND activo = 1";
            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);

            if ($user = mysqli_fetch_assoc($res)) {
                $token = bin2hex(random_bytes(32));
                $expiracion = date('Y-m-d H:i:s', strtotime('+30 minutes'));

                $sql_update = "UPDATE usuario SET reset_token = ?, reset_expiracion = ? WHERE id = ?";
                $stmt_upd = mysqli_prepare($conexion, $sql_update);
                mysqli_stmt_bind_param($stmt_upd, "ssi", $token, $expiracion, $user['id']);
                
                if (mysqli_stmt_execute($stmt_upd)) {
                    require_once __DIR__ . '/correo_helper.php';
                    enviar_correo_ytron($email, "Recuperación de Contraseña - Ytron Hosting", "recuperacion", [
                        'token' => $token
                    ]);
                    $mensaje = "Si el correo electrónico está registrado, recibirás un enlace de recuperación.";
                    $tipo_alerta = "success";
                } else {
                    $mensaje = "Error al generar el token. Inténtalo más tarde.";
                    $tipo_alerta = "error";
                }
                mysqli_stmt_close($stmt_upd);
            } else {
                $mensaje = "Si el correo electrónico está registrado, recibirás un enlace de recuperación.";
                $tipo_alerta = "success";
            }
            mysqli_stmt_close($stmt);
            mysqli_close($conexion);
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Ytron Hosting - Recuperar Contraseña</title>
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
                <h2 class="fw-bold text-white mb-2">Recuperar Contraseña</h2>
                <p class="text-light small">Introduce tu email para recibir un enlace de reseteo.</p>
            </div>

            <form action="recuperar_password.php" method="POST">
                <div class="mb-4">
                    <label for="email" class="form-label text-light text-opacity-75 small fw-bold">CORREO ELECTRÓNICO</label>
                    <input type="email" class="search-yt" id="email" name="email" required placeholder="tu@correo.com">
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-yt-primary py-2 fw-bold">
                        ENVIAR ENLACE <i class="bi bi-send ms-2"></i>
                    </button>
                </div>
            </form>

            <div class="text-center mt-4">
                <a href="login.php" class="text-decoration-none text-light small fw-bold text-yt-muted"><i class="bi bi-arrow-left me-1"></i> Volver al Login</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($mensaje): ?>
                Swal.fire({
                    title: '<?php echo $tipo_alerta == "success" ? "Proceso completado" : "Aviso"; ?>',
                    text: '<?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>',
                    icon: '<?php echo $tipo_alerta; ?>',
                    confirmButtonColor: '<?php echo $tipo_alerta == "success" ? "#00c853" : "#0d6efd"; ?>',
                    background: '#12121c',
                    color: '#e8eaf6'
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>