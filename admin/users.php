<?php
require_once '../includes/functions.php';
requireAdmin();

$pdo    = getDB();
$errors = [];

// Editar usuari
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $editId       = (int)($_POST['edit_id'] ?? 0);
    $fullName     = trim($_POST['full_name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $userType     = in_array($_POST['user_type'] ?? '', ['admin','company','individual']) ? $_POST['user_type'] : 'individual';
    $dni          = strtoupper(trim($_POST['dni'] ?? ''));
    $phone        = trim($_POST['phone'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $city         = trim($_POST['city'] ?? '');
    $postalCode   = trim($_POST['postal_code'] ?? '');
    $newPassword  = trim($_POST['new_password'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El correu no és vàlid.';
    }
    if (empty($username)) {
        $errors[] = 'El nom d\'usuari és obligatori.';
    }

    if (empty($errors) && $editId > 0) {
        // Comprovar que no duplica email/username en un altre usuari
        $check = $pdo->prepare("SELECT id FROM users WHERE (email=? OR username=?) AND id!=?");
        $check->execute([$email, $username, $editId]);
        if ($check->fetch()) {
            $errors[] = 'Ja existeix un altre usuari amb aquest correu o nom d\'usuari.';
        }
    }

    if (empty($errors) && $editId > 0) {
        $pdo->prepare("UPDATE users SET full_name=?,email=?,username=?,user_type=?,dni=?,phone=?,address=?,city=?,postal_code=? WHERE id=?")
            ->execute([$fullName ?: null, $email, $username, $userType, $dni ?: null, $phone ?: null, $address ?: null, $city ?: null, $postalCode ?: null, $editId]);
        // Canviar contrasenya si s'ha especificat
        if (!empty($newPassword) && strlen($newPassword) >= 8) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $editId]);
        }
        setFlash('success', 'Usuari actualitzat correctament!');
        header('Location: users.php');
        exit;
    }

    foreach ($errors as $e) setFlash('danger', $e);
}

$users     = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$pageTitle = 'Administració - Usuaris';
require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="section-title mb-0">
            <i class="bi bi-people-fill text-success me-2"></i>Gestió d'Usuaris
        </h2>
        <a href="/PHP_WEB_EI/admin/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuari</th>
                            <th>Correu</th>
                            <th>Nom complet</th>
                            <th>Tipus</th>
                            <th>Registrat</th>
                            <th>Accions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Sense usuaris</td></tr>
                        <?php else: ?>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="text-muted small">#<?= $u['id'] ?></td>
                            <td>
                                <strong><?= h($u['username']) ?></strong>
                                <?php if ($u['id'] == $_SESSION['user_id']): ?>
                                <span class="badge bg-info ms-1 small">Tu</span>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= h($u['email']) ?></td>
                            <td><?= h($u['full_name'] ?? '-') ?></td>
                            <td>
                                <?php $typeMap = ['admin'=>['danger','Administrador'],
                                                  'company'=>['primary','Empresa'],
                                                  'individual'=>['success','Particular']]; ?>
                                <span class="badge bg-<?= $typeMap[$u['user_type']][0] ?>">
                                    <?= $typeMap[$u['user_type']][1] ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-outline-primary btn-sm"
                                        onclick='openUserModal(<?= json_encode($u) ?>)'>
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
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

<!-- MODAL editar usuari -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="" method="POST" novalidate>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-fill me-2"></i>Editar usuari
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="uEditId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nom d'usuari *</label>
                            <input type="text" name="username" id="uUsername" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Correu electrònic *</label>
                            <input type="email" name="email" id="uEmail" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nom complet</label>
                            <input type="text" name="full_name" id="uFullName" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipus d'usuari</label>
                            <select name="user_type" id="uType" class="form-select">
                                <option value="individual">Particular</option>
                                <option value="company">Empresa</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">DNI</label>
                            <input type="text" name="dni" id="uDni" class="form-control" maxlength="9"
                                   oninput="this.value=this.value.toUpperCase()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Telèfon</label>
                            <input type="text" name="phone" id="uPhone" class="form-control" maxlength="9">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Codi Postal</label>
                            <input type="text" name="postal_code" id="uPostal" class="form-control" maxlength="5">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Direcció</label>
                            <input type="text" name="address" id="uAddress" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Població</label>
                            <input type="text" name="city" id="uCity" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nova clau <small class="text-muted fw-normal">(deixar en blanc per no canviar)</small></label>
                            <input type="password" name="new_password" class="form-control"
                                   placeholder="Mínim 8 caràcters">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-save me-2"></i>Desar canvis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openUserModal(u) {
    document.getElementById('uEditId').value    = u.id;
    document.getElementById('uUsername').value  = u.username;
    document.getElementById('uEmail').value     = u.email;
    document.getElementById('uFullName').value  = u.full_name || '';
    document.getElementById('uType').value      = u.user_type;
    document.getElementById('uDni').value       = u.dni || '';
    document.getElementById('uPhone').value     = u.phone || '';
    document.getElementById('uPostal').value    = u.postal_code || '';
    document.getElementById('uAddress').value   = u.address || '';
    document.getElementById('uCity').value      = u.city || '';
    new bootstrap.Modal(document.getElementById('userModal')).show();
}
</script>

<?php require_once '../includes/footer.php'; ?>
