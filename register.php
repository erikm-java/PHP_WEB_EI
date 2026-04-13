<?php
require_once 'includes/functions.php';
require_once 'includes/email.php';

startSession();
if (isLoggedIn()) { header('Location: account.php'); exit; }

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validacions
    if (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'El nom d\'usuari ha de tenir entre 3 i 50 caràcters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El correu electrònic no és vàlid.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'La clau ha de tenir almenys 8 caràcters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Les claus no coincideixen.';
    }

    if (empty($errors)) {
        $pdo = getDB();
        // Comprovar si ja existeix
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $errors[] = 'Ja existeix un compte amb aquest correu o nom d\'usuari.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hash]);
            // Enviar correu de benvinguda
            sendWelcomeEmail($email, $username);
            $success = true;
        }
    }
}

$pageTitle = 'Registre';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow rounded-4 p-4">
                <div class="text-center mb-4">
                    <span class="display-5">🌱</span>
                    <h2 class="fw-bold mt-2">Crea el teu compte</h2>
                    <p class="text-muted">Registra't i comença a comprar</p>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success text-center">
                    <i class="bi bi-check-circle-fill fs-4 d-block mb-2"></i>
                    <strong>Compte creat correctament!</strong><br>
                    T'hem enviat un correu de benvinguda.<br>
                    <a href="/login.php" class="btn btn-success mt-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sessió
                    </a>
                </div>
                <?php else: ?>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?>
                        <li><?= h($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form action="" method="POST" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom d'usuari *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="username" class="form-control"
                                   value="<?= h($_POST['username'] ?? '') ?>"
                                   placeholder="el_teu_nom" required minlength="3" maxlength="50">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Correu electrònic *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" name="email" class="form-control"
                                   value="<?= h($_POST['email'] ?? '') ?>"
                                   placeholder="correu@exemple.cat" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Clau *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password" id="password" class="form-control"
                                   placeholder="Mínim 8 caràcters" required minlength="8">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePass('password','eyePass')">
                                <i class="bi bi-eye-fill" id="eyePass"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirma la clau *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="confirm_password" id="confirm" class="form-control"
                                   placeholder="Repeteix la clau" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePass('confirm','eyeConf')">
                                <i class="bi bi-eye-fill" id="eyeConf"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">
                            <i class="bi bi-person-plus-fill me-2"></i>Registra'm
                        </button>
                    </div>

                    <div class="text-center text-muted small">
                        Ja tens compte?
                        <a href="/login.php" class="fw-semibold">Inicia sessió</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function togglePass(fieldId, iconId) {
    const f = document.getElementById(fieldId);
    const i = document.getElementById(iconId);
    if (f.type === 'password') {
        f.type = 'text';
        i.className = 'bi bi-eye-slash-fill';
    } else {
        f.type = 'password';
        i.className = 'bi bi-eye-fill';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
