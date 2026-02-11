<?php

    //A esta página unicamente tendrá acceso el ADMIN (porque es una pagina de edición)

    session_start();
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }


    //protección de la página, si el usuario no es admin, se le redirige a home.php
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

        $conexion = mysqli_connect("localhost", "root", "", "ytronhosting");

        //Sentencia preparada para el UPDATE en la bd, con seguridad contra inyecciones SQL
        $sql = "UPDATE plan SET nombre=?, precio=?, ram_mb=?, cpu_pct=? WHERE id=?";

        $stmt = mysqli_prepare($conexion, $sql);

        mysqli_stmt_bind_param($stmt, "sddii", $nombre, $precio, $ram, $cpu, $id);


        if (mysqli_stmt_execute($stmt)) {
            mysqli_close($conexion);
            header("Location: index.php");
            exit();
        } else {
            $error = "Error al actualizar: " . mysqli_error($conexion);
            mysqli_close($conexion);
            header("Location: error.php?mensaje=" . urlencode($error));
            exit();
        }
    }
?>