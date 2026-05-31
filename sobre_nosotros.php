<?php 
/**
 * Página estática "Sobre Nosotros".
 * Describe la misión y equipo.
 */
    include 'cabecera.php'; 
?>

<!-- html heredado de cabecera -->

<!-- pagina de acceso libre -->


<div class="container py-5 fade-in">

    <!-- bloque de presentacion -->
    <section class="nosotros-principal card-yt-glass text-center py-5 mb-5 position-relative overflow-hidden yt-nosotros-border">
        <!-- fondo brillante sutil -->
        <div class="position-absolute yt-glow-1"></div>
        <div class="position-absolute yt-glow-2"></div>
        
        <div class="nosotros-principal-contenedor py-5 position-relative z-index-1"> 
            <h1 class="display-4 fw-bold mb-3 yt-letter-spacing-tight">Nuestra <span class="texto2">Misión</span></h1>
            <p class="nosotros-principal-texto mx-auto text-light text-opacity-75 fs-5 px-3 yt-max-700-lh-16">
                En Ytron Hosting no solo alquilamos servidores; creamos el <span class="text-white fw-semibold">hogar digital</span> para tu comunidad. Nacimos de la pasión por Minecraft y el compromiso de ofrecer tecnología de vanguardia sin complicaciones.
            </p>
        </div>
    </section>

    <!-- bloque de contenido -->
    <div class="nosotros-comunidad row align-items-center mb-5 g-5">
        <div class="col-lg-6">
            <h2 class="fw-bold mb-4 yt-fs-22">¿Quiénes <span class="text-yt-cyan">somos?</span></h2>
            <p class="text-light text-opacity-75 mb-4 yt-lh-17-fs-105">
                Comenzamos como un pequeño grupo de jugadores frustrados por el lag constante y el soporte técnico automatizado. Decidimos construir la infraestructura que nosotros mismos deseábamos usar.
            </p>
            <p class="text-light text-opacity-75 mb-4 yt-lh-17-fs-105">
                Hoy en día, orquestamos clústeres de alto rendimiento propulsados por <strong class="text-white">discos NVMe puramente dedicados</strong> y un perímetro de defensa avanzado, para que tú solo tengas que preocuparte de construir y liderar tu comunidad.
            </p>
            
            <div class="d-flex flex-column gap-3 mt-4">
                <div class="card-yt p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center yt-icon-box-cyan">
                        <i class="bi bi-cpu-fill fs-5 text-yt-cyan"></i>
                    </div>
                    <span class="text-light fw-medium">Infraestructura propia y optimizada al milisegundo.</span>
                </div>

                <div class="card-yt p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center yt-icon-box-teal">
                        <i class="bi bi-headset fs-5 text-yt-teal"></i>
                    </div>
                    <span class="text-light fw-medium">Soporte técnico real operado por ingenieros expertos.</span>
                </div>
            </div>
        </div>

        <div class="col-lg-6 text-center position-relative">
            <div class="position-absolute w-100 h-100 yt-img-glow"></div>
            <img src="imagenes/grupo.jpg" class="img-fluid rounded-4 shadow-lg position-relative yt-img-filter" alt="Nuestro equipo de infraestructura">
        </div>
    </div>

    <!-- bloque de estadisticas -->
    <div class="row g-4 text-center mt-3">
        <div class="col-md-4">
            <div class="card-yt h-100 p-5 position-relative overflow-hidden">
                <i class="bi bi-activity position-absolute yt-bg-icon-cyan"></i>
                <h3 class="display-5 fw-bold mb-3 text-yt-cyan">99.9%</h3>
                <p class="text-light text-opacity-75 mb-0 fw-medium">Uptime garantizado. Tu mundo, siempre en línea.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-yt h-100 p-5 position-relative overflow-hidden">
                <i class="bi bi-server position-absolute yt-bg-icon-teal"></i>
                <h3 class="display-5 fw-bold mb-3 text-yt-teal">+500</h3>
                <p class="text-light text-opacity-75 mb-0 fw-medium">Servidores activos orquestados con nuestra tecnología.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-yt h-100 p-5 position-relative overflow-hidden">
                <i class="bi bi-shield-check position-absolute yt-bg-icon-cyan"></i>
                <h3 class="display-5 fw-bold mb-3 texto2">24/7</h3>
                <p class="text-light text-opacity-75 mb-0 fw-medium">Monitorización constante y soporte técnico especializado.</p>
            </div>
        </div>
    </div>
</div>


<?php 
    include 'footer.php'; 
?>