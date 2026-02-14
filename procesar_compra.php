<?php 
    include 'cabecera.php'; 


    //Eliminamos las etiquetas de apertura y cierre de body y html porque ya se encuentran en la cabecera y el footer

    //Esta página es unicamente de procesamiento de datos (se registran los planes comprados por el usuario en la bd), una vez procesados, el usuario será redirigido a factura.php para observar estos datos


    //Método de doble seguridad, bloqueo a usuarios no logueados que accedan por URL
    //Inicio de sesión del usuario, si no ha iniciado sesión, te redirige a login. Impide que usuarios no logueados puedan acceder
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    
    //Si el carrito (perfil.php) está vacío porque no se ha seleccionado ningún plan, no se podrá acceder
    if (empty($_SESSION['carrito'])) {
        header("Location: perfil.php");
        exit();
    }

    //conexión con la base de datos
    $conexion = mysqli_connect("localhost", "root", "", "ytronhosting");
    $usuario_id = $_SESSION['usuario_id'];
    $fecha_vencimiento = date('Y-m-d', strtotime('+1 month'));  //Añadimos una fecha de vencimiento de ejemplo (1 mes en este caso)


    //Iniciamos la transacción
    mysqli_begin_transaction($conexion);


    try {
        //Inserción de senticia preparada para la tabla factura, evitando inyecciones SQL
        $sql_factura = "INSERT INTO factura (fecha_vencimiento, pagada, usuario_id) VALUES (?, 0, ?)";
        $stmt_f = mysqli_prepare($conexion, $sql_factura);
        mysqli_stmt_bind_param($stmt_f, "si", $fecha_vencimiento, $usuario_id);
        mysqli_stmt_execute($stmt_f);
        
        $factura_id = mysqli_insert_id($conexion);

        //Inserción de senticia preparada para la tabla linea, evitando inyecciones SQL
        $sql_linea = "INSERT INTO linea (factura_id, concepto, cantidad, precio_ud) VALUES (?, ?, 1, ?)";
        $stmt_l = mysqli_prepare($conexion, $sql_linea);

        foreach ($_SESSION['carrito'] as $item) {
            mysqli_stmt_bind_param($stmt_l, "isd", $factura_id, $item['nombre'], $item['precio']);
            mysqli_stmt_execute($stmt_l);
        }


        //Si todo es correcto se guardan los cambios
        mysqli_commit($conexion);
        
        //Limpiar carrito y redirigir a factura.php con el ID de la factura (necesario para poder acceder a ella)
        $_SESSION['carrito'] = [];
        header("Location: factura.php?id=" . $factura_id . "&compra=exito");

    } catch (mysqli_sql_exception $e) {
        //Si algo falla, se deshace la factura creada
        mysqli_rollback($conexion);
        header("Location: perfil.php?error=compra_fallida");
    }

    mysqli_close($conexion);
    exit();




    include 'footer.php'; 
?>

