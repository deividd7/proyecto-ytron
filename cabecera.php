<?php
    session_start();

?>

<!-- Esta página es de libre acceso para usuarios no logueados -->


<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Ytron Hosting</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="David Pintado">    
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> 
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>  <!-- Permite que funcione el menú desplegable del admin -->
        <link rel="stylesheet" type="text/css" href="css/estilos.css?v=1.7"> <!-- Esto fuerza la actualización del navegador para actualizar los estilos y así evitar problemas con la caché (1.1, 1.2, 1.3 ...) -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Librería externa que transforma los mensajes del navegador el ventanas emergentes -->
    </head>

    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 shadow-sm">
            <div class="container-fluid">

                <a class="navbar-brand" href="home.php">
                    <img src="imagenes/Logo.png" class="cabecera-logo" alt="Ytron Logo">
                </a>      
                
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3 gap-3"> 
                    <li class="nav-item">
                        <a class="nav-link nav-custom" href="planes.php">Planes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-custom" href="sobre_nosotros.php">Sobre nosotros</a>
                    </li>
                </ul>  


                <div class="collapse navbar-collapse" id="navbarNav">  <!-- Si el usuario no inicia sesion en la web, muestra los botones para que lo haga y varias pestañas que si puede consultar. Si lo está, le da la bienvenida y muestra el boton de perfil y cerrar sesión. Y si inicia el admin puede ver el panel de gestión -->
                    <div class="d-flex align-items-center ms-auto">
                        <?php if (isset($_SESSION['usuario'])): ?>
                            <span class="me-2 text-secondary">
                                Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong>
                            </span>

                            <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1): ?>
                                <div class="dropdown d-inline-block me-2">
                                    <button class="btn btn-warning btn-sm dropdown-toggle shadow-sm" type="button" id="dropdownAdmin" data-bs-toggle="dropdown" aria-expanded="false">
                                        Panel de Gestión
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownAdmin">
                                        
                                        <li><a class="dropdown-item" href="index.php"><i class="bi bi-list-stars"></i> Configurar Planes</a></li>
                                        <li><a class="dropdown-item" href="lista_planes_usuarios.php"><i class="bi bi-people"></i> Planes Contratados</a></li>
                                    
                                    </ul>
                                </div>

                                <?php else: ?>
                                    <a href="perfil.php" class="btn btn-outline-secondary btn-sm me-2">
                                        Perfil de <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                                    </a>
                                <?php endif; ?>

                            <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>

                        <?php else: ?>
                            <a href="login.php?from=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-outline-primary btn-sm me-2">Iniciar Sesión</a>  <!-- Guarda en una variable (from) el lugar en el que se encuentra el usuario y lo redirige a login -->
                            <a href="registro.php?from=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary btn-sm">Regístrate</a>   <!-- Guarda en una variable (from) el lugar en el que se encuentra el usuario y lo redirige a login -->
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container"> <!-- Necesario para el tema de cuadrar la visualización de la pagina con css -->
<!-- Eliminamos las etiquetas de cierre body y html porque ya se encuentran en el footer-->