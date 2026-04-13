<?php
require_once '../includes/functions.php';
requireAdmin();

$pdo    = getDB();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// ─── ELIMINAR ─────────────────────────────────────────────────────────────────
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
    // Acceptar tant punt com coma com a separador decimal
    $price       = (float)str_replace(',', '.', $_POST['price'] ?? '0');
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $featured    = isset($_POST['featured']) ? 1 : 0;
    $stock       = (int)($_POST['stock'] ?? 0);
    $editId      = (int)($_POST['edit_id'] ?? 0);

    $errors = [];
    if (empty($name))  $errors[] = 'El nom del producte és obligatori.';
    if ($price <= 0)   $errors[] = 'El preu ha de ser major que 0.';

    if (!empty($errors)) {
        // Mostrar errors via flash i tornar a la pàgina
        setFlash('danger', '<strong>Errors en el formulari:</strong><ul class="mb-0 mt-1"><li>' . implode('</li><li>', $errors) . '</li></ul>');
        header('Location: products.php');
        exit;
    }

    // ── Gestionar imatge (NO bloqueja el guardatge si falla) ──────────────────
    $imageName    = $_POST['current_image'] ?? 'no-image.png';
    $imageWarning = '';

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $ftype   = mime_content_type($_FILES['image']['tmp_name']); // més fiable que $_FILES[type]

        if (!in_array($ftype, $allowed)) {
            $imageWarning = 'Format d\'imatge no permès. El producte s\'ha desat sense imatge.';
        } else {
            $ext  = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $safe = ['jpg'=>'jpg','jpeg'=>'jpg','png'=>'png','webp'=>'webp','gif'=>'gif'];
            $ext  = $safe[$ext] ?? 'jpg';

            $uploadDir = __DIR__ . '/../uploads/products/';
            // Crear directori si no existeix
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newName = 'prod_' . uniqid() . '.' . $ext;
            $dest    = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $imageName = $newName;
            } else {
                $imageWarning = 'No s\'ha pogut pujar la imatge (comprova els permisos de la carpeta uploads/). El producte s\'ha desat sense imatge.';
            }
        }
    }

    // ── Desar a la BD ─────────────────────────────────────────────────────────
    if ($editId > 0) {
        $pdo->prepare("UPDATE products SET name=?,description=?,price=?,category_id=?,featured=?,stock=?,image=? WHERE id=?")
            ->execute([$name, $description, $price, $categoryId ?: null, $featured, $stock, $imageName, $editId]);
        $msg = 'Producte <strong>' . htmlspecialchars($name) . '</strong> actualitzat correctament!';
    } else {
        $pdo->prepare("INSERT INTO products (name,description,price,category_id,featured,stock,image,active) VALUES (?,?,?,?,?,?,?,1)")
            ->execute([$name, $description, $price, $categoryId ?: null, $featured, $stock, $imageName]);
        $msg = 'Producte <strong>' . htmlspecialchars($name) . '</strong> creat correctament!';
    }

    if ($imageWarning) {
        setFlash('warning', $msg . '<br><small><i class="bi bi-exclamation-triangle me-1"></i>' . $imageWarning . '</small>');
    } else {
        setFlash('success', $msg);
    }
    header('Location: products.php');
    exit;
}

$categories = getCategories();
$products   = $pdo->query(
    "SELECT p.*, c.name AS cat_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.active = 1
     ORDER BY p.name"
)->fetchAll();

$pageTitle = 'Administració - Productes';
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
            <button class="btn btn-success btn-sm" id="btnNouProducte">
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
                        <tr><td colspan="8" class="text-center text-muted py-4">Sense productes. Afegeix-ne un!</td></tr>
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
                                <?php if (!empty($p['description'])): ?>
                                <br><small class="text-muted"><?= h(mb_substr($p['description'], 0, 55)) ?>...</small>
                                <?php endif; ?>
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
                                <?= $p['featured']
                                    ? '<i class="bi bi-star-fill text-warning fs-5"></i>'
                                    : '<i class="bi bi-star text-muted fs-5"></i>' ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-outline-primary btn-sm"
                                            onclick='openEditModal(<?= json_encode($p) ?>)'
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
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="productForm" action="products.php" method="POST" enctype="multipart/form-data">
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
                            <input type="text" name="name" id="prodName" class="form-control"
                                   placeholder="Ex: Monstera Deliciosa" required maxlength="200">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Preu (€) *</label>
                            <div class="input-group">
                                <input type="number" name="price" id="prodPrice" class="form-control"
                                       step="0.01" min="0.01" placeholder="0.00" required>
                                <span class="input-group-text">€</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripció</label>
                            <textarea name="description" id="prodDesc" class="form-control"
                                      rows="3" maxlength="1000"
                                      placeholder="Descriu el producte..."></textarea>
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
                            <input type="number" name="stock" id="prodStock" class="form-control"
                                   min="0" value="0">
                        </div>
                        <div class="col-md-3 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="featured" id="prodFeatured">
                                <label class="form-check-label fw-semibold" for="prodFeatured">
                                    <i class="bi bi-star-fill text-warning me-1"></i>Destacat
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Imatge del producte
                                <small class="text-muted fw-normal">(JPG, PNG, WEBP — màx. 5 MB)</small>
                            </label>
                            <input type="file" name="image" id="prodImage" class="form-control"
                                   accept="image/jpeg,image/png,image/webp,image/gif">
                            <div id="imgPreviewWrap" class="mt-2 d-none">
                                <small class="text-muted">Previsualització:</small><br>
                                <img id="imgPreview" src="" alt="previsualització"
                                     style="height:80px;width:80px;object-fit:cover;border-radius:8px;margin-top:4px;">
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
const productModal = new bootstrap.Modal(document.getElementById('productModal'));

// Botó "Afegir producte" → reseteja el modal i l'obre
document.getElementById('btnNouProducte').addEventListener('click', function () {
    resetModal();
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-box-seam me-2"></i>Nou producte';
    productModal.show();
});

// Botó "Editar" → omple el modal amb les dades del producte
function openEditModal(p) {
    resetModal();
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-fill me-2"></i>Editar producte';
    document.getElementById('editId').value     = p.id;
    document.getElementById('prodName').value   = p.name;
    document.getElementById('prodPrice').value  = p.price;
    document.getElementById('prodDesc').value   = p.description || '';
    document.getElementById('prodCat').value    = p.category_id || '';
    document.getElementById('prodStock').value  = p.stock || 0;
    document.getElementById('prodFeatured').checked = (p.featured == 1);
    document.getElementById('currentImage').value   = p.image;
    productModal.show();
}

function resetModal() {
    document.getElementById('editId').value     = '0';
    document.getElementById('prodName').value   = '';
    document.getElementById('prodPrice').value  = '';
    document.getElementById('prodDesc').value   = '';
    document.getElementById('prodCat').value    = '';
    document.getElementById('prodStock').value  = '0';
    document.getElementById('prodFeatured').checked = false;
    document.getElementById('currentImage').value   = 'no-image.png';
    document.getElementById('prodImage').value  = '';
    document.getElementById('imgPreviewWrap').classList.add('d-none');
}

// Previsualització de la imatge seleccionada
document.getElementById('prodImage').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('imgPreview').src = e.target.result;
        document.getElementById('imgPreviewWrap').classList.remove('d-none');
    };
    reader.readAsDataURL(file);
});
</script>

<?php require_once '../includes/footer.php'; ?>
