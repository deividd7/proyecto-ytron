<?php

    //Página donde el usuario cierra su sesión y por lo tanto de libre acceso

    //Lo añadimos para cerrar la sesión
    session_start();
    session_destroy();      //Se destruye y por lo tanto, cierra la sesión
    header("Location: home.php");    //Al cerrar sesión nos redirigirá por defecto a home.php
    exit();
?>