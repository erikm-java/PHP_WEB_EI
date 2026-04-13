<?php
$pageTitle = 'Inici';
require_once 'includes/functions.php';
$offers         = getActiveOffers();
$featuredProducts = getFeaturedProducts();
$categories     = getCategories();
require_once 'includes/header.php';
?>

<!-- HERO -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <p class="text-warning fw-semibold mb-2"><i class="bi bi-geo-alt-fill"></i> Lliçà d'Amunt, Vallès Oriental</p>
                <h1 class="fade-in-up">La natura a la porta de casa</h1>
                <p class="lead fade-in-up">Descobreix el nostre catàleg de plantes, llavors i accessoris de jardineria. Qualitat local, preus honestos.</p>
                <div class="d-flex flex-wrap gap-3 mt-4 fade-in-up">
                    <a href="/section.php" class="btn btn-warning btn-lg fw-bold px-4">
                        <i class="bi bi-shop-window me-2"></i>Veure productes
                    </a>
                    <a href="/contact.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-telephone me-2"></i>Contacta'ns
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <div style="font-size:140px; opacity:0.4;">🌱</div>
            </div>
        </div>
    </div>
</section>

<!-- OFERTES VIGENTS -->
<?php if (!empty($offers)): ?>
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="section-title mb-4"><i class="bi bi-tag-fill text-danger me-2"></i>Ofertes vigents</h2>

        <div id="offersCarousel" class="carousel slide offer-carousel" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach ($offers as $i => $offer): ?>
                    <button type="button" data-bs-target="#offersCarousel" data-bs-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active"' : '' ?>></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner rounded-3 shadow">
                <?php foreach ($offers as $i => $offer): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <div class="offer-card d-flex flex-column justify-content-center align-items-center text-center p-5" style="min-height:300px;">
                        <h3 class="fw-bold mb-3"><?= h($offer['name']) ?></h3>
                        <p class="fs-5 opacity-90 mb-4"><?= h($offer['message']) ?></p>
                        <a href="/section.php" class="btn btn-warning btn-lg fw-bold">
                            <i class="bi bi-cart-plus me-2"></i>Aprofita l'oferta
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($offers) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#offersCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#offersCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- PRODUCTES DESTACATS -->
<?php if (!empty($featuredProducts)): ?>
<section class="py-5" style="background: #f0f7f0;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="section-title"><i class="bi bi-star-fill text-warning me-2"></i>Productes destacats</h2>
            <a href="/section.php" class="btn btn-outline-success">Veure tots <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($featuredProducts as $prod): ?>
            <div class="col">
                <div class="card product-card position-relative">
                    <span class="badge bg-warning text-dark badge-featured"><i class="bi bi-star-fill"></i> Destacat</span>
                    <div class="card-img-wrapper">
                        <?= productImage($prod['image'], $prod['name']) ?>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="text-muted small mb-1"><?= h($prod['category_name'] ?? '') ?></p>
                        <h6 class="card-title fw-bold"><?= h($prod['name']) ?></h6>
                        <p class="card-text text-muted small flex-grow-1"><?= h(mb_substr($prod['description'], 0, 80)) ?>...</p>
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                            <span class="price"><?= number_format($prod['price'], 2) ?> €</span>
                            <form action="/cart.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- SECCIONS / CATEGORIES -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="section-title mb-4"><i class="bi bi-grid-fill text-success me-2"></i>Les nostres seccions</h2>
        <div class="row row-cols-2 row-cols-md-3 g-3">
            <?php
            $catEmojis = ['🪴','🌳','🌱','🪚','🏺','🌿'];
            foreach ($categories as $i => $cat):
            ?>
            <div class="col">
                <a href="/section.php?slug=<?= h($cat['slug']) ?>" class="text-decoration-none">
                    <div class="card text-center py-4 h-100 border-0 shadow-sm rounded-3 category-card" style="background:var(--green-pale); transition:all .2s">
                        <div class="display-4"><?= $catEmojis[$i % count($catEmojis)] ?></div>
                        <div class="card-body">
                            <h6 class="fw-bold text-success mb-1"><?= h($cat['name']) ?></h6>
                            <p class="text-muted small mb-0"><?= h(mb_substr($cat['description'], 0, 60)) ?>...</p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PER QUÈ NOSALTRES -->
<section class="py-5" style="background:linear-gradient(135deg,#f1f8f1,#e8f5e9);">
    <div class="container">
        <h2 class="section-title mb-5 text-center" style="margin:0 auto;">Per què Jardins de Lliçà?</h2>
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="fs-1 mb-3">🌿</div>
                <h5 class="fw-bold text-success">Producte local</h5>
                <p class="text-muted small">Treballem amb proveïdors de la comarca per assegurar la frescor.</p>
            </div>
            <div class="col-md-3">
                <div class="fs-1 mb-3">🚚</div>
                <h5 class="fw-bold text-success">Enviament ràpid</h5>
                <p class="text-muted small">Lliurament en 24-48 hores a tot Catalunya.</p>
            </div>
            <div class="col-md-3">
                <div class="fs-1 mb-3">💬</div>
                <h5 class="fw-bold text-success">Consell expert</h5>
                <p class="text-muted small">El nostre equip t'ajudarà a triar la planta perfecta.</p>
            </div>
            <div class="col-md-3">
                <div class="fs-1 mb-3">♻️</div>
                <h5 class="fw-bold text-success">Compromís eco</h5>
                <p class="text-muted small">Envàs 100% reciclable i pràctiques sostenibles.</p>
            </div>
        </div>
    </div>
</section>

<style>
.category-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(46,125,50,.2) !important; }
</style>

<?php require_once 'includes/footer.php'; ?>
