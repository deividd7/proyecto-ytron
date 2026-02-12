<?php 

    include 'cabecera.php'; 
    
    
    //Método de doble seguridad, primer bloqueo a usuarios no logueados y segundo bloqueo a usuarios sin permisos admin
    //Inicio de sesión del usuario, si no ha iniciado sesión, te redirige a login. Impide que usuarios no logueados puedan acceder
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    //protección de la página, si el usuario no es admin, se le redirige a home.php
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }

?>

<!-- A esta página unicamente tendrá acceso el ADMIN -->





<div class="layout-body">


    <h2>Crear Nuevo Plan</h2>

    <form action="guardar_plan.php" method="POST" class="mt-4" style="max-width: 500px;">
        
        <div class="mb-3">
            <label class="form-label">Nombre del Plan:</label>
            <input type="text" name="nombre" class="form-control" placeholder="Ej: Básico, Pro..." required>
        </div>

        <div class="mb-3">
            <label class="form-label">Precio Mensual (€):</label>
            <input type="number" step="0.01" name="precio" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">RAM (MB):</label>
            <input type="number" name="ram_mb" class="form-control" placeholder="Ej: 1024">
        </div>

        <div class="mb-3">
            <label class="form-label">CPU Cores:</label>
            <input type="number" name="cpu_pct" class="form-control" placeholder="Ej: 1">
        </div>

        <button type="submit" class="btn btn-success">Guardar Plan</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>



    

</div>



<?php 
    include 'footer.php';
?>