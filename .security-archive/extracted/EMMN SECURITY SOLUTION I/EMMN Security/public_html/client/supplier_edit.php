<?php
require_once dirname(__DIR__) . '/includes/config.php';
if (!isLoggedIn() || !isClient()) redirect('client/login.php');

$userId = (int)$_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);
$s = dbFetchOne('SELECT * FROM suppliers WHERE id = ? AND client_id = ?', [$id, $userId]);
if (!$s) redirect('client/suppliers.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $service = sanitize($_POST['service'] ?? '');
    $email = sanitize($_POST['contact_email'] ?? '');
    $phone = sanitize($_POST['contact_phone'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    if ($name === '') {
        $error = 'Supplier name is required.';
    } else {
        dbUpdate('suppliers', [
            'name' => $name,
            'service' => $service,
            'contact_email' => $email ?: null,
            'contact_phone' => $phone ?: null,
            'notes' => $notes ?: null,
        ], 'id = ? AND client_id = ?', [$id, $userId]);
        redirect('client/suppliers.php');
    }
    $s = array_merge($s, $_POST);
}
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<div class="container">
    <div class="page-title-row">
        <h1>Edit Supplier</h1>
        <a href="suppliers.php" class="btn-outline-gold">← Back</a>
    </div>
    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
    <div class="card" style="max-width:640px">
        <form method="POST">
            <div class="form-group">
                <label>Supplier Name *</label>
                <input type="text" name="name" required value="<?php echo e($s['name']); ?>">
            </div>
            <div class="form-group">
                <label>Service / Category</label>
                <input type="text" name="service" value="<?php echo e($s['service']); ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="email" name="contact_email" value="<?php echo e($s['contact_email']); ?>">
                </div>
                <div class="form-group">
                    <label>Contact Phone</label>
                    <input type="text" name="contact_phone" value="<?php echo e($s['contact_phone']); ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3"><?php echo e($s['notes']); ?></textarea>
            </div>
            <button type="submit" class="btn-gold">Update Supplier</button>
        </form>
    </div>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
