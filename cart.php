<?php
require_once 'includes/functions.php';

// Processar accions de la cistella
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);

    switch ($action) {
        case 'add':
            if ($productId > 0) {
                addToCart($productId, 1);
                setFlash('success', 'Producte afegit a la cistella!');
            }
            break;
        case 'update':
            $qty = (int)($_POST['quantity'] ?? 0);
            if ($productId > 0) updateCartItem($productId, $qty);
            break;
        case 'remove':
            if ($productId > 0) {
                removeFromCart($productId);
                setFlash('info', 'Producte eliminat de la cistella.');
            }
            break;
        case 'clear':
            clearCart();
            setFlash('info', 'Cistella buidada.');
            break;
    }
    header('Location: cart.php');
    exit;
}

$cartItems = getCartDetails();
$total     = getCartTotal();
$pageTitle = 'Cistella de la compra';
require_once 'includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/index.php">Inici</a></li>
            <li class="breadcrumb-item active">Cistella</li>
        </ol>
    </nav>

    <h2 class="section-title mb-4"><i class="bi bi-cart-fill text-success me-2"></i>Cistella de la compra</h2>

    <?php if (empty($cartItems)): ?>
    <!-- Cistella buida -->
    <div class="text-center py-5">
        <div class="display-1 mb-3">🛒</div>
        <h4 class="text-muted">La teva cistella és buida</h4>
        <p class="text-muted">Afegeix productes per a continuar amb la compra.</p>
        <a href="/section.php" class="btn btn-success btn-lg mt-3">
            <i class="bi bi-shop-window me-2"></i>Veure productes
        </a>
    </div>

    <?php else: ?>
    <div class="row g-4">
        <!-- Llista d'articles -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th colspan="2">Producte</th>
                                    <th class="text-center">Preu unit.</th>
                                    <th class="text-center">Quantitat</th>
                                    <th class="text-end">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): $p = $item['product']; ?>
                                <tr>
                                    <td style="width:80px;">
                                        <div style="width:70px;height:70px;overflow:hidden;border-radius:8px;background:#e8f5e9;">
                                            <?= productImage($p['image'], $p['name']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= h($p['name']) ?></strong>
                                        <br><small class="text-muted"><?= h($p['category_name'] ?? '') ?></small>
                                    </td>
                                    <td class="text-center"><?= number_format($p['price'], 2) ?> €</td>
                                    <td class="text-center" style="width:150px;">
                                        <form action="" method="POST" class="d-flex align-items-center justify-content-center gap-1">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <button type="button" onclick="changeQty(this,-1)" class="btn btn-outline-secondary btn-sm">−</button>
                                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>"
                                                   min="1" max="99" class="form-control form-control-sm text-center qty-input"
                                                   style="width:55px;" onchange="this.form.submit()">
                                            <button type="button" onclick="changeQty(this,1)" class="btn btn-outline-secondary btn-sm">+</button>
                                        </form>
                                    </td>
                                    <td class="text-end fw-bold"><?= number_format($item['subtotal'], 2) ?> €</td>
                                    <td>
                                        <form action="" method="POST">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between flex-wrap gap-2 py-3">
                    <a href="/section.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Continuar comprant
                    </a>
                    <form action="" method="POST" onsubmit="return confirm('Vols buidar la cistella?')">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-trash3 me-2"></i>Buidar cistella
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Resum de la comanda -->
        <div class="col-lg-4">
            <div class="cart-summary">
                <h5 class="fw-bold mb-4"><i class="bi bi-receipt me-2"></i>Resum de la comanda</h5>
                <?php foreach ($cartItems as $item): ?>
                <div class="d-flex justify-content-between mb-2 small">
                    <span><?= h($item['product']['name']) ?> × <?= $item['quantity'] ?></span>
                    <span><?= number_format($item['subtotal'], 2) ?> €</span>
                </div>
                <?php endforeach; ?>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span><?= number_format($total, 2) ?> €</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Enviament</span>
                    <span class="text-success fw-semibold">
                        <?= $total >= 50 ? '<i class="bi bi-check-circle"></i> Gratis' : number_format(4.99, 2) . ' €' ?>
                    </span>
                </div>
                <?php if ($total < 50): ?>
                <div class="alert alert-info p-2 small">
                    <i class="bi bi-truck me-1"></i>Afegeix <?= number_format(50 - $total, 2) ?> € més per a enviament gratuït!
                </div>
                <?php endif; ?>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold fs-5">TOTAL</span>
                    <span class="cart-total"><?= number_format($total + ($total < 50 ? 4.99 : 0), 2) ?> €</span>
                </div>
                <a href="/checkout.php" class="btn btn-success btn-lg w-100 fw-bold">
                    <i class="bi bi-credit-card-fill me-2"></i>Finalitzar compra
                </a>
                <p class="text-muted small text-center mt-3">
                    <i class="bi bi-shield-check me-1"></i>Pagament 100% segur
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function changeQty(btn, delta) {
    const input = btn.parentElement.querySelector('.qty-input');
    const newVal = Math.max(1, parseInt(input.value) + delta);
    input.value = newVal;
    input.form.submit();
}
</script>

<?php require_once 'includes/footer.php'; ?>
