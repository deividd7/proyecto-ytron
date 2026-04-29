<?php 
    session_start();


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
    //Para conectar por localhost a la BD
    //$conexion = mysqli_connect("localhost", "root", "", "ytronhosting");
        
    //Para conectar a la VM en la que se encuentra alojada la BD
    $conexion = mysqli_connect("10.10.30.10", "root", "", "ytronhosting");

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




        //Inicialización de la variable (Fase implementación de Ansible)
        $primer_plan_id = null;


        foreach ($_SESSION['carrito'] as $item) {
            mysqli_stmt_bind_param($stmt_l, "isd", $factura_id, $item['nombre'], $item['precio']);
            mysqli_stmt_execute($stmt_l);

            
            //Guardamos el plan del carrito para el despliegue en Ansible (Fase implementación de Ansible)
            if ($primer_plan_id === null) {
                $primer_plan_id = $item['id'];
            }

        }




        

        
        //Creación del Servidor en la Tabla Servidor en la BD (Fase implementación de Ansible)
        $nodo_id = 1;
        
        //Generamos un UUID único en el servidor
        $uuid = bin2hex(random_bytes(16));  //Una cadena de 16 dígitos random

        $sql_servidor = "
            INSERT INTO servidor (uuid, ip, puerto, estado, usuario_id, nodo_id, plan_id)
            VALUES (?, NULL, NULL, 'provisionando', ?, ?, ?)
        ";
        $stmt_s = mysqli_prepare($conexion, $sql_servidor);
        mysqli_stmt_bind_param($stmt_s, "siii", $uuid, $usuario_id, $nodo_id, $primer_plan_id);
        mysqli_stmt_execute($stmt_s);

        $servidor_id = mysqli_insert_id($conexion);



        
        //Lanzamiento de Ansible sobre Docker (Fase implementación de Ansible)
        //Ruta hacia el archivo, utilizando WSL en Windows y una dirección hacia el archivo
        $cmd = "wsl ansible-playbook /mnt/c/xampp/htdocs/AnsibleYtronHosting/PlaybookAnsible.yml -e servidor_id={$servidor_id}";

        exec($cmd, $output, $return_code);

        
        //Si Ansible falla, NO se cancela la compra
        if ($return_code !== 0) {
            mysqli_query(
                $conexion,
                "UPDATE servidor SET estado='error' WHERE id = $servidor_id"
            );
        } else {
            mysqli_query(
                $conexion,
                "UPDATE servidor SET estado='activo' WHERE id = $servidor_id"
            );
        }






        //Si todo es correcto se guardan los cambios
        mysqli_commit($conexion);
        
        //Limpiar carrito y redirigir a factura.php con el ID de la factura (necesario para poder acceder a ella)
        $_SESSION['carrito'] = [];
        header("Location: factura.php?id=" . $factura_id . "&compra=exito");

    } catch (Exception $e) {    //MODIFICADO (Fase implementación de Ansible)
        //Si algo falla, se deshace la factura creada
        mysqli_rollback($conexion);
        header("Location: perfil.php?error=compra_fallida");
    }

    mysqli_close($conexion);
    exit();





?>

