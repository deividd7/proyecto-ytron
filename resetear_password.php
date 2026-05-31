<?php
/**
 * Formulario de nueva contraseña.
 * Valida token y actualiza credenciales.
 */
    require_once __DIR__ . '/sesion_db.php';
    session_start();

    $mensaje = "";
    $tipo_alerta = "";
    $token_valido = false;
    $usuario_id = null;

    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    // valida token recibido url
    $token = $_GET['token'] ?? $_POST['token'] ?? '';

    if (!empty($token)) {
        // comprueba vigencia del token
        $sql = "SELECT id FROM usuario WHERE reset_token = ? AND reset_expiracion > NOW() AND activo = 1";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($res)) {
            $token_valido = true;
            $usuario_id = $user['id'];
        } else {
            $mensaje = "El enlace de recuperación es inválido o ha expirado.";
            $tipo_alerta = "error";
        }
        mysqli_stmt_close($stmt);
    } else {
        $mensaje = "No se ha proporcionado un token de seguridad.";
        $tipo_alerta = "error";
    }

    // procesa formulario nueva pass
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valido) {
        $nueva_pass = $_POST['password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        // verifica longitud minima clave
        if (strlen($nueva_pass) < 8) {
            $mensaje = "La contraseña debe tener al menos 8 caracteres.";
            $tipo_alerta = "warning";
        } elseif ($nueva_pass !== $confirm_pass) {
            $mensaje = "Las contraseñas no coinciden.";
            $tipo_alerta = "warning";
        } else {
            // actualiza pass anula token
            $pass_hash = password_hash($nueva_pass, PASSWORD_BCRYPT);
            
            $sql_upd = "UPDATE usuario SET password = ?, reset_token = NULL, reset_expiracion = NULL WHERE id = ?";
            $stmt_upd = mysqli_prepare($conexion, $sql_upd);
            mysqli_stmt_bind_param($stmt_upd, "si", $pass_hash, $usuario_id);
            
            if (mysqli_stmt_execute($stmt_upd)) {
                $mensaje = "Contraseña actualizada con éxito. Redirigiendo al login...";
                $tipo_alerta = "success";
                $token_valido = false; // oculta formulario al terminar
            } else {
                $mensaje = "Error al actualizar la contraseña en el servidor.";
                $tipo_alerta = "error";
            }
            mysqli_stmt_close($stmt_upd);
        }
    }

    mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Ytron Hosting - Nueva Contraseña</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="css/estilos.css?v=1.9">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-dark text-white d-flex align-items-center justify-content-center yt-auth-wrapper">

    <div class="container d-flex justify-content-center align-items-center flex-grow-1 fade-in">
        <div class="card-yt p-5 shadow-lg w-100 yt-auth-card-simple">
            <div class="text-center mb-4">
                <a href="home.php">
                    <img src="imagenes/Logo.png" alt="Ytron Logo" class="yt-auth-logo mb-3" onerror="this.style.display='none'">
                </a>
                <h2 class="fw-bold text-white mb-2">Restablecer Contraseña</h2>
                <p class="text-light small">Introduce tu nueva credencial de acceso.</p>
            </div>

            <?php if ($token_valido): ?>
            <form action="resetear_password.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="mb-3">
                    <label for="password" class="form-label text-light small fw-bold">NUEVA CONTRASEÑA</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent yt-input-icon"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control bg-transparent text-white yt-border-input" id="password" name="password" required minlength="8" placeholder="Mínimo 8 caracteres">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label text-light small fw-bold">CONFIRMAR CONTRASEÑA</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent yt-input-icon"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control bg-transparent text-white yt-border-input" id="confirm_password" name="confirm_password" required minlength="8" placeholder="Repita la contraseña">
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-yt-primary py-2 fw-bold">
                        GUARDAR CONTRASEÑA <i class="bi bi-check2-circle ms-2"></i>
                    </button>
                </div>
            </form>
            <?php else: ?>
                <div class="text-center mt-4">
                    <a href="recuperar_password.php" class="btn btn-yt-outline">Solicitar nuevo enlace</a>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="login.php" class="text-decoration-none text-light small"><i class="bi bi-arrow-left me-1"></i> Volver al Login</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($mensaje): ?>
                Swal.fire({
                    title: '<?php echo $tipo_alerta == "success" ? "Operación exitosa" : "Aviso de Seguridad"; ?>',
                    text: '<?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>',
                    icon: '<?php echo $tipo_alerta; ?>',
                    confirmButtonColor: '<?php echo $tipo_alerta == "success" ? "#00c853" : "#0d6efd"; ?>',
                    background: '#12121c',
                    color: '#e8eaf6'
                }).then((result) => {
                    <?php if ($tipo_alerta == "success"): ?>
                        window.location.href = "login.php";
                    <?php endif; ?>
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>
