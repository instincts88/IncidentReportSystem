<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/functions.php';

$errors = []; $success = false;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!Security::validateCsrfToken($_POST['csrf_token']??'')) { $errors[]='Invalid request.'; }
    else {
        $name  = Security::sanitize($_POST['name']??'');
        $email = Security::sanitizeEmail($_POST['email']??'');
        if (strlen($name)<2)  $errors[]='Name must be at least 2 characters.';
        if (!$email)          $errors[]='Invalid email.';

        if (empty($errors)) {
            // Check email not taken by someone else
            $stmt = db()->prepare("SELECT id FROM users WHERE email=? AND id!=?");
            $stmt->execute([$email,$user['id']]);
            if ($stmt->fetch()) { $errors[]='Email already in use.'; }
            else {
                db()->prepare("UPDATE users SET name=?,email=?,updated_at=NOW() WHERE id=?")->execute([$name,$email,$user['id']]);
                $_SESSION['user_name']=$name; $_SESSION['user_email']=$email;
                $success=true;
            }
        }
    }
}

$stmt=db()->prepare("SELECT * FROM users WHERE id=?"); $stmt->execute([$user['id']]); $profile=$stmt->fetch();
$myIncidents=db()->prepare("SELECT COUNT(*) FROM incidents WHERE reported_by=?"); $myIncidents->execute([$user['id']]); $incCount=$myIncidents->fetchColumn();
$myResolved=db()->prepare("SELECT COUNT(*) FROM incidents WHERE reported_by=? AND status='resolved'"); $myResolved->execute([$user['id']]); $resCount=$myResolved->fetchColumn();
?>

<div class="page-header">
    <h1><i class="bi bi-person-circle me-2 text-primary"></i>My Profile</h1>
</div>

<?= renderFlash() ?>
<?php if ($success): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>Profile updated successfully.</div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?= Security::e($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-4">
                <div class="avatar-circle mx-auto mb-3" style="width:72px;height:72px;font-size:1.8rem;background:linear-gradient(135deg,#2563EB,#7C3AED)">
                    <?= strtoupper(substr($profile['name'],0,1)) ?>
                </div>
                <h5 class="fw-bold"><?= Security::e($profile['name']) ?></h5>
                <p class="text-muted small"><?= Security::e($profile['email']) ?></p>
                <span class="badge bg-primary"><?= ucfirst($profile['role']) ?></span>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fw-bold fs-4"><?= $incCount ?></div>
                        <div class="text-muted small">Reported</div>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold fs-4 text-success"><?= $resCount ?></div>
                        <div class="text-muted small">Resolved</div>
                    </div>
                </div>
                <hr>
                <p class="text-muted small mb-0">Member since <?= formatDate($profile['created_at'],'M j, Y') ?></p>
                <?php if ($profile['last_login']): ?>
                <p class="text-muted small">Last login: <?= timeAgo($profile['last_login']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Edit Profile</div>
            <div class="card-body p-4">
                <form method="POST">
                    <?= Security::csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="<?= Security::e($profile['name']) ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" value="<?= Security::e($profile['email']) ?>" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Changes</button>
                        <a href="change-password.php" class="btn btn-outline-secondary"><i class="bi bi-lock me-1"></i>Change Password</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
