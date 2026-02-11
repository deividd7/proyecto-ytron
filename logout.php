<?php

    //Página donde el usuario cierra su sesión

    //Lo añadimos para cerrar la sesión
    session_start();
    session_destroy();      //Se destruye y por lo tanto, cierra la sesión
    header("Location: index.php");    //Nos redirige a index 
    exit();
?>