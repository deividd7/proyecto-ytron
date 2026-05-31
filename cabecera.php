<?php
/**
 * Cabecera principal de la plataforma.
 * Incluye metadatos, navegación, diseño y enrutamiento visual.
 */
    require_once __DIR__ . '/sesion_db.php';
    session_start();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Ytron Hosting</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="David Pintado y Óscar Quirant">
        <meta name="description" content="Ytron Hosting — Servidores de Minecraft de alto rendimiento con despliegue automatizado.">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" type="text/css" href="css/estilos.css?v=2.0">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>

    <body>
        <nav class="navbar navbar-expand-lg navbar-ytron mb-4 sticky-top">
            <div class="container-fluid">

                <a class="navbar-brand" href="home.php">
                    <img src="imagenes/Logo.png" alt="Ytron Logo">
                </a>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="bi bi-list text-white fs-4"></i>
                </button>

                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3 gap-1">
                    <li class="nav-item">
                        <a class="nav-link" href="planes.php"><i class="bi bi-box-seam me-1"></i>Planes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sobre_nosotros.php"><i class="bi bi-info-circle me-1"></i>Sobre nosotros</a>
                    </li>
                </ul>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="d-flex align-items-center ms-auto gap-2">
                        <?php if (isset($_SESSION['usuario'])): ?>

                            <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1): ?>
                                <!-- panel admin expandido -->
                                <div class="dropdown d-inline-block me-1">
                                    <button class="btn btn-yt-admin btn-sm dropdown-toggle" type="button" id="dropdownAdmin" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-shield-lock me-1"></i>Admin
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-yt" aria-labelledby="dropdownAdmin">
                                        <li><a class="dropdown-item" href="index.php"><i class="bi bi-gear"></i> Gestión de Planes</a></li>
                                        <li><a class="dropdown-item" href="lista_planes_usuarios.php"><i class="bi bi-receipt"></i> Planes Contratados</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="admin_usuarios.php"><i class="bi bi-people"></i> Gestión de Usuarios</a></li>
                                        <li><a class="dropdown-item" href="admin_servidores.php"><i class="bi bi-hdd-rack"></i> Monitor de Servidores</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="diagnostico.php"><i class="bi bi-activity"></i> Diagnóstico del Sistema</a></li>
                                    </ul>
                                </div>

                                <!-- perfil para admin -->
                                <a href="perfil.php" class="btn btn-yt-outline btn-sm">
                                    <i class="bi bi-person me-1"></i>Perfil
                                </a>
                            <?php else: ?>
                                <a href="perfil.php" class="btn btn-yt-outline btn-sm">
                                    <i class="bi bi-person me-1"></i>Perfil
                                </a>
                            <?php endif; ?>

                            <a href="logout.php" class="btn btn-yt-danger btn-sm">
                                <i class="bi bi-box-arrow-right me-1"></i>Salir
                            </a>

                        <?php else: ?>
                            <a href="login.php?from=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-yt-outline btn-sm">Iniciar Sesión</a>
                            <a href="registro.php" class="btn btn-yt-primary btn-sm">Regístrate</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- contenido principal -->
        <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1): ?>
        <script>
            // limpieza silenciosa segundo plano
            document.addEventListener('DOMContentLoaded', () => {
                fetch('limpieza_automatica.php').catch(() => {});
            });
        </script>
        <?php endif; ?>