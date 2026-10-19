<?php
if (!defined('EMMN_SECURITY')) {
    require_once __DIR__ . '/config.php';
}
$base = defined('SITE_URL') ? SITE_URL : '/';
$loggedIn = function_exists('isLoggedIn') && isLoggedIn();
$isAdminUser = $loggedIn && function_exists('isAdmin') && isAdmin();
$isClientUser = $loggedIn && function_exists('isClient') && isClient();
$userName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('SITE_NAME') ? e(SITE_NAME) : 'EMMN Security Solutions'; ?> - Supply Chain Risk Checker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo-area">
                <a href="<?php echo $base; ?>index.php" class="logo-link">
                    <div class="logo">
                        <i class="fas fa-shield-alt" style="font-size: 1.8rem; color: var(--emmn-gold, #c9a227);"></i>
                    </div>
                    <div class="brand">EMMN <span>Security</span></div>
                </a>
            </div>

            <button class="nav-toggle" id="navToggle" aria-label="Menu" type="button">
                <i class="fas fa-bars"></i>
            </button>

            <div class="nav-links" id="navLinks">
                <a href="<?php echo $base; ?>index.php"><i class="fas fa-home"></i> Home</a>
                <a href="<?php echo $base; ?>about.php"><i class="fas fa-info-circle"></i> About</a>

                <!-- Services dropdown -->
                <div class="dropdown">
                    <button class="dropdown-toggle" type="button">
                        <i class="fas fa-cogs"></i> Services <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?php echo $base; ?>services.php"><i class="fas fa-list"></i> All Services</a>
                        <a href="<?php echo $base; ?>services.php#assessment"><i class="fas fa-clipboard-check"></i> Risk Assessment</a>
                        <a href="<?php echo $base; ?>services.php#reporting"><i class="fas fa-file-pdf"></i> Risk Reporting</a>
                        <a href="<?php echo $base; ?>services.php#analytics"><i class="fas fa-chart-pie"></i> Analytics</a>
                    </div>
                </div>

                <a href="<?php echo $base; ?>contacts.php"><i class="fas fa-envelope"></i> Contact</a>

                <?php if ($isAdminUser): ?>
                <!-- Admin menu dropdown -->
                <div class="dropdown">
                    <button class="dropdown-toggle btn-outline-gold" type="button">
                        <i class="fas fa-user-cog"></i> Admin <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?php echo $base; ?>admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        <a href="<?php echo $base; ?>admin/client.php"><i class="fas fa-users"></i> Clients</a>
                        <a href="<?php echo $base; ?>admin/suppliers.php"><i class="fas fa-building"></i> Suppliers</a>
                        <a href="<?php echo $base; ?>admin/assessments.php"><i class="fas fa-clipboard-list"></i> Assessments</a>
                        <a href="<?php echo $base; ?>admin/message.php"><i class="fas fa-envelope"></i> Messages</a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo $base; ?>admin/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>

                <?php elseif ($isClientUser): ?>
                <!-- Client menu dropdown -->
                <div class="dropdown">
                    <button class="dropdown-toggle btn-outline-gold" type="button">
                        <i class="fas fa-user"></i> <?php echo e(explode(' ', $userName)[0]); ?> <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?php echo $base; ?>client/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        <a href="<?php echo $base; ?>client/suppliers.php"><i class="fas fa-building"></i> My Suppliers</a>
                        <a href="<?php echo $base; ?>client/supplier_add.php"><i class="fas fa-plus"></i> Add Supplier</a>
                        <a href="<?php echo $base; ?>client/assessments.php"><i class="fas fa-clipboard-list"></i> Assessments</a>
                        <a href="<?php echo $base; ?>client/assess_new.php"><i class="fas fa-tasks"></i> New Assessment</a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo $base; ?>client/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>

                <?php else: ?>
                <!-- Guest: Client dropdown only (no Admin on public site) -->
                <div class="dropdown">
                    <button class="dropdown-toggle btn-outline-gold" type="button">
                        <i class="fas fa-user"></i> Client <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?php echo $base; ?>client/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="<?php echo $base; ?>client/register.php"><i class="fas fa-user-plus"></i> Register</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main>
