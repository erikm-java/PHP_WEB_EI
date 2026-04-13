<?php
require_once __DIR__ . '/../config/database.php';

// ─── Sessió ───────────────────────────────────────────────────────────────────
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    startSession();
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// ─── Productes ────────────────────────────────────────────────────────────────
function getFeaturedProducts(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.featured = 1 AND p.active = 1 ORDER BY p.created_at DESC LIMIT 8");
    return $stmt->fetchAll();
}

function getProductsByCategory(int $categoryId, int $limit = 20): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.active = 1 ORDER BY p.name LIMIT ?");
    $stmt->execute([$categoryId, $limit]);
    return $stmt->fetchAll();
}

function searchProducts(string $query, int $limit = 50): array {
    $pdo = getDB();
    $term = '%' . $query . '%';
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.active = 1 AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?) ORDER BY p.featured DESC, p.name LIMIT ?");
    $stmt->execute([$term, $term, $term, $limit]);
    return $stmt->fetchAll();
}

function getProductById(int $id): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.active = 1");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getAllProducts(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name");
    return $stmt->fetchAll();
}

// ─── Categories ───────────────────────────────────────────────────────────────
function getCategories(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

function getCategoryBySlug(string $slug): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

// ─── Ofertes ──────────────────────────────────────────────────────────────────
function getActiveOffers(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM offers WHERE active = 1 ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

// ─── Cistella (COOKIE) ────────────────────────────────────────────────────────
function getCart(): array {
    if (isset($_COOKIE['cart'])) {
        $cart = json_decode($_COOKIE['cart'], true);
        return is_array($cart) ? $cart : [];
    }
    return [];
}

function saveCart(array $cart): void {
    setcookie('cart', json_encode($cart), time() + 60 * 60 * 24 * 7, '/');
    $_COOKIE['cart'] = json_encode($cart);
}

function addToCart(int $productId, int $qty = 1): void {
    $cart = getCart();
    if (isset($cart[$productId])) {
        $cart[$productId] += $qty;
    } else {
        $cart[$productId] = $qty;
    }
    saveCart($cart);
}

function removeFromCart(int $productId): void {
    $cart = getCart();
    unset($cart[$productId]);
    saveCart($cart);
}

function updateCartItem(int $productId, int $qty): void {
    $cart = getCart();
    if ($qty <= 0) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $qty;
    }
    saveCart($cart);
}

function clearCart(): void {
    setcookie('cart', '', time() - 3600, '/');
    unset($_COOKIE['cart']);
}

function getCartCount(): int {
    $cart = getCart();
    return array_sum($cart);
}

function getCartDetails(): array {
    $cart = getCart();
    if (empty($cart)) return [];

    $pdo = getDB();
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND active = 1");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    $result = [];
    foreach ($products as $p) {
        $result[] = [
            'product'  => $p,
            'quantity' => $cart[$p['id']],
            'subtotal' => $p['price'] * $cart[$p['id']],
        ];
    }
    return $result;
}

function getCartTotal(): float {
    $items = getCartDetails();
    return array_sum(array_column($items, 'subtotal'));
}

// ─── Usuaris ──────────────────────────────────────────────────────────────────
function getUserByEmail(string $email): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() ?: null;
}

function getUserByUsername(string $username): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch() ?: null;
}

function getUserById(int $id): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function loginUser(array $user): void {
    startSession();
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['user_type'] = $user['user_type'];
    session_regenerate_id(true);
}

// ─── Validació DNI ───────────────────────────────────────────────────────────
function validateDNI(string $dni): bool {
    $dni = strtoupper(trim($dni));
    if (!preg_match('/^[0-9]{8}[A-Z]$/', $dni) && !preg_match('/^[XYZ][0-9]{7}[A-Z]$/', $dni)) {
        return false;
    }
    $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
    if (preg_match('/^[XYZ]/', $dni)) {
        $num = str_replace(['X','Y','Z'], ['0','1','2'], substr($dni, 0, 8));
        $num = (int)$num;
    } else {
        $num = (int)substr($dni, 0, 8);
    }
    return $letters[$num % 23] === $dni[strlen($dni) - 1];
}

// ─── Sanejament ───────────────────────────────────────────────────────────────
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function sanitize(string $str): string {
    return trim(htmlspecialchars($str, ENT_QUOTES, 'UTF-8'));
}

// ─── Imatge de producte ───────────────────────────────────────────────────────
function productImage(string $img, string $alt = ''): string {
    $path = '/uploads/products/' . $img;
    $placeholder = 'https://placehold.co/300x300/e8f5e9/2e7d32?text=' . urlencode($alt ?: 'Producte');
    // Si el fitxer existeix físicament, usa'l; si no, usa placeholder
    $fullPath = __DIR__ . '/../uploads/products/' . $img;
    $src = (file_exists($fullPath) && $img !== 'no-image.png') ? $path : $placeholder;
    return '<img src="' . h($src) . '" alt="' . h($alt) . '" class="img-fluid">';
}

function offerImage(string $img, string $alt = ''): string {
    $path = '/uploads/offers/' . $img;
    $placeholder = 'https://placehold.co/800x300/c8e6c9/1b5e20?text=' . urlencode($alt ?: 'Oferta');
    $fullPath = __DIR__ . '/../uploads/offers/' . $img;
    $src = (file_exists($fullPath) && $img !== 'no-image.png') ? $path : $placeholder;
    return '<img src="' . h($src) . '" alt="' . h($alt) . '" class="img-fluid w-100">';
}

// ─── Flash messages ───────────────────────────────────────────────────────────
function setFlash(string $type, string $msg): void {
    startSession();
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    startSession();
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function showFlash(): void {
    $f = getFlash();
    if ($f) {
        echo '<div class="alert alert-' . h($f['type']) . ' alert-dismissible fade show" role="alert">'
            . h($f['msg'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}
