<?php
require_once '../includes/functions.php';
requireAdmin();

$pdo    = getDB();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// ─── TOGGLE actiu/inactiu (ha d'anar ABANS de qualsevol HTML) ────────────────
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE offers SET active = NOT active WHERE id=?")->execute([$tid]);
    setFlash('success', 'Estat de l\'oferta actualitzat.');
    header('Location: offers.php');
    exit;
}

// ─── ELIMINAR ─────────────────────────────────────────────────────────────────
if ($action === 'delete' && $id > 0) {
    $pdo->prepare("UPDATE offers SET active=0 WHERE id=?")->execute([$id]);
    setFlash('success', 'Oferta eliminada correctament.');
    header('Location: offers.php');
    exit;
}

// ─── GUARDAR oferta ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $editId  = (int)($_POST['edit_id'] ?? 0);

    if (empty($name)) {
        setFlash('danger', 'El nom de l\'oferta és obligatori.');
        header('Location: offers.php');
        exit;
    }

    // ── Gestió d'imatge (NO bloqueja el guardatge si falla) ──────────────────
    $imageName    = $_POST['current_image'] ?? 'no-image.png';
    $imageWarning = '';

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $ftype   = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($ftype, $allowed)) {
            $imageWarning = 'Format d\'imatge no permès.';
        } else {
            $ext  = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $safe = ['jpg'=>'jpg','jpeg'=>'jpg','png'=>'png','webp'=>'webp','gif'=>'gif'];
            $ext  = $safe[$ext] ?? 'jpg';

            $uploadDir = __DIR__ . '/../uploads/offers/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newName = 'offer_' . uniqid() . '.' . $ext;
            $dest    = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $imageName = $newName;
            } else {
                $imageWarning = 'No s\'ha pogut pujar la imatge. Comprova els permisos de la carpeta uploads/.';
            }
        }
    }

    if ($editId > 0) {
        $pdo->prepare("UPDATE offers SET name=?,message=?,image=? WHERE id=?")
            ->execute([$name, $message, $imageName, $editId]);
        $msg = 'Oferta <strong>' . htmlspecialchars($name) . '</strong> actualitzada!';
    } else {
        $pdo->prepare("INSERT INTO offers (name,message,image,active) VALUES (?,?,?,1)")
            ->execute([$name, $message, $imageName]);
        $msg = 'Oferta <strong>' . htmlspecialchars($name) . '</strong> creada!';
    }

    if ($imageWarning) {
        setFlash('warning', $msg . '<br><small>' . $imageWarning . '</small>');
    } else {
        setFlash('success', $msg);
    }
    header('Location: offers.php');
    exit;
}

// ─── LLISTA ───────────────────────────────────────────────────────────────────
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
            <button class="btn btn-warning btn-sm text-dark" id="btnNovaOferta">
                <i class="bi bi-plus-circle me-1"></i>Nova oferta
            </button>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($offers)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>Sense ofertes creades. Afegeix-ne una!
            </div>
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
                            onclick='openEditOfferModal(<?= json_encode($offer) ?>)'>
                        <i class="bi bi-pencil-fill me-1"></i>Editar
                    </button>
                    <a href="offers.php?action=delete&id=<?= $offer['id'] ?>"
                       class="btn btn-outline-danger btn-sm"
                       onclick="return confirm('Eliminar l\'oferta \'<?= h(addslashes($offer['name'])) ?>\'?')"
                       title="Eliminar">
                        <i class="bi bi-trash3-fill"></i>
                    </a>
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
            <form id="offerForm" action="offers.php" method="POST" enctype="multipart/form-data">
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
                        <input type="text" name="name" id="oName" class="form-control"
                               placeholder="Ex: Oferta de primavera" required maxlength="200">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Missatge / Descripció</label>
                        <textarea name="message" id="oMessage" class="form-control"
                                  rows="4" maxlength="500"
                                  placeholder="Descriu l'oferta..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Imatge <small class="text-muted fw-normal">(opcional — JPG, PNG, WEBP)</small>
                        </label>
                        <input type="file" name="image" id="oImage" class="form-control"
                               accept="image/jpeg,image/png,image/webp,image/gif">
                        <div id="oImgPreviewWrap" class="mt-2 d-none">
                            <img id="oImgPreview" src="" alt="previsualització"
                                 style="height:80px;border-radius:8px;object-fit:cover;">
                        </div>
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

<script>
const offerModal = new bootstrap.Modal(document.getElementById('offerModal'));

document.getElementById('btnNovaOferta').addEventListener('click', function () {
    resetOfferModal();
    document.getElementById('offerModalTitle').innerHTML = '<i class="bi bi-tag-fill me-2"></i>Nova oferta';
    offerModal.show();
});

function openEditOfferModal(o) {
    resetOfferModal();
    document.getElementById('offerModalTitle').innerHTML = '<i class="bi bi-pencil-fill me-2"></i>Editar oferta';
    document.getElementById('oEditId').value        = o.id;
    document.getElementById('oName').value          = o.name;
    document.getElementById('oMessage').value       = o.message || '';
    document.getElementById('oCurrentImage').value  = o.image;
    offerModal.show();
}

function resetOfferModal() {
    document.getElementById('oEditId').value       = '0';
    document.getElementById('oName').value         = '';
    document.getElementById('oMessage').value      = '';
    document.getElementById('oCurrentImage').value = 'no-image.png';
    document.getElementById('oImage').value        = '';
    document.getElementById('oImgPreviewWrap').classList.add('d-none');
}

document.getElementById('oImage').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('oImgPreview').src = e.target.result;
        document.getElementById('oImgPreviewWrap').classList.remove('d-none');
    };
    reader.readAsDataURL(file);
});
</script>

<?php require_once '../includes/footer.php'; ?>
