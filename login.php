<?php
require_once 'includes/functions.php';
startSession();

if (isLoggedIn()) { header('Location: index.php'); exit; }

$error   = '';
$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = 'Omple tots els camps.';
    } else {
        $pdo = getDB();
        // Permet login per correu O per nom d'usuari
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);
            setFlash('success', 'Benvingut/da, ' . $user['username'] . '! 🌿');
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Usuari o clau incorrectes. Torna-ho a intentar.';
        }
    }
}

$pageTitle = 'Iniciar sessió';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card border-0 shadow rounded-4 p-4">
                <div class="text-center mb-4">
                    <span class="display-5">🌿</span>
                    <h2 class="fw-bold mt-2">Iniciar sessió</h2>
                    <p class="text-muted small">Accedeix al teu compte de Jardins de Lliçà</p>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= h($error) ?>
                </div>
                <?php endif; ?>

                <form action="" method="POST" novalidate>
                    <input type="hidden" name="redirect" value="<?= h($redirect) ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Usuari o correu electrònic *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="identifier" class="form-control"
                                   value="<?= h($_POST['identifier'] ?? '') ?>"
                                   placeholder="nom_usuari o correu@exemple.cat" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <label class="form-label fw-semibold">Clau *</label>
                            <a href="/recover-password.php" class="small text-success">Has oblidat la clau?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password" id="password" class="form-control"
                                   placeholder="La teva clau" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePass()">
                                <i class="bi bi-eye-fill" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sessió
                        </button>
                    </div>

                    <div class="divider text-center text-muted my-3">
                        <span class="px-2 bg-white small">o</span>
                        <hr class="mt-n3">
                    </div>

                    <div class="d-grid">
                        <a href="/register.php" class="btn btn-outline-success">
                            <i class="bi bi-person-plus-fill me-2"></i>Crea un compte nou
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePass() {
    const f = document.getElementById('password');
    const i = document.getElementById('eyeIcon');
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
