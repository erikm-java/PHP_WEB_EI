<?php
require_once 'includes/functions.php';

// Afegir a cistella
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $q   = $_POST['q'] ?? '';
    if ($pid > 0) addToCart($pid, 1);
    setFlash('success', 'Producte afegit a la cistella!');
    header('Location: search.php?q=' . urlencode($q));
    exit;
}

$query    = trim($_GET['q'] ?? '');
$products = $query ? searchProducts($query) : [];
$pageTitle = $query ? 'Cerca: ' . $query : 'Cerca';

require_once 'includes/header.php';
?>

<div class="container py-4">
    <!-- Barra de cerca -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-7">
            <h2 class="section-title mb-4">
                <i class="bi bi-search text-success me-2"></i>Cerca de productes
            </h2>
            <form action="search.php" method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="form-control form-control-lg"
                       placeholder="Cerca plantes, llavors, eines..."
                       value="<?= h($query) ?>" required>
                <button type="submit" class="btn btn-success btn-lg px-4">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    <?php if ($query): ?>
    <!-- Resultats -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            Resultats per a "<strong><?= h($query) ?></strong>"
        </h4>
        <span class="badge bg-success rounded-pill fs-6"><?= count($products) ?> resultats</span>
    </div>

    <?php if (empty($products)): ?>
        <div class="alert alert-warning d-flex align-items-center gap-3">
            <i class="bi bi-search fs-3"></i>
            <div>
                <strong>Sense resultats.</strong> No hem trobat cap producte que coincideixi amb "<em><?= h($query) ?></em>".
                <br>Prova amb una altra paraula o <a href="/section.php">explora les nostres categories</a>.
            </div>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
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
                            <?= h(mb_substr($prod['description'], 0, 80)) ?>...
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                            <span class="price"><?= number_format($prod['price'], 2) ?> €</span>
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                <input type="hidden" name="q" value="<?= h($query) ?>">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-cart-plus me-1"></i>Afegir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Estat buit: suggeriments -->
    <div class="text-center py-5">
        <div class="display-1 mb-3">🔍</div>
        <h4 class="text-muted">Comença a cercar productes</h4>
        <p class="text-muted">Escriu el nom d'una planta, eina o producte al cercador de dalt.</p>
        <a href="/section.php" class="btn btn-success mt-3">
            <i class="bi bi-grid-fill me-2"></i>Explorar totes les seccions
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
