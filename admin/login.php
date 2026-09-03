<?php
/**
 * Admin Login Page
 * BongoStore BD
 */
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to admin dashboard
if (is_admin_logged_in()) {
    header("Location: /admin/index.php");
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'অনুগ্রহ করে ইউজারনেম ও পাসওয়ার্ড প্রদান করুন।';
    } else {
        $pdo = get_db_connection();
        try {
            // Support both admin_users and admins table
            try {
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $admin = $stmt->fetch();
            } catch (Exception $e1) {
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $admin = $stmt->fetch();
            }

            if ($admin && password_verify($password, $admin['password'])) {
                // Login success
                $_SESSION['admin_user'] = [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'name' => $admin['name'] ?? $admin['full_name'] ?? 'Admin',
                    'role' => $admin['role'] ?? 'admin'
                ];
                set_flash('success', 'স্বাগতম! আপনি সফলভাবে অ্যাডমিন প্যানেলে প্রবেশ করেছেন।');
                header("Location: /admin/index.php");
                exit;
            } else {
                $error = 'ইউজারনেম অথবা পাসওয়ার্ড সঠিক নয়!';
            }
        } catch (Exception $e) {
            $error = 'লগইন ব্যর্থ হয়েছে: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন লগইন | Arif Shikder IT Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="background: linear-gradient(135deg, #0f172a 0%, #064e3b 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">

<div style="background: #ffffff; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); width: 100%; max-width: 440px; padding: 40px 30px; border: 1px solid rgba(255,255,255,0.2);">
    <!-- Brand Icon -->
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 60px; height: 60px; background: var(--primary); color: #fff; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 12px; box-shadow: 0 4px 12px rgba(4,120,87,0.3);">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h2 style="font-size: 1.5rem; color: var(--secondary); margin-bottom: 4px;">অ্যাডমিন কন্ট্রোল প্যানেল</h2>
        <p style="color: var(--text-muted); font-size: 0.88rem;">Arif Shikder IT Services কন্ট্রোল সিস্টেম</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px; font-size: 0.88rem;">
            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="/admin/login.php" method="POST">
        <div class="form-group">
            <label class="form-label">ইউজারনেম অথবা ইমেইল</label>
            <div style="position: relative;">
                <input type="text" name="username" class="form-control" placeholder="যেমন: admin" value="<?= htmlspecialchars($username) ?>" required autofocus>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label">পাসওয়ার্ড</label>
            <div style="position: relative;">
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn-checkout" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem;">
            <i class="fa-solid fa-right-to-bracket"></i> লগইন করুন
        </button>
    </form>

    <!-- Demo Credentials Box -->
    <div style="margin-top: 24px; padding: 14px; background: #ecfdf5; border: 1px dashed #059669; border-radius: var(--radius-md); font-size: 0.84rem;">
        <div style="font-weight: 700; color: #065f46; margin-bottom: 4px;">
            <i class="fa-solid fa-key"></i> ডিফল্ট ডেমো ক্রেডেনশিয়াল:
        </div>
        <div style="color: #047857;">
            ইউজারনেম: <strong>admin</strong><br>
            পাসওয়ার্ড: <strong>admin123</strong>
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="/" style="color: var(--text-muted); font-size: 0.85rem; text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> মূল ওয়েবসাইটে ফিরে যান
        </a>
    </div>
</div>

</body>
</html>
