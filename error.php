<?php 
/**
 * Página genérica de errores.
 * Muestra mensajes de error formateados.
 */
    // incluye cabecera sesion estilos
    include 'cabecera.php';
        
    // obtiene mensaje de url
    $error_msg = $_GET['mensaje'] ?? 'Error desconocido'; 
?>

<!-- pagina de acceso libre -->


<div class="alert alert-danger mt-5">
    <h2>Lo sentimos</h2>
    <p>
        <?php echo htmlspecialchars($error_msg); ?>
    </p>
    
    <a href="javascript:history.back()" class="btn btn-primary">Volver atrás</a>
</div>



<?php 
    include 'footer.php';
?>
