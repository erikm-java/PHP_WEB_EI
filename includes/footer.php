<!-- PEU DE PÀGINA -->
<footer class="bg-dark text-light mt-5 pt-5 pb-3">
    <div class="container">
        <div class="row g-4">
            <!-- Info botiga -->
            <div class="col-md-4">
                <h5 class="text-success fw-bold"><span>🌿</span> Jardins de Lliçà</h5>
                <p class="text-muted small">La teva botiga de confiança de plantes i jardineria a Lliçà d'Amunt. Qualitat i passió per les plantes des de 2010.</p>
                <div class="d-flex gap-3 mt-2">
                    <a href="#" class="text-muted"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" class="text-muted"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" class="text-muted"><i class="bi bi-twitter-x fs-5"></i></a>
                </div>
            </div>
            <!-- Navegació ràpida -->
            <div class="col-md-3">
                <h6 class="text-uppercase text-success fw-bold mb-3">Navegació</h6>
                <ul class="list-unstyled">
                    <li><a href="/index.php" class="footer-link">Inici</a></li>
                    <li><a href="/section.php" class="footer-link">Productes</a></li>
                    <li><a href="/contact.php" class="footer-link">Contacte</a></li>
                    <li><a href="/login.php" class="footer-link">Inicia sessió</a></li>
                    <li><a href="/register.php" class="footer-link">Registra't</a></li>
                </ul>
            </div>
            <!-- Legal -->
            <div class="col-md-3">
                <h6 class="text-uppercase text-success fw-bold mb-3">Legal</h6>
                <ul class="list-unstyled">
                    <li><a href="/cookies-policy.php" class="footer-link">Política de cookies</a></li>
                    <li><a href="/cookies-policy.php#proteccio-dades" class="footer-link">Protecció de dades</a></li>
                </ul>
            </div>
            <!-- Contacte -->
            <div class="col-md-2">
                <h6 class="text-uppercase text-success fw-bold mb-3">Contacte</h6>
                <ul class="list-unstyled text-muted small">
                    <li><i class="bi bi-geo-alt-fill me-1"></i>C/ Major, 15<br>Lliçà d'Amunt</li>
                    <li class="mt-2"><i class="bi bi-telephone-fill me-1"></i>938 428 000</li>
                    <li class="mt-2"><i class="bi bi-envelope-fill me-1"></i>info@jardinsllica.cat</li>
                </ul>
            </div>
        </div>

        <hr class="border-secondary mt-4">

        <!-- Copyright i autors -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0 text-light small">
                    <strong>Jardins de Lliçà</strong> © 2026. Tots els drets reservats.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 text-light small">
                    Desenvolupat per <strong>Erik Munuera i Isaac Dominguez</strong>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Cookie Consent Banner -->
<?php if (!isset($_COOKIE['cookies_accepted'])): ?>
<div id="cookieBanner" class="cookie-banner bg-dark text-light p-3 shadow-lg">
    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <div>
            <i class="bi bi-cookie text-warning me-2"></i>
            <strong>Política de cookies:</strong> Fem servir cookies per a millorar la teva experiència i gestionar la cistella de la compra.
            <a href="/cookies-policy.php" class="text-warning">Llegir més</a>.
        </div>
        <div class="d-flex gap-2">
            <button onclick="acceptCookies()" class="btn btn-success btn-sm">Acceptar</button>
            <a href="/cookies-policy.php" class="btn btn-outline-light btn-sm">Més informació</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function acceptCookies() {
    document.cookie = "cookies_accepted=1; path=/; max-age=" + (365*24*60*60);
    document.getElementById('cookieBanner').style.display = 'none';
}
</script>
</body>
</html>
