<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

Security::startSecureSession();
Security::setSecureHeaders();

if (Auth::isLoggedIn()) { header('Location: dashboard.php'); exit; }

$errors = []; $success = false;
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name     = Security::sanitize($_POST['name'] ?? '');
        $email    = Security::sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (strlen($name) < 2)  $errors[] = 'Full name must be at least 2 characters.';
        if (!$email)            $errors[] = 'Please enter a valid email address.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';

        $pwdErrors = Security::validatePasswordStrength($password);
        $errors = array_merge($errors, $pwdErrors);

        if (empty($errors)) {
            // Check email uniqueness
            $stmt = db()->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with this email already exists.';
            } else {
                $hash = Security::hashPassword($password);
                $stmt = db()->prepare("INSERT INTO users (name, email, password, role, is_active, created_at) VALUES (?, ?, ?, 'user', 1, NOW())");
                $stmt->execute([$name, $email, $hash]);
                $success = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
<div class="login-card" style="max-width:480px">
    <div class="login-logo"><i class="bi bi-shield-exclamation"></i></div>
    <h1 class="text-center fw-bold mb-1" style="font-size:1.5rem">Create Account</h1>
    <p class="text-center text-muted mb-4 small"><?= APP_NAME ?></p>

    <?php if ($success): ?>
    <div class="alert alert-success text-center">
        <i class="bi bi-check-circle-fill me-2"></i>
        Account created! <a href="index.php" class="alert-link fw-semibold">Sign in now</a>
    </div>
    <?php else: ?>

    <?php if ($errors): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?>
            <li><?= Security::e($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <?= Security::csrfField() ?>
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control" name="name" value="<?= Security::e($name) ?>" required placeholder="John Doe" autofocus>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" name="email" value="<?= Security::e($email ?? '') ?>" required placeholder="you@example.com">
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" required placeholder="Min. 8 characters">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('password','eye1')"><i class="bi bi-eye" id="eye1"></i></button>
            </div>
            <div class="password-strength-bar mt-2"><div class="fill"></div></div>
            <div class="d-flex justify-content-between mt-1">
                <span class="form-text">Use 8+ chars, uppercase, number &amp; symbol</span>
                <span class="form-text" id="strength-text"></span>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                <input type="password" class="form-control" name="confirm_password" required placeholder="Repeat password">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('confirm_password','eye2')"><i class="bi bi-eye" id="eye2"></i></button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-person-plus me-2"></i>Create Account
        </button>
    </form>
    <?php endif; ?>
    <hr>
    <p class="text-center mb-0 small text-muted">
        Already have an account? <a href="index.php" class="text-primary fw-semibold">Sign in</a>
    </p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script>
function togglePwd(id, iconId) {
    const el = document.getElementById(id), icon = document.getElementById(iconId);
    el.type = el.type === 'password' ? 'text' : 'password';
    icon.className = el.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
