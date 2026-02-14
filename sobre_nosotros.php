<?php 
    include 'cabecera.php'; 
?>

<!-- Eliminamos las etiquetas de apertura y cierre de body y html porque ya se encuentran en la cabecera y el footer-->

<!-- Esta página es de libre acceso para usuarios no logueados -->


<div class="container py-5">

    <section class="nosotros-principal text-white text-center py-5 shadow-lg">
        <div class="nosotros-principal-contenedor py-5"> 
            <h1 class="display-4 fw-bold">Nuestra Misión</h1>
            <p class="nosotros-principal-texto mx-auto">
                En <span class="texto2">Ytron Hosting</span>, no solo alquilamos servidores; creamos el hogar 
                digital para tu comunidad. Nacimos de la pasión por Minecraft y el compromiso de ofrecer 
                tecnología de vanguardia sin complicaciones.
            </p>
        </div>
    </section>

    <div class="nosotros-comunidad row align-items-center mb-5">
        <div class="col-lg-6">
            <h2 class="fw-bold mb-4">¿Quiénes somos?</h2>
            <p>Comenzamos como un pequeño grupo de jugadores frustrados por el lag y el mal soporte técnico. Decidimos cambiar eso.</p>
            <p>Hoy, gestionamos infraestructuras de alto rendimiento con <strong>discos NVMe</strong> y protección avanzada para que tú solo tengas que preocuparte de construir y explorar.</p>
            
            <ul class="nosotros-comunidad-ul">

                <li class="nosotros-comunidad-li h-100 border-0 shadow-sm p-4 mb-3">
                    <span>Infraestructura propia y optimizada.</span>
                </li>

                <li class="nosotros-comunidad-li h-100 border-0 shadow-sm p-4 mb-3">
                    <span>Soporte real por y para expertos.</span>
                </li>

            </ul>
        </div>

        <div class="col-lg-6 text-center">
            <img src="imagenes/grupo.png" class="img-fluid rounded-4 shadow" alt="Nuestro equipo">
        </div>

    </div>

    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="card nosotros-tarjeta h-100 border-0 p-4">
                <h3 class="display-6 fw-bold texto2">99.9%</h3>
                <p>Uptime garantizado para que tu mundo nunca se detenga.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card nosotros-tarjeta h-100 border-0 p-4">
                <h3 class="display-6 fw-bold texto2">+500</h3>
                <p>Servidores activos confiando en nuestra tecnología.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card nosotros-tarjeta h-100 border-0 p-4">
                <h3 class="display-6 fw-bold texto2">24/7</h3>
                <p>Soporte técnico real siempre disponible para ti.</p>
            </div>
        </div>
    </div>
</div>


<?php 
    include 'footer.php'; 
?>