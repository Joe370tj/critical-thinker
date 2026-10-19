<?php
require_once dirname(__DIR__) . '/includes/config.php';
if (!isLoggedIn() || !isClient()) redirect('client/login.php');

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
        $id = dbInsert('suppliers', [
            'client_id' => (int)$_SESSION['user_id'],
            'name' => $name,
            'service' => $service,
            'contact_email' => $email ?: null,
            'contact_phone' => $phone ?: null,
            'risk_level' => 'Medium',
            'risk_score' => 50,
            'status' => 'pending',
            'notes' => $notes ?: null,
        ]);
        if ($id) {
            logActivity($_SESSION['user_id'], 'SUPPLIER_ADD', "Added supplier: $name");
            redirect('client/suppliers.php');
        }
        $error = 'Could not save supplier.';
    }
}
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<div class="container">
    <div class="page-title-row">
        <h1><i class="fas fa-plus" style="color:var(--emmn-gold,#c9a227)"></i> Add Supplier</h1>
        <a href="suppliers.php" class="btn-outline-gold">← Back</a>
    </div>
    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
    <div class="card" style="max-width:640px">
        <form method="POST">
            <div class="form-group">
                <label>Supplier Name *</label>
                <input type="text" name="name" required value="<?php echo e($_POST['name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Service / Category</label>
                <input type="text" name="service" placeholder="e.g. Cloud Hosting" value="<?php echo e($_POST['service'] ?? ''); ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="email" name="contact_email" value="<?php echo e($_POST['contact_email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Contact Phone</label>
                    <input type="text" name="contact_phone" value="<?php echo e($_POST['contact_phone'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3"><?php echo e($_POST['notes'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn-gold"><i class="fas fa-save"></i> Save Supplier</button>
        </form>
    </div>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
