<?php 
    // Página de error
    // Usamos la cabecera para mantener la sesión y estilos
    include 'cabecera.php';
        
    // Extraemos el mensaje de error de la URL
    $error_msg = $_GET['mensaje'] ?? 'Error desconocido'; 
?>

<!-- Esta página es de libre acceso para usuarios no logueados -->


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
