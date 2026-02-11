<?php include 'cabecera.php'; ?>

<div class="container mt-5">
    <?php 
    // Comprobamos si el usuario fue redirigido por falta de permisos
    if (isset($_GET['error']) && $_GET['error'] == 'acceso_denegado'): 
    ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <strong>¡Acceso denegado!</strong> No tienes permisos de administrador para entrar en esa sección.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="jumbotron text-center p-5 bg-light border rounded">
        <h1 class="display-4">Panel de Usuario</h1>
        <p class="lead">Bienvenido a tu área personal de Ytron Hosting.</p>
        <hr class="my-4">
        <p>Aquí podrás ver tus planes contratados próximamente.</p>
    </div>
</div>

<?php include 'footer.php'; ?>