<?php
require_once 'includes/functions.php';
require_once 'includes/email.php';

$cartItems = getCartDetails();
if (empty($cartItems)) {
    setFlash('warning', 'La cistella és buida.');
    header('Location: cart.php');
    exit;
}

$total     = getCartTotal();
$shipping  = $total < 50 ? 4.99 : 0;
$totalFinal = $total + $shipping;

// Dades de l'usuari si ha iniciat sessió
$user = null;
if (isLoggedIn()) {
    $user = getUserById((int)$_SESSION['user_id']);
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recollir dades
    $data = [
        'full_name'   => trim($_POST['full_name'] ?? ''),
        'dni'         => strtoupper(trim($_POST['dni'] ?? '')),
        'phone'       => trim($_POST['phone'] ?? ''),
        'address'     => trim($_POST['address'] ?? ''),
        'city'        => trim($_POST['city'] ?? ''),
        'postal_code' => trim($_POST['postal_code'] ?? ''),
        'billing'     => (bool)($_POST['has_billing'] ?? false),
        'billing_cif'     => trim($_POST['billing_cif'] ?? ''),
        'billing_name'    => trim($_POST['billing_name'] ?? ''),
        'billing_address' => trim($_POST['billing_address'] ?? ''),
        'billing_city'    => trim($_POST['billing_city'] ?? ''),
        'billing_postal'  => trim($_POST['billing_postal'] ?? ''),
    ];

    // Validacions d'enviament
    if (empty($data['full_name'])) $errors[] = 'El nom complet és obligatori.';
    if (!validateDNI($data['dni']))  $errors[] = 'El DNI no és vàlid.';
    if (!preg_match('/^\d{9}$/', $data['phone'])) $errors[] = 'El telèfon ha de tenir exactament 9 dígits.';
    if (empty($data['address']))     $errors[] = 'La direcció és obligatòria.';
    if (empty($data['city']))        $errors[] = 'La població és obligatòria.';
    if (!preg_match('/^\d{5}$/', $data['postal_code'])) $errors[] = 'El codi postal ha de tenir 5 dígits.';

    // Validació facturació (si s'ha marcat)
    if ($data['billing']) {
        if (empty($data['billing_cif']))     $errors[] = 'El CIF de facturació és obligatori.';
        if (empty($data['billing_name']))    $errors[] = 'La raó social és obligatòria.';
        if (empty($data['billing_address'])) $errors[] = 'La direcció de facturació és obligatòria.';
        if (empty($data['billing_city']))    $errors[] = 'La població de facturació és obligatòria.';
        if (!preg_match('/^\d{5}$/', $data['billing_postal'])) $errors[] = 'El codi postal de facturació ha de tenir 5 dígits.';
    }

    if (empty($errors)) {
        $pdo = getDB();
        // Crear comanda
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, total, shipping_name, shipping_dni, shipping_phone,
                shipping_address, shipping_city, shipping_postal_code,
                billing_cif, billing_company_name, billing_address, billing_city, billing_postal_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user ? $user['id'] : null,
            $totalFinal,
            $data['full_name'], $data['dni'], $data['phone'],
            $data['address'], $data['city'], $data['postal_code'],
            $data['billing'] ? $data['billing_cif'] : null,
            $data['billing'] ? $data['billing_name'] : null,
            $data['billing'] ? $data['billing_address'] : null,
            $data['billing'] ? $data['billing_city'] : null,
            $data['billing'] ? $data['billing_postal'] : null,
        ]);
        $orderId = (int)$pdo->lastInsertId();

        // Inserir línies de comanda
        $lineItems = [];
        foreach ($cartItems as $item) {
            $stmt2 = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?,?,?,?,?)");
            $stmt2->execute([
                $orderId,
                $item['product']['id'],
                $item['product']['name'],
                $item['quantity'],
                $item['product']['price'],
            ]);
            $lineItems[] = [
                'product_name' => $item['product']['name'],
                'quantity'     => $item['quantity'],
                'price'        => $item['product']['price'],
            ];

            // Reduir estoc
            $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?")
                ->execute([$item['quantity'], $item['product']['id']]);
        }

        // Enviar correu
        $emailTo   = $user ? $user['email'] : (isset($_POST['email']) ? $_POST['email'] : ADMIN_EMAIL);
        $emailName = $data['full_name'];
        sendOrderEmail($emailTo, $emailName, $orderId, $lineItems, $totalFinal);

        // Buidar cistella
        clearCart();
        $success  = true;
        $orderId_final = $orderId;
    }
}

$pageTitle = 'Finalitzar compra';
require_once 'includes/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/index.php">Inici</a></li>
            <li class="breadcrumb-item"><a href="/cart.php">Cistella</a></li>
            <li class="breadcrumb-item active">Finalitzar compra</li>
        </ol>
    </nav>

    <?php if ($success): ?>
    <!-- Confirmació de compra -->
    <div class="text-center py-5">
        <div class="display-1 mb-3">✅</div>
        <h2 class="fw-bold text-success">Compra realitzada amb èxit!</h2>
        <p class="lead text-muted">La teva comanda #<?= $orderId_final ?> s'ha processat correctament.</p>
        <p class="text-muted">Rebràs un correu de confirmació en breu. Gràcies per comprar a Jardins de Lliçà!</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="/index.php" class="btn btn-success btn-lg">
                <i class="bi bi-house-fill me-2"></i>Tornar a l'inici
            </a>
            <?php if (isLoggedIn()): ?>
            <a href="/account.php" class="btn btn-outline-success btn-lg">
                <i class="bi bi-person-circle me-2"></i>El meu compte
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php else: ?>
    <h2 class="section-title mb-4"><i class="bi bi-credit-card-fill text-success me-2"></i>Finalitzar compra</h2>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong><i class="bi bi-exclamation-triangle me-2"></i>Corregeix els errors:</strong>
        <ul class="mb-0 mt-2 ps-3">
            <?php foreach ($errors as $e): ?>
            <li><?= h($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form action="" method="POST" id="checkoutForm" novalidate>
        <div class="row g-4">
            <!-- Dades d'enviament -->
            <div class="col-lg-7">
                <div class="card checkout-card mb-4">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-truck me-2"></i>Dades d'enviament
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom complet *</label>
                                <input type="text" name="full_name" class="form-control"
                                       value="<?= h($_POST['full_name'] ?? ($user['full_name'] ?? '')) ?>"
                                       placeholder="Nom i cognoms" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">DNI *</label>
                                <input type="text" name="dni" class="form-control"
                                       value="<?= h($_POST['dni'] ?? ($user['dni'] ?? '')) ?>"
                                       placeholder="12345678A" maxlength="9" required
                                       oninput="this.value=this.value.toUpperCase()">
                                <div class="form-text text-muted">9 caràcters, la darrera és una lletra</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Telèfon *</label>
                                <input type="tel" name="phone" class="form-control"
                                       value="<?= h($_POST['phone'] ?? ($user['phone'] ?? '')) ?>"
                                       placeholder="612345678" maxlength="9" pattern="\d{9}" required>
                            </div>
                            <?php if (!$user): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Correu electrònic *</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= h($_POST['email'] ?? '') ?>"
                                       placeholder="correu@exemple.cat" required>
                            </div>
                            <?php endif; ?>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Direcció *</label>
                                <input type="text" name="address" class="form-control"
                                       value="<?= h($_POST['address'] ?? ($user['address'] ?? '')) ?>"
                                       placeholder="Carrer, número, pis..." required>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Població *</label>
                                <input type="text" name="city" class="form-control"
                                       value="<?= h($_POST['city'] ?? ($user['city'] ?? '')) ?>"
                                       placeholder="Lliçà d'Amunt" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Codi Postal *</label>
                                <input type="text" name="postal_code" class="form-control"
                                       value="<?= h($_POST['postal_code'] ?? ($user['postal_code'] ?? '')) ?>"
                                       placeholder="08186" maxlength="5" pattern="\d{5}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facturació (empreses) -->
                <div class="card checkout-card">
                    <div class="card-header bg-light fw-bold">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="hasBilling" name="has_billing"
                                   value="1"
                                   <?= (!empty($_POST['has_billing']) || ($user && $user['user_type'] === 'company')) ? 'checked' : '' ?>
                                   onchange="toggleBilling(this)">
                            <label class="form-check-label fw-bold" for="hasBilling">
                                <i class="bi bi-building me-2"></i>Facturació per a empresa
                            </label>
                        </div>
                    </div>
                    <div class="card-body" id="billingFields" style="<?= (!empty($_POST['has_billing']) || ($user && $user['user_type'] === 'company')) ? '' : 'display:none' ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">CIF *</label>
                                <input type="text" name="billing_cif" class="form-control"
                                       value="<?= h($_POST['billing_cif'] ?? ($user['company_cif'] ?? '')) ?>"
                                       placeholder="B12345678">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Raó social *</label>
                                <input type="text" name="billing_name" class="form-control"
                                       value="<?= h($_POST['billing_name'] ?? ($user['company_name'] ?? '')) ?>"
                                       placeholder="Empresa S.L.">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Direcció *</label>
                                <input type="text" name="billing_address" class="form-control"
                                       value="<?= h($_POST['billing_address'] ?? ($user['company_address'] ?? '')) ?>"
                                       placeholder="Carrer, número">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Població *</label>
                                <input type="text" name="billing_city" class="form-control"
                                       value="<?= h($_POST['billing_city'] ?? ($user['company_city'] ?? '')) ?>"
                                       placeholder="Població">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Codi Postal *</label>
                                <input type="text" name="billing_postal" class="form-control"
                                       value="<?= h($_POST['billing_postal'] ?? ($user['company_postal_code'] ?? '')) ?>"
                                       placeholder="08000" maxlength="5">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resum de la comanda -->
            <div class="col-lg-5">
                <div class="card checkout-card sticky-top" style="top: 80px;">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-receipt me-2"></i>Resum de la comanda
                    </div>
                    <div class="card-body">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="order-summary-item d-flex justify-content-between">
                            <div>
                                <strong class="small"><?= h($item['product']['name']) ?></strong>
                                <br><small class="text-muted">× <?= $item['quantity'] ?></small>
                            </div>
                            <span class="fw-semibold"><?= number_format($item['subtotal'], 2) ?> €</span>
                        </div>
                        <?php endforeach; ?>

                        <div class="d-flex justify-content-between mt-3 mb-1">
                            <span class="text-muted">Subtotal</span>
                            <span><?= number_format($total, 2) ?> €</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Enviament</span>
                            <span class="<?= $shipping == 0 ? 'text-success fw-semibold' : '' ?>">
                                <?= $shipping == 0 ? 'Gratis' : number_format($shipping, 2) . ' €' ?>
                            </span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <strong class="fs-5">TOTAL</strong>
                            <strong class="fs-4 text-success"><?= number_format($totalFinal, 2) ?> €</strong>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg fw-bold">
                                <i class="bi bi-check-circle-fill me-2"></i>Confirmar comanda
                            </button>
                        </div>
                        <p class="text-muted small text-center mt-3 mb-0">
                            <i class="bi bi-shield-check me-1"></i>Compra segura · Sense compromís de pagament
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
function toggleBilling(checkbox) {
    const fields = document.getElementById('billingFields');
    fields.style.display = checkbox.checked ? 'block' : 'none';
}
</script>

<?php require_once 'includes/footer.php'; ?>
