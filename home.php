<?php 
    include 'cabecera.php'; 
?>

<!-- Eliminamos las etiquetas de apertura y cierre de body y html porque ya se encuentran en la cabecera y el footer-->

<!-- Esta página es de libre acceso para usuarios no logueados -->



<div class="contenedorHome">
    <?php 
        // Comprobamos si el usuario fue redirigido aquí por falta de permisos (aqui se hará la prueba para comprobar los permisos de los usuarios)
        if (isset($_GET['error']) && $_GET['error'] == 'acceso_denegado'): 
    ?>

    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <strong>¡Acceso denegado!</strong> No tienes permisos de administrador para entrar en esa sección.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <?php endif; ?>




    <section class="home-principal bg-dark text-white text-center py-5 shadow-lg">
        <div class="container py-5"> 
            <h1 class="display-4 fw-bold home-title">Domina tu propio mundo con <span class="texto2">Ytron Hosting</span></h1>
            <p class="lead mb-4 home-subtitle">Servidores de alto rendimiento para Minecraft con protección Anti-DDoS y latencia mínima.</p>
            <div class="cta-container">
                <a href="planes.php" class="btn btn-primary btn-lg px-5 shadow home-boton">¡Empieza ahora!</a>
            </div>
        </div>
    </section>


    
    <section class="home-tarjetas py-5">
        <div class="container text-center">
            <h2 class="display-5 fw-bold home-title">¿Por qué elegir Ytron Hosting?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card home-tarjeta h-100 border-0 shadow-sm p-4">
                        <h4>Rendimiento SSD NVMe</h4>
                        <p>Tus servidores cargarán en segundos. Se acabaron los tirones (lag) mientras exploras el mapa.</p>
                        <img src="imagenes/alto-rendimiento.png" class="home-imagen" alt="Rendimiento SSD">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card home-tarjeta h-100 border-0 shadow-sm p-4">
                        <h4>Protección Anti-DDoS</h4>
                        <p>Seguridad de nivel empresarial para mantener tu servidor online 24/7 frente a ataques externos.</p>
                        <img src="imagenes/proteccionddos.png" class="home-imagen" alt="Proteccion DDOS">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card home-tarjeta h-100 border-0 shadow-sm p-4">
                        <h4>Soporte Técnico</h4>
                        <p>Nuestro equipo de expertos está listo para ayudarte con cualquier configuración o plugin.</p>
                        <img src="imagenes/soporte-tecnico.png" class="home-imagen" alt="Rendimiento SSD">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-comunidad py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3 class="display-6 fw-bold">Crea tu comunidad en Minecraft</h3>
                    <p class="comunidad-texto">Minecraft es mucho más que un juego de cubos; es un lienzo infinito...<br>
                    Nuestros servidores soportan tanto <strong>Java Edition</strong> como <strong>Bedrock</strong>.</p>
                    <ul class="comunidad-ul">
                        <li class="comunidad-li h-100 border-0 shadow-sm p-4 mb-3 rounded">
                            <strong>Compatibilidad Total:</strong> Soporte nativo para Java Edition y Bedrock (Geyser).
                        </li>
                        <li class="comunidad-li h-100 border-0 shadow-sm p-4 mb-3 rounded">
                            <strong>Panel Intuitivo:</strong> Gestiona tus archivos, consola y backups con un solo clic.
                        </li>
                        <li class="comunidad-li h-100 border-0 shadow-sm p-4 mb-3 rounded">
                            <strong>Escalabilidad:</strong> ¿Tu comunidad creció? Sube de plan al instante sin perder tus datos.
                        </li>
                    </ul>
                </div>

                <div class="col-lg-6 text-center">
                    <img src="imagenes/mine-comunidad.png" class="comunidad-imagen" alt="Minecraft foto">
                </div>
            </div>
        </div>
    </section>

</div>



<?php 
    include 'footer.php'; 
?>