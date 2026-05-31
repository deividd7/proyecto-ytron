<?php
/**
 * Cierre de sesión.
 * Destruye la sesión del usuario activo.
 */

    // pagina cierre sesion libre

    // inicializa y destruye sesion
    require_once __DIR__ . '/sesion_db.php';
    session_start();
    session_destroy();      // destruye sesion activa completamente
    header("Location: home.php");    // redirige a la portada
    exit();
?>