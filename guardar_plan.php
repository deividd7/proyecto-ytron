<?php
/**
 * Procesador para guardar planes.
 * Solo accesible para administradores.
 */

    // archivo sin salida html


    // valida sesion de admin
    // verifica usuario logueado
    session_start();
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    // redirige si no admin
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }

    // recoge datos por post
    $nombre = $_POST['nombre'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $ram    = $_POST['ram_mb'] ?? '';
    $cpu    = $_POST['cpu_pct'] ?? '';

    // valida campos vacios
    if (empty(trim($nombre)) || empty(trim($precio)) || empty(trim($ram)) || empty(trim($cpu))) {
        $mensaje = "Los campos no pueden estar vacíos";
        header("Location: error.php?mensaje=" . urlencode($mensaje));
        exit();
    } 

    // conecta base de datos
        
    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();


    // verifica conexion valida
    if (!$conexion) {
        $mensaje = "Error al intentar conectarse con la Base de datos: YtronHosting";
        header("Location: error.php?mensaje=" . urlencode($mensaje));
        exit();
    }


    // prepara query anti inyeccion
    $sql = "INSERT INTO plan (nombre, precio, ram_mb, cpu_pct) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param($stmt, "sddi", $nombre, $precio, $ram, $cpu);




    if (mysqli_stmt_execute($stmt)) {
        mysqli_close($conexion);
        header("Location: index.php?creado=exito");  //Le añadimos la terminación "?creado=exito" para que se guarde en la url
        exit();
    } else {
        $mensaje = "Error en la consulta: " . mysqli_error($conexion);
        header("Location: error.php?mensaje=" . urlencode($mensaje));
        exit();
    }
?>