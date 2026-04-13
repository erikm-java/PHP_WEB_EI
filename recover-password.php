<?php
require_once 'includes/functions.php';
require_once 'includes/email.php';

startSession();
if (isLoggedIn()) { header('Location: account.php'); exit; }

$step    = 'request'; // request | reset | done
$errors  = [];
$success = '';

// Pas 2: Restablir la clau amb token
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    $pdo   = getDB();
    $stmt  = $pdo->prepare("SELECT * FROM password_resets WHERE token=? AND used=0 AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $errors[] = 'L\'enllaç de recuperació no és vàlid o ha caducat. Sol·licita'n un de nou.';
    } else {
        $step = 'reset';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPass  = $_POST['new_password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            if (strlen($newPass) < 8) {
                $errors[] = 'La clau ha de tenir almenys 8 caràcters.';
            }
            if ($newPass !== $confirm) {
                $errors[] = 'Les claus no coincideixen.';
            }

            if (empty($errors)) {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password=? WHERE email=?")->execute([$hash, $reset['email']]);
                $pdo->prepare("UPDATE password_resets SET used=1 WHERE token=?")->execute([$token]);
                $step    = 'done';
                $success = 'La clau s\'ha restablert correctament!';
            }
        }
    }

// Pas 1: Sol·licitar recuperació
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Introdueix un correu electrònic vàlid.';
    } else {
        $user = getUserByEmail($email);
        if (!$user) {
            $errors[] = 'No existeix cap compte associat a aquest correu electrònic.';
        } else {
            // Generar token
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hora
            $pdo       = getDB();
            $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?,?,?)")
                ->execute([$email, $token, $expiresAt]);
            // Enviar correu
            sendPasswordResetEmail($email, $token);
            $success = 'T\'hem enviat un correu amb les instruccions per a restablir la clau. Comprova la safata d\'entrada (i la carpeta de correu brossa).';
        }
    }
}

$pageTitle = 'Recuperació de clau';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card border-0 shadow rounded-4 p-4">
                <div class="text-center mb-4">
                    <span class="display-5">🔐</span>
                    <h2 class="fw-bold mt-2">
                        <?= $step === 'reset' ? 'Nova clau' : 'Recuperar la clau' ?>
                    </h2>
                    <p class="text-muted small">
                        <?= $step === 'reset'
                            ? 'Introdueix la teva nova clau d\'accés'
                            : 'Introdueix el teu correu per a rebre l\'enllaç de recuperació' ?>
                    </p>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if ($step === 'done'): ?>
                <!-- Èxit final -->
                <div class="alert alert-success text-center">
                    <i class="bi bi-check-circle-fill fs-3 d-block mb-2"></i>
                    <strong>Clau restablerta!</strong><br>
                    Ja pots iniciar sessió amb la nova clau.
                </div>
                <div class="d-grid mt-3">
                    <a href="/login.php" class="btn btn-success btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sessió
                    </a>
                </div>

                <?php elseif ($success && $step === 'request'): ?>
                <!-- Correu enviat -->
                <div class="alert alert-success">
                    <i class="bi bi-envelope-check-fill me-2"></i>
                    <?= h($success) ?>
                </div>
                <div class="text-center mt-3">
                    <a href="/login.php" class="btn btn-outline-success">
                        <i class="bi bi-arrow-left me-2"></i>Tornar al login
                    </a>
                </div>

                <?php elseif ($step === 'reset'): ?>
                <!-- Formulari nova clau -->
                <form action="" method="POST" novalidate>
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
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">
                            <i class="bi bi-lock-fill me-2"></i>Restablir clau
                        </button>
                    </div>
                </form>

                <?php else: ?>
                <!-- Formulari sol·licitud -->
                <form action="" method="POST" novalidate>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Correu electrònic *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" name="email" class="form-control"
                                   value="<?= h($_POST['email'] ?? '') ?>"
                                   placeholder="correu@exemple.cat" required>
                        </div>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">
                            <i class="bi bi-send-fill me-2"></i>Enviar instruccions
                        </button>
                    </div>
                    <div class="text-center">
                        <a href="/login.php" class="text-muted small">
                            <i class="bi bi-arrow-left me-1"></i>Tornar al login
                        </a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
