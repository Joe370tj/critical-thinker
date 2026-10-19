<?php
require_once dirname(__DIR__) . '/includes/config.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'client/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $full_name = sanitize($_POST['full_name'] ?? '');
    $company_name = sanitize($_POST['company_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!isPasswordStrong($password)) {
        $error = 'Password must be at least 8 characters with upper, lower, and a number.';
    } else {
        $exists = dbFetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
        if ($exists) {
            $error = 'Username or email already registered.';
        } else {
            $id = dbInsert('users', [
                'username' => $username,
                'email' => $email,
                'password' => hashPassword($password),
                'full_name' => $full_name,
                'company_name' => $company_name ?: null,
                'phone' => $phone ?: null,
                'role' => 'client',
                'status' => 'active',
            ]);
            if ($id) {
                $success = 'Registration successful! You can now log in.';
            } else {
                $error = 'Registration failed. Please try again.';
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
    <title>Client Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box" style="max-width: 420px;">
            <h1><i class="fas fa-shield-alt"></i> <?php echo SITE_NAME; ?></h1>
            <h2>Client Registration</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo e($success); ?></div>
                <p><a href="login.php" class="btn-gold">Go to Login</a></p>
            <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="Full Name *" required value="<?php echo e($_POST['full_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <input type="text" name="company_name" placeholder="Company Name" value="<?php echo e($_POST['company_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username *" required value="<?php echo e($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email *" required value="<?php echo e($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <input type="text" name="phone" placeholder="Phone" value="<?php echo e($_POST['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password *" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password *" required>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <?php endif; ?>
            <p style="margin-top: 15px;">Already registered? <a href="login.php" style="color: var(--emmn-gold, #c9a227);">Login</a></p>
        </div>
    </div>
</body>
</html>
