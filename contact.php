<?php
$pageTitle = 'Contacte';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/PHP_WEB_EI/index.php">Inici</a></li>
            <li class="breadcrumb-item active">Contacte</li>
        </ol>
    </nav>

    <h2 class="section-title mb-5"><i class="bi bi-telephone-fill text-success me-2"></i>Contacta amb nosaltres</h2>

    <div class="row g-4">
        <!-- Informació de contacte -->
        <div class="col-lg-5">
            <div class="contact-card shadow-sm mb-4">
                <h4 class="fw-bold text-success mb-4">Jardins de Lliçà</h4>

                <div class="contact-info-item">
                    <div class="contact-info-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Adreça</h6>
                        <p class="text-muted mb-0">
                            Carrer Major, 15<br>
                            08186 Lliçà d'Amunt<br>
                            Vallès Oriental, Catalunya
                        </p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Telèfon</h6>
                        <p class="text-muted mb-0">
                            <a href="tel:938428000" class="text-success fw-semibold text-decoration-none">938 428 000</a>
                        </p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Correu electrònic</h6>
                        <p class="text-muted mb-0">
                            <a href="mailto:info@jardinsllica.cat" class="text-success fw-semibold text-decoration-none">
                                info@jardinsllica.cat
                            </a>
                        </p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Horari</h6>
                        <p class="text-muted mb-0">
                            Dilluns – Divendres: 9:00 – 19:00<br>
                            Dissabte: 9:00 – 14:00<br>
                            Diumenge: Tancat
                        </p>
                    </div>
                </div>
            </div>

            <!-- Xarxes socials -->
            <div class="contact-card shadow-sm">
                <h5 class="fw-bold mb-3">Segueix-nos</h5>
                <div class="d-flex gap-3">
                    <a href="#" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-facebook me-1"></i> Facebook
                    </a>
                    <a href="#" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-instagram me-1"></i> Instagram
                    </a>
                    <a href="#" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-twitter-x me-1"></i> X
                    </a>
                </div>
            </div>
        </div>

        <!-- Mapa -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="bi bi-map-fill me-2"></i>On som (INS Lliçà d'Amunt)
                </div>
                <!-- Google Maps iframe - Ubicació de l'INS Lliçà d'Amunt -->
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2981.2547362765456!2d2.2396849!3d41.6143742!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12a4bd7a03b3e6e5%3A0x4c7d2c2d2e3f1b0a!2sINS%20Llic%C3%A0%20d'Amunt!5e0!3m2!1sca!2ses!4v1700000000000"
                    width="100%"
                    height="400"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Ubicació de Jardins de Lliçà">
                </iframe>
            </div>

            <!-- Instruccions per arribar -->
            <div class="card border-0 shadow-sm rounded-3 mt-4 p-4">
                <h5 class="fw-bold text-success mb-3"><i class="bi bi-signpost-2 me-2"></i>Com arribar-hi</h5>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <h6 class="fw-semibold"><i class="bi bi-train-front me-2 text-success"></i>Transport públic</h6>
                        <p class="text-muted small mb-0">
                            Línia R2 Nord - Estació de Lliçà d'Amunt<br>
                            Autobús: L-60 des de Granollers
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <h6 class="fw-semibold"><i class="bi bi-car-front me-2 text-success"></i>En cotxe</h6>
                        <p class="text-muted small mb-0">
                            Autopista C-33 - Sortida Lliçà d'Amunt<br>
                            Aparcament gratuït disponible
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
