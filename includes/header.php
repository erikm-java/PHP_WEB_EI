<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
$categories  = getCategories();
$cartCount   = getCartCount();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' - Jardins de Lliçà' : 'Jardins de Lliçà' ?></title>
    <!-- Favicon (icona de la pestanya del navegador) -->
    <link rel="icon" type="image/jpeg" href="/images/logo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="/images/logo.jpg">
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<!-- CAPÇALERA / NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top shadow">
    <div class="container">

        <!-- Logo + Nom de la botiga (porta a l'inici) -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="/index.php">
            <img src="/images/logo.jpg"
                 alt="Jardins de Lliçà"
                 style="width:36px; height:36px; object-fit:cover; border-radius:50%; border:2px solid rgba(255,255,255,0.5);">
            <span class="fw-bold">Jardins de Lliçà</span>
        </a>

        <!-- Botó hamburguesa mòbil -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">

            <!-- Menú principal -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="/index.php">
                        <i class="bi bi-house-fill"></i> Inici
                    </a>
                </li>

                <!-- Seccions (categories) -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $currentPage === 'section.php' ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-grid-fill"></i> Seccions
                    </a>
                    <ul class="dropdown-menu">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a class="dropdown-item" href="/section.php?slug=<?= h($cat['slug']) ?>">
                                <?= h($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/contact.php">
                        <i class="bi bi-telephone-fill"></i> Contacte
                    </a>
                </li>

                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'account.php' ? 'active' : '' ?>" href="/account.php">
                            <i class="bi bi-person-circle"></i> Gestió del compte
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning <?= strpos($currentPage, 'admin') !== false ? 'active' : '' ?>" href="/admin/index.php">
                            <i class="bi bi-gear-fill"></i> Administració
                        </a>
                    </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <!-- Cercador -->
            <form class="d-flex me-3 my-2 my-lg-0" action="/search.php" method="GET">
                <div class="input-group">
                    <input class="form-control form-control-sm" type="search" name="q"
                           placeholder="Cercar productes..."
                           value="<?= isset($_GET['q']) ? h($_GET['q']) : '' ?>"
                           aria-label="Cerca">
                    <button class="btn btn-light btn-sm" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            <!-- Dreta: Login/Logout + Cistella -->
            <ul class="navbar-nav align-items-center gap-1">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <span class="nav-link text-light">
                            <i class="bi bi-person-check-fill"></i>
                            <?= h($_SESSION['username']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout.php" title="Tancar sessió">
                            <i class="bi bi-box-arrow-right"></i> Log out
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'login.php' ? 'active' : '' ?>" href="/login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Log in
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Cistella -->
                <li class="nav-item">
                    <a class="nav-link position-relative" href="/cart.php" title="Cistella">
                        <i class="bi bi-cart-fill fs-5"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark cart-badge">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div><!-- /.collapse -->
    </div><!-- /.container -->
</nav>

<!-- Zona de missatges flash -->
<div class="container mt-3">
<?php showFlash(); ?>
</div>
