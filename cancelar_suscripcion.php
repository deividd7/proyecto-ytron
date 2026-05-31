<?php 
/**
 * Cancela suscripción de un plan.
 * Marca factura impagada sin borrar contenedor.
 */
    require_once __DIR__ . '/sesion_db.php';
    session_start();
    if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }

    $linea_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$linea_id) { header("Location: perfil.php"); exit(); }

    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    // verifica propiedad factura
    $sql_check = "SELECT l.id, f.id as factura_id FROM linea l 
                  JOIN factura f ON l.factura_id = f.id WHERE l.id = ? AND f.usuario_id = ?";
    $stmt_c = mysqli_prepare($conexion, $sql_check);
    mysqli_stmt_bind_param($stmt_c, "ii", $linea_id, $_SESSION['usuario_id']);
    mysqli_stmt_execute($stmt_c);
    $linea = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_c));

    if ($linea) {
        // marca factura como impagada
        $sql_cancel = "UPDATE factura SET pagada = 0 WHERE id = ?";
        $stmt_cancel = mysqli_prepare($conexion, $sql_cancel);
        mysqli_stmt_bind_param($stmt_cancel, "i", $linea['factura_id']);
        mysqli_stmt_execute($stmt_cancel);
        header("Location: factura.php?id=" . $linea['factura_id'] . "&cancelado=exito");
    } else {
        header("Location: perfil.php?error=no_autorizado");
    }
    mysqli_close($conexion);
    exit();
?>