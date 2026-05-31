<?php 
/**
 * Página principal (Landing Page).
 * Muestra información comercial básica.
 */
    include 'cabecera.php';
?>

<div class="container py-4 fade-in">
    <div class="home-principal p-5 mb-5 shadow-lg">
        <div class="row w-100">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold text-white home-title mb-4">
                    Eleva tu servidor a la <br> <span class="texto2">Máxima Potencia</span>
                </h1>
                <p class="lead home-subtitle mb-4 fs-4 yt-text-max-600">
                    Infraestructura Zero-Touch para Minecraft. Alto rendimiento, protección DDoS y monitorización en tiempo real.
                </p>
                <div class="d-flex gap-3 position-relative z-3">
                    <a href="planes.php" class="btn btn-yt-primary btn-lg">Explorar Planes</a>
                    <a href="sobre_nosotros.php" class="btn btn-yt-outline btn-lg">Saber Más</a>
                </div>
            </div>
        </div>
    </div>

    <section class="home-tarjetas py-5">
        <div class="container text-center">
            <h2 class="display-5 fw-bold home-title mb-5">¿Por qué elegir Ytron Hosting?</h2>
            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="card-yt p-4 h-100 text-center">
                        <div class="mb-4">
                            <i class="bi bi-lightning-charge fs-1 text-yt-teal"></i>
                        </div>
                        <h4 class="fw-bold mb-3 text-yt-cyan">Rendimiento Extremo</h4>
                        <p class="text-light mb-0">Nodos impulsados por procesadores AMD Ryzen de última generación y almacenamiento NVMe SSD en RAID 10.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-yt p-4 h-100 text-center">
                        <div class="mb-4">
                            <i class="bi bi-shield-check fs-1 text-yt-cyan"></i>
                        </div>
                        <h4 class="fw-bold mb-3 text-yt-teal">Protección DDoS</h4>
                        <p class="text-light mb-0">Nuestra red está protegida por hardware perimetral de nivel empresarial. Tu servidor nunca se desconectará.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-yt p-4 h-100 text-center">
                        <div class="mb-4">
                            <i class="bi bi-robot fs-1 text-white"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Zero-Touch</h4>
                        <p class="text-light mb-0">Despliegue automatizado. Compra tu plan y tu servidor de Minecraft estará en línea en menos de 60 segundos.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="home-comunidad mt-5 mb-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 order-2 order-lg-1">
                <h2 class="fw-bold mb-4">Únete a nuestra comunidad</h2>
                <p class="text-light fs-5 mb-4">Más de 5,000 administradores de servidores ya confían en Ytron. Soporte técnico 24/7 y una comunidad activa en Discord.</p>
                <ul class="comunidad-ul">
                    <li class="comunidad-li mb-3">
                        <i class="bi bi-check2-circle me-3 yt-check-icon"></i> Soporte Técnico Especializado
                    </li>
                    <li class="comunidad-li mb-3">
                        <i class="bi bi-check2-circle me-3 yt-check-icon"></i> Tutoriales y Documentación
                    </li>
                    <li class="comunidad-li">
                        <i class="bi bi-check2-circle me-3 yt-check-icon"></i> Comunidad de Discord Activa
                    </li>
                </ul>
            </div>
            <div class="col-lg-6 order-1 order-lg-2">
                <img src="imagenes/mine-comunidad2.png" onerror="this.src='https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?q=80&w=600'" class="comunidad-imagen shadow-lg" alt="Comunidad Ytron Hosting">
            </div>
        </div>
    </div>
</div>

<?php 
    include 'footer.php'; 
?>