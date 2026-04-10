<?php
require_once '../includes/functions.php';
requireAdmin();

$pdo    = getDB();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);
$errors = [];
$success = '';

// ─── ELIMINAR ────────────────────────────────────────────────────────────────
if ($action === 'delete' && $id > 0) {
    $pdo->prepare("UPDATE products SET active=0 WHERE id=?")->execute([$id]);
    setFlash('success', 'Producte eliminat correctament.');
    header('Location: products.php');
    exit;
}

// ─── GUARDAR (add / edit) ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $featured    = isset($_POST['featured']) ? 1 : 0;
    $stock       = (int)($_POST['stock'] ?? 0);
    $editId      = (int)($_POST['edit_id'] ?? 0);

    if (empty($name))   $errors[] = 'El nom del producte és obligatori.';
    if ($price <= 0)    $errors[] = 'El preu ha de ser major que 0.';

    // Gestionar imatge
    $imageName = $_POST['current_image'] ?? 'no-image.png';
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed)) {
            $errors[] = 'Format d\'imatge no permès. Utilitza JPG, PNG, WEBP o GIF.';
        } else {
            $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('prod_') . '.' . $ext;
            $dest      = __DIR__ . '/../uploads/products/' . $imageName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $errors[] = 'Error en pujar la imatge.';
                $imageName = $_POST['current_image'] ?? 'no-image.png';
            }
        }
    }

    if (empty($errors)) {
        if ($editId > 0) {
            $pdo->prepare("UPDATE products SET name=?,description=?,price=?,category_id=?,featured=?,stock=?,image=? WHERE id=?")
                ->execute([$name, $description, $price, $categoryId ?: null, $featured, $stock, $imageName, $editId]);
            setFlash('success', 'Producte actualitzat correctament!');
        } else {
            $pdo->prepare("INSERT INTO products (name,description,price,category_id,featured,stock,image) VALUES (?,?,?,?,?,?,?)")
                ->execute([$name, $description, $price, $categoryId ?: null, $featured, $stock, $imageName]);
            setFlash('success', 'Producte creat correctament!');
        }
        header('Location: products.php');
        exit;
    }
}

// Dades pel formulari (mode edit)
$editProduct = null;
if ($action === 'edit' && $id > 0) {
    $editProduct = $pdo->prepare("SELECT * FROM products WHERE id=?")->execute([$id]) ? null : null;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$id]);
    $editProduct = $stmt->fetch();
}

$categories  = getCategories();
$products    = $pdo->query("SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.active=1 ORDER BY p.name")->fetchAll();
$pageTitle   = 'Administració - Productes';
require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="section-title mb-0">
            <i class="bi bi-box-seam text-success me-2"></i>Gestió de Productes
        </h2>
        <div class="d-flex gap-2">
            <a href="/PHP_WEB_EI/admin/index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#productModal">
                <i class="bi bi-plus-circle me-1"></i>Afegir producte
            </button>
        </div>
    </div>

    <!-- Taula de productes -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imatge</th>
                            <th>Nom</th>
                            <th>Categoria</th>
                            <th>Preu</th>
                            <th>Estoc</th>
                            <th>Destacat</th>
                            <th>Accions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Sense productes</td></tr>
                        <?php else: ?>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td class="text-muted small">#<?= $p['id'] ?></td>
                            <td>
                                <div style="width:50px;height:50px;overflow:hidden;border-radius:6px;background:#e8f5e9;">
                                    <?= productImage($p['image'], $p['name']) ?>
                                </div>
                            </td>
                            <td>
                                <strong><?= h($p['name']) ?></strong>
                                <br><small class="text-muted"><?= h(mb_substr($p['description'], 0, 50)) ?>...</small>
                            </td>
                            <td><?= h($p['cat_name'] ?? '-') ?></td>
                            <td class="fw-bold text-success"><?= number_format($p['price'], 2) ?> €</td>
                            <td>
                                <?php if ($p['stock'] <= 0): ?>
                                    <span class="badge bg-danger">Sense estoc</span>
                                <?php elseif ($p['stock'] <= 5): ?>
                                    <span class="badge bg-warning text-dark"><?= $p['stock'] ?> ud.</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= $p['stock'] ?> ud.</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $p['featured'] ? '<i class="bi bi-star-fill text-warning fs-5"></i>' : '<i class="bi bi-star text-muted fs-5"></i>' ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-outline-primary btn-sm"
                                            onclick='editProduct(<?= json_encode($p) ?>)'
                                            title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <a href="products.php?action=delete&id=<?= $p['id'] ?>"
                                       class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('Eliminar el producte \'<?= h(addslashes($p['name'])) ?>\'?')"
                                       title="Eliminar">
                                        <i class="bi bi-trash3-fill"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Afegir / Editar producte -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="" method="POST" enctype="multipart/form-data" novalidate>
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="modalTitle">
                        <i class="bi bi-box-seam me-2"></i>Nou producte
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="editId" value="0">
                    <input type="hidden" name="current_image" id="currentImage" value="no-image.png">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nom del producte *</label>
                            <input type="text" name="name" id="prodName" class="form-control" required maxlength="200">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Preu (€) *</label>
                            <input type="number" name="price" id="prodPrice" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripció</label>
                            <textarea name="description" id="prodDesc" class="form-control" rows="3" maxlength="1000"></textarea>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Categoria</label>
                            <select name="category_id" id="prodCat" class="form-select">
                                <option value="">-- Sense categoria --</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Estoc</label>
                            <input type="number" name="stock" id="prodStock" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="featured" id="prodFeatured">
                                <label class="form-check-label fw-semibold" for="prodFeatured">
                                    <i class="bi bi-star-fill text-warning me-1"></i>Destacat
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Imatge del producte</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div id="currentImagePreview" class="mt-2" style="display:none;">
                                <small class="text-muted">Imatge actual:</small>
                                <img id="imgPreview" src="" alt="" style="height:60px;width:60px;object-fit:cover;border-radius:6px;" class="ms-2">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="bi bi-save me-2"></i>Desar producte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editProduct(p) {
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-fill me-2"></i>Editar producte';
    document.getElementById('editId').value = p.id;
    document.getElementById('prodName').value = p.name;
    document.getElementById('prodPrice').value = p.price;
    document.getElementById('prodDesc').value = p.description || '';
    document.getElementById('prodCat').value = p.category_id || '';
    document.getElementById('prodStock').value = p.stock || 0;
    document.getElementById('prodFeatured').checked = p.featured == 1;
    document.getElementById('currentImage').value = p.image;
    // Mostrar preview imatge actual
    const prev = document.getElementById('currentImagePreview');
    const img  = document.getElementById('imgPreview');
    img.src    = 'https://placehold.co/60x60/e8f5e9/2e7d32?text=' + encodeURIComponent(p.name.substring(0,5));
    prev.style.display = 'block';
    new bootstrap.Modal(document.getElementById('productModal')).show();
}

// Reset modal en obrir per a afegir nou
document.getElementById('productModal').addEventListener('show.bs.modal', function(event) {
    if (!event.relatedTarget) return;
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-box-seam me-2"></i>Nou producte';
    document.getElementById('editId').value = '0';
    document.querySelector('[name="name"]').value = '';
    document.querySelector('[name="price"]').value = '';
    document.querySelector('[name="description"]').value = '';
    document.querySelector('[name="category_id"]').value = '';
    document.querySelector('[name="stock"]').value = '0';
    document.querySelector('[name="featured"]').checked = false;
    document.getElementById('currentImagePreview').style.display = 'none';
});
</script>

<?php require_once '../includes/footer.php'; ?>
