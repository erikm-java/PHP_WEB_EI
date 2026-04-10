<?php
require_once '../includes/functions.php';
requireAdmin();

$pdo = getDB();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE active=1")->fetchColumn();
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalOffers   = $pdo->query("SELECT COUNT(*) FROM offers WHERE active=1")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders")->fetchColumn();
$recentOrders  = $pdo->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();

$pageTitle = 'Administració';
require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title mb-0">
            <i class="bi bi-gear-fill text-success me-2"></i>Panell d'administració
        </h2>
        <span class="badge bg-danger fs-6"><i class="bi bi-shield-fill me-1"></i>Administrador</span>
    </div>

    <!-- Estadístiques -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-stat-card shadow" style="background:linear-gradient(135deg,#2e7d32,#66bb6a);">
                <div class="display-5 mb-2">🌿</div>
                <h2 class="fw-bold"><?= $totalProducts ?></h2>
                <p class="mb-0 opacity-75">Productes actius</p>
                <a href="/PHP_WEB_EI/admin/products.php" class="btn btn-outline-light btn-sm mt-3">Gestionar</a>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-stat-card shadow" style="background:linear-gradient(135deg,#1565c0,#42a5f5);">
                <div class="display-5 mb-2">👥</div>
                <h2 class="fw-bold"><?= $totalUsers ?></h2>
                <p class="mb-0 opacity-75">Usuaris registrats</p>
                <a href="/PHP_WEB_EI/admin/users.php" class="btn btn-outline-light btn-sm mt-3">Gestionar</a>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-stat-card shadow" style="background:linear-gradient(135deg,#e65100,#ffa726);">
                <div class="display-5 mb-2">📦</div>
                <h2 class="fw-bold"><?= $totalOrders ?></h2>
                <p class="mb-0 opacity-75">Comandes totals</p>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-stat-card shadow" style="background:linear-gradient(135deg,#6a1b9a,#ab47bc);">
                <div class="display-5 mb-2">💰</div>
                <h2 class="fw-bold"><?= number_format($totalRevenue, 2) ?> €</h2>
                <p class="mb-0 opacity-75">Facturació total</p>
            </div>
        </div>
    </div>

    <!-- Accions ràpides -->
    <div class="row g-3 mb-5">
        <div class="col-12">
            <h5 class="fw-bold mb-3">Accions ràpides</h5>
        </div>
        <div class="col-sm-4">
            <a href="/PHP_WEB_EI/admin/products.php?action=add" class="btn btn-success w-100 py-3">
                <i class="bi bi-plus-circle-fill fs-4 d-block mb-1"></i>Afegir producte
            </a>
        </div>
        <div class="col-sm-4">
            <a href="/PHP_WEB_EI/admin/offers.php?action=add" class="btn btn-warning w-100 py-3 text-dark">
                <i class="bi bi-tag-fill fs-4 d-block mb-1"></i>Afegir oferta
            </a>
        </div>
        <div class="col-sm-4">
            <a href="/PHP_WEB_EI/admin/users.php" class="btn btn-primary w-100 py-3">
                <i class="bi bi-people-fill fs-4 d-block mb-1"></i>Gestionar usuaris
            </a>
        </div>
    </div>

    <!-- Darreres comandes -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-success text-white fw-bold">
            <i class="bi bi-clock-history me-2"></i>Darreres comandes
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Nom enviament</th>
                            <th>Total</th>
                            <th>Estat</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Sense comandes encara</td></tr>
                        <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= h($order['username'] ?? 'Convidat') ?></td>
                            <td><?= h($order['shipping_name'] ?? '-') ?></td>
                            <td class="fw-bold"><?= number_format($order['total'], 2) ?> €</td>
                            <td>
                                <?php $statusMap = ['pendent'=>'warning','confirmat'=>'info','enviat'=>'primary','lliurat'=>'success']; ?>
                                <span class="badge bg-<?= $statusMap[$order['status']] ?? 'secondary' ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
