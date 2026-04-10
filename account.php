<?php
require_once 'includes/functions.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];
$user   = getUserById($userId);
$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? 'personal';

    if ($section === 'personal') {
        $fullName   = trim($_POST['full_name'] ?? '');
        $dni        = strtoupper(trim($_POST['dni'] ?? ''));
        $phone      = trim($_POST['phone'] ?? '');
        $address    = trim($_POST['address'] ?? '');
        $city       = trim($_POST['city'] ?? '');
        $postalCode = trim($_POST['postal_code'] ?? '');
        $userType   = in_array($_POST['user_type'] ?? '', ['company', 'individual']) ? $_POST['user_type'] : 'individual';

        // Validacions
        if (!empty($dni) && !validateDNI($dni)) {
            $errors[] = 'El DNI no és vàlid. Comprova els 9 caràcters i la lletra de control.';
        }
        if (!empty($phone) && !preg_match('/^\d{9}$/', $phone)) {
            $errors[] = 'El telèfon ha de tenir exactament 9 dígits.';
        }
        if (!empty($postalCode) && !preg_match('/^\d{5}$/', $postalCode)) {
            $errors[] = 'El codi postal ha de tenir 5 dígits.';
        }

        if (empty($errors)) {
            $pdo = getDB();
            $stmt = $pdo->prepare("
                UPDATE users SET full_name=?, dni=?, phone=?, address=?, city=?, postal_code=?, user_type=?
                WHERE id=?
            ");
            $stmt->execute([$fullName, $dni ?: null, $phone ?: null, $address ?: null, $city ?: null, $postalCode ?: null, $userType, $userId]);
            $success = 'Informació personal actualitzada correctament!';
            $user = getUserById($userId); // Refrescar
            $_SESSION['full_name'] = $user['full_name'];
        }

    } elseif ($section === 'company') {
        $cif         = trim($_POST['company_cif'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $companyAddr = trim($_POST['company_address'] ?? '');
        $companyCity = trim($_POST['company_city'] ?? '');
        $companyPost = trim($_POST['company_postal_code'] ?? '');

        if (!empty($companyPost) && !preg_match('/^\d{5}$/', $companyPost)) {
            $errors[] = 'El codi postal de facturació ha de tenir 5 dígits.';
        }

        if (empty($errors)) {
            $pdo = getDB();
            $stmt = $pdo->prepare("
                UPDATE users SET company_cif=?, company_name=?, company_address=?, company_city=?, company_postal_code=?, user_type='company'
                WHERE id=?
            ");
            $stmt->execute([$cif ?: null, $companyName ?: null, $companyAddr ?: null, $companyCity ?: null, $companyPost ?: null, $userId]);
            $success = 'Dades de facturació actualitzades!';
            $user = getUserById($userId);
        }

    } elseif ($section === 'password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPass, $user['password'])) {
            $errors[] = 'La clau actual no és correcta.';
        }
        if (strlen($newPass) < 8) {
            $errors[] = 'La nova clau ha de tenir almenys 8 caràcters.';
        }
        if ($newPass !== $confirmPass) {
            $errors[] = 'Les noves claus no coincideixen.';
        }

        if (empty($errors)) {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $pdo  = getDB();
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $userId]);
            $success = 'Clau canviada correctament!';
        }
    }

    if ($errors) setFlash('danger', implode('<br>', $errors));
    if ($success) setFlash('success', $success);
    header('Location: account.php');
    exit;
}

$pageTitle = 'Gestió del compte';
require_once 'includes/header.php';
?>

<div class="container py-4">
    <h2 class="section-title mb-4"><i class="bi bi-person-circle text-success me-2"></i>Gestió del compte</h2>

    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="account-sidebar">
                <div class="text-center mb-4">
                    <div class="display-4 mb-2">👤</div>
                    <h6 class="fw-bold mb-0"><?= h($user['full_name'] ?: $user['username']) ?></h6>
                    <small class="text-muted"><?= h($user['email']) ?></small>
                    <br><span class="badge <?= $user['user_type']==='admin' ? 'bg-danger' : ($user['user_type']==='company' ? 'bg-primary' : 'bg-success') ?> mt-2">
                        <?= $user['user_type']==='admin' ? 'Administrador' : ($user['user_type']==='company' ? 'Empresa' : 'Particular') ?>
                    </span>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="#personal" data-bs-toggle="tab">
                        <i class="bi bi-person-fill me-2"></i>Informació personal
                    </a>
                    <a class="nav-link" href="#company" data-bs-toggle="tab">
                        <i class="bi bi-building me-2"></i>Facturació empresa
                    </a>
                    <a class="nav-link" href="#password" data-bs-toggle="tab">
                        <i class="bi bi-lock-fill me-2"></i>Canviar clau
                    </a>
                </nav>
            </div>
        </div>

        <!-- Contingut principal -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Informació personal -->
                <div class="tab-pane fade show active" id="personal">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-success text-white fw-bold">
                            <i class="bi bi-person-fill me-2"></i>Informació personal
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" novalidate>
                                <input type="hidden" name="section" value="personal">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nom complet</label>
                                        <input type="text" name="full_name" class="form-control"
                                               value="<?= h($user['full_name'] ?? '') ?>"
                                               placeholder="Nom i cognoms">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">DNI</label>
                                        <input type="text" name="dni" class="form-control"
                                               value="<?= h($user['dni'] ?? '') ?>"
                                               placeholder="12345678A" maxlength="9"
                                               oninput="this.value=this.value.toUpperCase()"
                                               onblur="validateDNIField(this)">
                                        <div class="form-text">9 caràcters, la darrera una lletra</div>
                                        <div class="invalid-feedback">DNI no vàlid</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Telèfon</label>
                                        <input type="tel" name="phone" class="form-control"
                                               value="<?= h($user['phone'] ?? '') ?>"
                                               placeholder="612345678" maxlength="9" pattern="\d{9}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tipus d'usuari</label>
                                        <select name="user_type" class="form-select" <?= $user['user_type']==='admin' ? 'disabled' : '' ?>>
                                            <option value="individual" <?= $user['user_type']==='individual' ? 'selected' : '' ?>>Particular</option>
                                            <option value="company" <?= $user['user_type']==='company' ? 'selected' : '' ?>>Empresa</option>
                                        </select>
                                        <?php if ($user['user_type']==='admin'): ?>
                                        <input type="hidden" name="user_type" value="admin">
                                        <div class="form-text text-danger">El tipus administrador només pot ser canviat per un admin.</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Direcció</label>
                                        <input type="text" name="address" class="form-control"
                                               value="<?= h($user['address'] ?? '') ?>"
                                               placeholder="Carrer, número, pis...">
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label fw-semibold">Població</label>
                                        <input type="text" name="city" class="form-control"
                                               value="<?= h($user['city'] ?? '') ?>"
                                               placeholder="Lliçà d'Amunt">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">Codi Postal</label>
                                        <input type="text" name="postal_code" class="form-control"
                                               value="<?= h($user['postal_code'] ?? '') ?>"
                                               placeholder="08186" maxlength="5" pattern="\d{5}">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-save me-2"></i>Desar canvis
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Facturació empresa -->
                <div class="tab-pane fade" id="company">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-success text-white fw-bold">
                            <i class="bi bi-building me-2"></i>Dades de facturació (empresa)
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" novalidate>
                                <input type="hidden" name="section" value="company">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">CIF</label>
                                        <input type="text" name="company_cif" class="form-control"
                                               value="<?= h($user['company_cif'] ?? '') ?>"
                                               placeholder="B12345678">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Raó social</label>
                                        <input type="text" name="company_name" class="form-control"
                                               value="<?= h($user['company_name'] ?? '') ?>"
                                               placeholder="Empresa S.L.">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Direcció</label>
                                        <input type="text" name="company_address" class="form-control"
                                               value="<?= h($user['company_address'] ?? '') ?>"
                                               placeholder="Carrer, número">
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label fw-semibold">Població</label>
                                        <input type="text" name="company_city" class="form-control"
                                               value="<?= h($user['company_city'] ?? '') ?>"
                                               placeholder="Població">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">Codi Postal</label>
                                        <input type="text" name="company_postal_code" class="form-control"
                                               value="<?= h($user['company_postal_code'] ?? '') ?>"
                                               placeholder="08000" maxlength="5">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-save me-2"></i>Desar dades de facturació
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Canviar clau -->
                <div class="tab-pane fade" id="password">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-success text-white fw-bold">
                            <i class="bi bi-lock-fill me-2"></i>Canviar la clau d'accés
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" novalidate>
                                <input type="hidden" name="section" value="password">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Clau actual *</label>
                                    <input type="password" name="current_password" class="form-control"
                                           placeholder="Clau actual" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nova clau *</label>
                                    <input type="password" name="new_password" class="form-control"
                                           placeholder="Mínim 8 caràcters" minlength="8" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Confirma la nova clau *</label>
                                    <input type="password" name="confirm_password" class="form-control"
                                           placeholder="Repeteix la nova clau" required>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-lock-fill me-2"></i>Canviar clau
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validació DNI en temps real (al perdre el focus)
function validateDNIField(input) {
    const val = input.value.trim().toUpperCase();
    if (!val) return;
    const letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
    let valid = false;
    if (/^[0-9]{8}[A-Z]$/.test(val)) {
        valid = letters[parseInt(val.slice(0,8)) % 23] === val[8];
    } else if (/^[XYZ][0-9]{7}[A-Z]$/.test(val)) {
        const num = val.replace('X','0').replace('Y','1').replace('Z','2');
        valid = letters[parseInt(num.slice(0,8)) % 23] === val[8];
    }
    input.classList.toggle('is-invalid', !valid);
    input.classList.toggle('is-valid', valid);
}
// Activar tab correcta si hi ha error
document.addEventListener('DOMContentLoaded', function() {
    const activeSection = '<?= h($_POST['section'] ?? 'personal') ?>';
    if (activeSection !== 'personal') {
        const tab = document.querySelector('[href="#' + activeSection + '"]');
        if (tab) new bootstrap.Tab(tab).show();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
