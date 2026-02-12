<?php

    //A esta página unicamente tendrá acceso el ADMIN (porque es una pagina de edición)

    //Guardar en la base de datos los nuevos datos del nuevo plan (nuevo_plan.php) en la bd

    //En esta página hemos eliminado por completo el HTML, ya que el papel que realizará sserá procesar datos y redirigirlos, en este caso el HTML provoca un error en el funcionamiento


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

    //Recogida de datos del formulario mediante POST
    $nombre = $_POST['nombre'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $ram    = $_POST['ram_mb'] ?? '';
    $cpu    = $_POST['cpu_pct'] ?? '';

    //Validación, Comprobar que ningún campo esté vacío
    if (empty(trim($nombre)) || empty(trim($precio)) || empty(trim($ram)) || empty(trim($cpu))) {
        $mensaje = "Los campos no pueden estar vacíos";
        header("Location: error.php?mensaje=" . urlencode($mensaje));
        exit();
    } 

    //conexión con la bd
    $conexion = mysqli_connect("localhost", "root", "", "ytronhosting");

    //Comprobación de la conexión
    if (!$conexion) {
        $mensaje = "Error al intentar conectarse con la Base de datos: YtronHosting";
        header("Location: error.php?mensaje=" . urlencode($mensaje));
        exit();
    }


    //sentencia prepada para insertar datos en la tabla plan, con seguridad para evitar inyecciones SQL
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