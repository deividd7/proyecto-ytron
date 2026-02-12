<?php

    // A esta página unicamente tendrá acceso el ADMIN (porque es una pagina de edición)

    session_start();
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }


    //Método de doble seguridad, primer bloqueo a usuarios no logueados y segundo bloqueo a usuarios sin permisos admin
    //Inicio de sesión del usuario, si no ha iniciado sesión, te redirige a login. Impide que usuarios no logueados puedan acceder
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


    
    $id = $_GET['id'] ?? '';

    if (!empty($id)) {
        $conexion = mysqli_connect("localhost", "root", "", "ytronhosting");

        // Usamos una sentencia preparada para insertar los datos con seguridad
        $sql = "DELETE FROM plan WHERE id = ?";

        $stmt = mysqli_prepare($conexion, $sql);

        mysqli_stmt_bind_param($stmt, "i", $id);    // "i" de entero (integer), que sirve como protección contra la eliminación total de las tablas 
        


        mysqli_stmt_execute($stmt);

        mysqli_close($conexion);
    }

    header("Location: index.php");
    exit();
?>