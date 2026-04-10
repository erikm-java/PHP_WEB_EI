<?php
require_once 'includes/functions.php';
startSession();
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
setFlash('info', 'Has tancat la sessió correctament. Fins aviat! 👋');
header('Location: /PHP_WEB_EI/index.php');
exit;
