<?php
/**
 * Controlador para la actualización de un plan.
 * Solo accesible para administradores desde el panel.
 */
    // solo admin puede editar
    session_start();
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    // bloquea usuarios no admin
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id     = $_POST['id'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $precio = $_POST['precio'] ?? '';
        $ram    = $_POST['ram_mb'] ?? '';
        $cpu    = $_POST['cpu_pct'] ?? '';

        if (empty($id) || empty($nombre) || empty($precio)) {
            header("Location: error.php?mensaje=Faltan datos obligatorios");
            exit();
        }

        // conecta a base datos
        require_once __DIR__ . '/db_conexion.php';
        $conexion = getDbConnection();

        // actualiza plan seguro sql
        $sql = "UPDATE plan SET nombre=?, precio=?, ram_mb=?, cpu_pct=? WHERE id=?";
        $stmt = mysqli_prepare($conexion, $sql);

        mysqli_stmt_bind_param($stmt, "sddii", $nombre, $precio, $ram, $cpu, $id);


        if (mysqli_stmt_execute($stmt)) {
            mysqli_close($conexion);
            header("Location: index.php?editado=exito");   //Le añadimos la terminación "?editado=exito" para que se guarde en la url (para poder mostrar la ventana emergenete)
            exit();
        } else {
            $error = "Error al actualizar: " . mysqli_error($conexion);
            mysqli_close($conexion);
            header("Location: error.php?mensaje=" . urlencode($error));
            exit();
        }
    }
?>