<?php
/**
 * Elimina un plan de alojamiento del catálogo.
 * Solo accesible por el administrador.
 */

    // verifica sesion activa usuario
    session_start();
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    // bloquea usuarios no admin
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }



    $id = $_GET['id'] ?? '';

    if (!empty($id)) {
        // conecta base datos
        
        require_once __DIR__ . '/db_conexion.php';
        $conexion = getDbConnection();


        // prepara borrado seguro bd
        $sql = "DELETE FROM plan WHERE id = ?";

        $stmt = mysqli_prepare($conexion, $sql);

        mysqli_stmt_bind_param($stmt, "i", $id);    // fuerza tipo integer anti sql
        


        mysqli_stmt_execute($stmt);

        mysqli_close($conexion);
    }

    header("Location: index.php");
    exit();
?>