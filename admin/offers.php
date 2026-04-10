<?php
require_once '../includes/functions.php';
requireAdmin();

$pdo    = getDB();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// Eliminar oferta
if ($action === 'delete' && $id > 0) {
    $pdo->prepare("UPDATE offers SET active=0 WHERE id=?")->execute([$id]);
    setFlash('success', 'Oferta eliminada correctament.');
    header('Location: offers.php');
    exit;
}

// Guardar oferta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $editId  = (int)($_POST['edit_id'] ?? 0);
    $errors  = [];

    if (empty($name)) $errors[] = 'El nom de l\'oferta és obligatori.';

    // Gestió d'imatge
    $imageName = $_POST['current_image'] ?? 'no-image.png';
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed)) {
            $errors[] = 'Format d\'imatge no permès.';
        } else {
            $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('offer_') . '.' . $ext;
            $dest      = __DIR__ . '/../uploads/offers/' . $imageName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $errors[] = 'Error en pujar la imatge.';
                $imageName = $_POST['current_image'] ?? 'no-image.png';
            }
        }
    }

    if (empty($errors)) {
        if ($editId > 0) {
            $pdo->prepare("UPDATE offers SET name=?,message=?,image=? WHERE id=?")
                ->execute([$name, $message, $imageName, $editId]);
            setFlash('success', 'Oferta actualitzada!');
        } else {
            $pdo->prepare("INSERT INTO offers (name,message,image) VALUES (?,?,?)")
                ->execute([$name, $message, $imageName]);
            setFlash('success', 'Oferta creada!');
        }
        header('Location: offers.php');
        exit;
    }
    foreach ($errors as $e) setFlash('danger', $e);
}

$offers    = $pdo->query("SELECT * FROM offers ORDER BY active DESC, created_at DESC")->fetchAll();
$pageTitle = 'Administració - Ofertes';
require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="section-title mb-0">
            <i class="bi bi-tag-fill text-warning me-2"></i>Gestió d'Ofertes
        </h2>
        <div class="d-flex gap-2">
            <a href="/PHP_WEB_EI/admin/index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
            <button class="btn btn-warning btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#offerModal">
                <i class="bi bi-plus-circle me-1"></i>Nova oferta
            </button>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($offers)): ?>
        <div class="col-12">
            <div class="alert alert-info">Sense ofertes creades. Afegeix-ne una!</div>
        </div>
        <?php else: ?>
        <?php foreach ($offers as $offer): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 <?= !$offer['active'] ? 'opacity-50' : '' ?>">
                <div class="card-header d-flex justify-content-between align-items-center
                            <?= $offer['active'] ? 'bg-warning text-dark' : 'bg-secondary text-white' ?>">
                    <span class="fw-bold"><?= h($offer['name']) ?></span>
                    <span class="badge <?= $offer['active'] ? 'bg-dark' : 'bg-light text-dark' ?>">
                        <?= $offer['active'] ? 'Activa' : 'Inactiva' ?>
                    </span>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?= h($offer['message'] ?: 'Sense descripció') ?></p>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= date('d/m/Y H:i', strtotime($offer['created_at'])) ?>
                    </p>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm flex-grow-1"
                            onclick='editOffer(<?= json_encode($offer) ?>)'>
                        <i class="bi bi-pencil-fill me-1"></i>Editar
                    </button>
                    <a href="offers.php?action=delete&id=<?= $offer['id'] ?>"
                       class="btn btn-outline-danger btn-sm"
                       onclick="return confirm('Eliminar oferta?')" title="Eliminar">
                        <i class="bi bi-trash3-fill"></i>
                    </a>
                    <!-- Toggle actiu/inactiu -->
                    <a href="offers.php?toggle=<?= $offer['id'] ?>"
                       class="btn btn-outline-secondary btn-sm"
                       title="<?= $offer['active'] ? 'Desactivar' : 'Activar' ?>">
                        <i class="bi bi-<?= $offer['active'] ? 'pause-fill' : 'play-fill' ?>"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL oferta -->
<div class="modal fade" id="offerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST" enctype="multipart/form-data" novalidate>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold" id="offerModalTitle">
                        <i class="bi bi-tag-fill me-2"></i>Nova oferta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="oEditId" value="0">
                    <input type="hidden" name="current_image" id="oCurrentImage" value="no-image.png">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom de l'oferta *</label>
                        <input type="text" name="name" id="oName" class="form-control" required maxlength="200">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Missatge / Descripció</label>
                        <textarea name="message" id="oMessage" class="form-control" rows="4" maxlength="500"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Imatge (opcional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-save me-2"></i>Desar oferta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Toggle actiu/inactiu
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE offers SET active = NOT active WHERE id=?")->execute([$tid]);
    header('Location: offers.php');
    exit;
}
?>

<script>
function editOffer(o) {
    document.getElementById('offerModalTitle').innerHTML = '<i class="bi bi-pencil-fill me-2"></i>Editar oferta';
    document.getElementById('oEditId').value    = o.id;
    document.getElementById('oName').value      = o.name;
    document.getElementById('oMessage').value   = o.message || '';
    document.getElementById('oCurrentImage').value = o.image;
    new bootstrap.Modal(document.getElementById('offerModal')).show();
}

document.getElementById('offerModal').addEventListener('show.bs.modal', function(event) {
    if (!event.relatedTarget) return;
    document.getElementById('offerModalTitle').innerHTML = '<i class="bi bi-tag-fill me-2"></i>Nova oferta';
    document.getElementById('oEditId').value  = '0';
    document.getElementById('oName').value    = '';
    document.getElementById('oMessage').value = '';
});
</script>

<?php require_once '../includes/footer.php'; ?>
