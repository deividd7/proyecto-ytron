<?php


    // A esta página unicamente tendrá acceso el ADMIN (porque es una pagina de borrado)


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

    //Recogida del ID
    $id = $_GET['id'] ?? '';

    if (!empty($id)) {
        $conexion = mysqli_connect("localhost", "root", "", "ytronhosting");

        //Verificamos conexión
        if (!$conexion) {
            header("Location: lista_planes_usuarios.php?error=db");
            exit();
        }

        //Sentencia preparada para evitar inyecciones SQL
        $sql = "DELETE FROM linea WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_close($conexion);
            header("Location: lista_planes_usuarios.php?eliminado=exito");
            exit();
        } else {
            mysqli_close($conexion);
            header("Location: lista_planes_usuarios.php?error=fallo");
            exit();
        }
    } else {
        //Si no hay ID, volvemos a la lista sin hacer nada
        header("Location: lista_planes_usuarios.php");
        exit();
    }
?>