<?php
/**
 * quitar_carrito.php
 * Elimina un plan del carrito.
 */
    require_once __DIR__ . '/sesion_db.php';
    session_start();

    // protege sesion estrictamente
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    // valida id del plan
    $plan_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

    if ($plan_id && isset($_SESSION['carrito'][$plan_id])) {
        unset($_SESSION['carrito'][$plan_id]);
    }

    header("Location: perfil.php");
    exit();
?>
