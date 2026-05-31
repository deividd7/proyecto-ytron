<?php 
/**
 * Vista para editar un plan.
 * Solo accesible para administradores.
 */

    // solo admin edita planes    include 'cabecera.php'; 
    $id = $_GET['id'] ?? '';

    

    // valida sesion de admin
    // verifica usuario logueado
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    // redirige si no admin
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        header("Location: home.php?error=acceso_denegado");
        exit();
    }



    if (empty($id)) {
        header("Location: index.php");
        exit();
    }

    // conecta base de datos
        
    require_once __DIR__ . '/db_conexion.php';
    $conexion = getDbConnection();

    
    // obtiene datos actuales plan
    $sql = "SELECT * FROM plan WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $plan = mysqli_fetch_assoc($resultado);

    if (!$plan) {
        header("Location: index.php");
        exit();
    }
?>



<div class="container mt-4">
    <h2>Editar Plan: <?php echo htmlspecialchars($plan['nombre']); ?></h2>
    
    <form action="actualizar_plan.php" method="POST" class="yt-form-container">
        <input type="hidden" name="id" value="<?php echo $plan['id']; ?>">

        <div class="mb-3">
            <label class="form-label">Nombre del Plan:</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($plan['nombre']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Precio Mensual (€):</label>
            <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $plan['precio']; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">RAM (MB):</label>
            <input type="number" name="ram_mb" class="form-control" value="<?php echo $plan['ram_mb']; ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">CPU Limit (%):</label>
            <input type="number" name="cpu_pct" class="form-control" value="<?php echo $plan['cpu_pct']; ?>">
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
    
        


<?php 
    include 'footer.php'; 
?>