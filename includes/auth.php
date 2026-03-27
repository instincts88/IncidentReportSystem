<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/db.php';

class Auth {

    public static function login(string $email, string $password): array {
        Security::startSecureSession();

        if (!Security::checkLoginAttempts($email)) {
            $remaining = Security::getRemainingLockoutTime($email);
            return ['success' => false, 'message' => "Too many failed attempts. Try again in " . ceil($remaining/60) . " minute(s)."];
        }

        $sanitizedEmail = Security::sanitizeEmail($email);
        if (!$sanitizedEmail) {
            return ['success' => false, 'message' => 'Invalid email address.'];
        }

        try {
            $stmt = db()->prepare("SELECT id, name, email, password, role, is_active FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$sanitizedEmail]);
            $user = $stmt->fetch();

            if (!$user || !Security::verifyPassword($password, $user['password'])) {
                Security::recordLoginAttempt($email);
                return ['success' => false, 'message' => 'Invalid email or password.'];
            }

            if (!$user['is_active']) {
                return ['success' => false, 'message' => 'Your account has been deactivated. Contact an administrator.'];
            }

            Security::resetLoginAttempts($email);
            Security::regenerateSession();

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email']= $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time']= time();

            // Update last login
            db()->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

            return ['success' => true, 'role' => $user['role']];
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'A system error occurred. Please try again.'];
        }
    }

    public static function logout(): void {
        Security::startSecureSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: ../index.php');
        exit;
    }

    public static function isLoggedIn(): bool {
        Security::startSecureSession();
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) return false;
        // Session timeout check
        if (time() - ($_SESSION['login_time'] ?? 0) > SESSION_LIFETIME) {
            self::logout();
        }
        return true;
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    public static function requireRole(string ...$roles): void {
        self::requireLogin();
        if (!in_array($_SESSION['user_role'] ?? '', $roles)) {
            header('Location: ' . BASE_URL . '/dashboard.php?error=unauthorized');
            exit;
        }
    }

    public static function currentUser(): array {
        Security::startSecureSession();
        return [
            'id'    => $_SESSION['user_id']    ?? null,
            'name'  => $_SESSION['user_name']  ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role'  => $_SESSION['user_role']  ?? '',
        ];
    }

    public static function isAdmin(): bool {
        return ($_SESSION['user_role'] ?? '') === 'admin';
    }
}
