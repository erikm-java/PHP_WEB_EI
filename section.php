<?php
require_once 'includes/functions.php';

$slug     = isset($_GET['slug']) ? trim($_GET['slug']) : null;
$category = $slug ? getCategoryBySlug($slug) : null;

// Afegir a cistella (POST des d'aquesta pàgina)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $pid = (int)($_POST['product_id'] ?? 0);
    if ($pid > 0) addToCart($pid, 1);
    $redirect = $_SERVER['REQUEST_URI'];
    setFlash('success', 'Producte afegit a la cistella!');
    header('Location: ' . $redirect);
    exit;
}

if ($category) {
    $products  = getProductsByCategory($category['id'], 100);
    $pageTitle = $category['name'];
} else {
    // Mostrar tots si no hi ha categoria
    $products  = getAllProducts();
    $pageTitle = 'Tots els productes';
}

$categories = getCategories();
require_once 'includes/header.php';
?>

<div class="container py-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/index.php">Inici</a></li>
            <li class="breadcrumb-item"><a href="/section.php">Productes</a></li>
            <?php if ($category): ?>
            <li class="breadcrumb-item active"><?= h($category['name']) ?></li>
            <?php endif; ?>
        </ol>
    </nav>

    <div class="row">
        <!-- Sidebar de categories -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-success text-white fw-bold rounded-top-3">
                    <i class="bi bi-grid-fill me-2"></i>Categories
                </div>
                <div class="list-group list-group-flush rounded-bottom-3">
                    <a href="/section.php"
                       class="list-group-item list-group-item-action <?= !$slug ? 'active bg-success text-white border-0' : '' ?>">
                        <i class="bi bi-collection me-2"></i>Tots els productes
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="/section.php?slug=<?= h($cat['slug']) ?>"
                       class="list-group-item list-group-item-action <?= ($slug === $cat['slug']) ? 'active bg-success text-white border-0' : '' ?>">
                        <?= h($cat['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Llista de productes -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title mb-0"><?= h($pageTitle) ?></h2>
                <span class="badge bg-success rounded-pill fs-6"><?= count($products) ?> productes</span>
            </div>

            <?php if (empty($products)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>No hi ha productes en aquesta categoria.
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                    <?php foreach ($products as $prod): ?>
                    <div class="col">
                        <div class="card product-card h-100 position-relative">
                            <?php if ($prod['featured']): ?>
                            <span class="badge bg-warning text-dark badge-featured"><i class="bi bi-star-fill"></i> Destacat</span>
                            <?php endif; ?>
                            <div class="card-img-wrapper">
                                <?= productImage($prod['image'], $prod['name']) ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <p class="text-muted small mb-1"><?= h($prod['category_name'] ?? '') ?></p>
                                <h6 class="card-title fw-bold"><?= h($prod['name']) ?></h6>
                                <p class="card-text text-muted small flex-grow-1">
                                    <?= h(mb_substr($prod['description'], 0, 90)) ?>...
                                </p>
                                <?php if ($prod['stock'] > 0 && $prod['stock'] <= 5): ?>
                                    <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Últimes <?= $prod['stock'] ?> unitats!</small>
                                <?php elseif ($prod['stock'] == 0): ?>
                                    <small class="text-danger"><i class="bi bi-x-circle"></i> Sense estoc</small>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                    <span class="price"><?= number_format($prod['price'], 2) ?> €</span>
                                    <?php if ($prod['stock'] > 0 || $prod['stock'] === null): ?>
                                    <form action="" method="POST">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="bi bi-cart-plus me-1"></i>Afegir
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled>Sense estoc</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
