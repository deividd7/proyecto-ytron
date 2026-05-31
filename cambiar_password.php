<?php
/**
 * API para el cambio de contraseña.
 * Valida clave actual y actualiza hash.
 */
    require_once __DIR__ . '/sesion_db.php';
    session_start();

    // protege acceso no logueado
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No estás autenticado.']);
        exit();
    }

    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $actual = $_POST['password_actual'] ?? '';
        $nueva = $_POST['nueva_password'] ?? '';
        $confirmar = $_POST['confirmar_password'] ?? '';

        if (empty($actual) || empty($nueva) || empty($confirmar)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            exit();
        }

        if (strlen($nueva) < 8) {
            echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 8 caracteres.']);
            exit();
        }

        if ($nueva !== $confirmar) {
            echo json_encode(['success' => false, 'message' => 'Las nuevas contraseñas no coinciden.']);
            exit();
        }

        require_once __DIR__ . '/db_conexion.php';
        $conexion = getDbConnection();
        $usuario_id = $_SESSION['usuario_id'];

        // verifica hash actual
        $sql = "SELECT password FROM usuario WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuario_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($res)) {
            if (password_verify($actual, $row['password'])) {
                // encripta nueva clave
                $nuevo_hash = password_hash($nueva, PASSWORD_BCRYPT);
                
                // guarda nuevo hash bd
                $sql_upd = "UPDATE usuario SET password = ? WHERE id = ?";
                $stmt_upd = mysqli_prepare($conexion, $sql_upd);
                mysqli_stmt_bind_param($stmt_upd, "si", $nuevo_hash, $usuario_id);
                
                if (mysqli_stmt_execute($stmt_upd)) {
                    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error interno al actualizar la contraseña.']);
                }
                mysqli_stmt_close($stmt_upd);
            } else {
                echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el usuario en la base de datos.']);
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conexion);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    }
?>
