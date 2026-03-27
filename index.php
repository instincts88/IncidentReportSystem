<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';

Security::startSecureSession();
Security::setSecureHeaders();

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $result   = Auth::login($email, $password);
        if ($result['success']) {
            $redirect = $_GET['redirect'] ?? 'dashboard.php';
            // Sanitize redirect URL
            if (!filter_var($redirect, FILTER_VALIDATE_URL) && strpos($redirect, '..') === false) {
                header('Location: ' . $redirect);
            } else {
                header('Location: dashboard.php');
            }
            exit;
        }
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
<div class="login-card">
    <div class="login-logo"><i class="bi bi-shield-exclamation"></i></div>
    <h1 class="text-center fw-bold mb-1" style="font-size:1.5rem"><?= APP_NAME ?></h1>
    <p class="text-center text-muted mb-4" style="font-size:0.9rem">Sign in to your account</p>

    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span><?= Security::e($error) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate autocomplete="off">
        <?= Security::csrfField() ?>
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?= Security::e($email) ?>" required autofocus
                    placeholder="you@example.com" autocomplete="email">
            </div>
        </div>
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label mb-0">Password</label>
                <a href="forgot-password.php" class="small text-primary text-decoration-none">Forgot password?</a>
            </div>
            <div class="input-group mt-1">
                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password"
                    required placeholder="••••••••" autocomplete="current-password">
                <button type="button" class="btn btn-outline-secondary" id="togglePwd"
                    onclick="togglePassword()" title="Show/hide password">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label small" for="remember">Keep me signed in</label>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
        </button>
    </form>
    <hr>
    <p class="text-center mb-0 small text-muted">
        Don't have an account? <a href="register.php" class="text-primary fw-semibold">Create one</a>
    </p>
</div>
<script>
function togglePassword() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        pwd.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
