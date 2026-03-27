<?php
$pageTitle = 'Change Password';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/functions.php';

$errors = []; $success = false;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!Security::validateCsrfToken($_POST['csrf_token']??'')) { $errors[]='Invalid request.'; }
    else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = db()->prepare("SELECT password FROM users WHERE id=?");
        $stmt->execute([$user['id']]); $row = $stmt->fetch();

        if (!Security::verifyPassword($current, $row['password'])) $errors[]='Current password is incorrect.';
        elseif ($new !== $confirm) $errors[]='New passwords do not match.';
        else {
            $pwdErrors = Security::validatePasswordStrength($new);
            $errors = array_merge($errors, $pwdErrors);
        }

        if (empty($errors)) {
            db()->prepare("UPDATE users SET password=?,updated_at=NOW() WHERE id=?")
                ->execute([Security::hashPassword($new), $user['id']]);
            $success = true;
        }
    }
}
?>
<div class="page-header">
    <h1><i class="bi bi-lock me-2 text-primary"></i>Change Password</h1>
</div>

<div class="row justify-content-center"><div class="col-md-6 col-lg-5">
<?php if ($success): ?>
<div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>Password changed successfully.</div>
<?php else: ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?= Security::e($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="card">
    <div class="card-body p-4">
        <form method="POST">
            <?= Security::csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-control" name="current_password" required>
            </div>
            <div class="mb-2">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="new_password" required>
                <div class="password-strength-bar mt-2"><div class="fill"></div></div>
                <div class="d-flex justify-content-between mt-1">
                    <span class="form-text">8+ chars, uppercase, number &amp; symbol</span>
                    <span class="form-text" id="strength-text"></span>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i>Update Password</button>
        </form>
    </div>
</div>
<?php endif; ?>
</div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
