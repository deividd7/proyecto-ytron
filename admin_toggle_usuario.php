<?php
/**
 * endpoint suspende activa usuarios
 * solo admin cambia activo
 */
    header('Content-Type: application/json');
    require_once __DIR__ . '/sesion_db.php';
    session_start();

    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
        exit();
    }

    $user_id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
        exit();
    }

    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    // evita auto suspension admin
    if ($user_id == $_SESSION['usuario_id']) {
        echo json_encode(['status' => 'error', 'message' => 'No puedes suspenderte a ti mismo.']);
        mysqli_close($conexion);
        exit();
    }

    // valida usuario no admin
    $sql = "SELECT id, activo, admin FROM usuario WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
        mysqli_close($conexion);
        exit();
    }

    if ($user['admin'] == 1) {
        echo json_encode(['status' => 'error', 'message' => 'No se puede suspender a otro administrador.']);
        mysqli_close($conexion);
        exit();
    }

    // invierte estado activo
    $nuevo_estado = $user['activo'] ? 0 : 1;
    $sql_update = "UPDATE usuario SET activo = ? WHERE id = ?";
    $stmt_u = mysqli_prepare($conexion, $sql_update);
    mysqli_stmt_bind_param($stmt_u, "ii", $nuevo_estado, $user_id);
    mysqli_stmt_execute($stmt_u);
    mysqli_stmt_close($stmt_u);

    $msg = $nuevo_estado ? 'Usuario activado correctamente.' : 'Usuario suspendido correctamente.';
    echo json_encode(['status' => 'success', 'message' => $msg, 'nuevo_estado' => $nuevo_estado]);

    mysqli_close($conexion);
    exit();
?>
