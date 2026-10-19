<?php
require_once dirname(__DIR__) . '/includes/config.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    }
    redirect('client/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } elseif (!canAttemptLogin($username)) {
        $error = 'Too many failed attempts. Please try again later.';
    } else {
        $user = getUserByUsernameOrEmail($username);
        
        if ($user && verifyPassword($password, $user['password'])) {
            if ($user['role'] !== 'client') {
                $error = 'Please use the Admin login for admin accounts.';
            } else {
                createSecureSession($user);
                clearLoginAttempts($username);
                logActivity($user['id'], 'LOGIN', 'Client logged in');
                redirect('client/dashboard.php');
            }
        } else {
            logFailedLogin($username);
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1><i class="fas fa-shield-alt"></i> <?php echo SITE_NAME; ?></h1>
            <h2>Client Login</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username or Email" required value="<?php echo e($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <p style="margin-top: 15px;">Don't have an account? <a href="register.php" style="color: var(--emmn-gold, #c9a227);">Register</a></p>
            <p style="margin-top: 10px;"><a href="<?php echo SITE_URL; ?>index.php" style="color: #999;">← Back to Home</a></p>
        </div>
    </div>
</body>
</html>
