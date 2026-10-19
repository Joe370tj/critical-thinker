<?php
require_once dirname(__DIR__) . '/includes/config.php';
if (!isLoggedIn() || !isClient()) redirect('client/login.php');

$userId = (int) $_SESSION['user_id'];
$msg = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $row = dbFetchOne('SELECT id FROM suppliers WHERE id = ? AND client_id = ?', [$id, $userId]);
    if ($row) {
        dbDelete('assessments', 'supplier_id = ?', [$id]);
        dbDelete('suppliers', 'id = ? AND client_id = ?', [$id, $userId]);
        $msg = 'Supplier deleted.';
    }
}

$suppliers = dbFetchAll('SELECT * FROM suppliers WHERE client_id = ? ORDER BY id DESC', [$userId]);
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<div class="container">
    <div class="page-title-row">
        <h1><i class="fas fa-building" style="color:var(--emmn-gold,#c9a227)"></i> My Suppliers</h1>
        <a href="supplier_add.php" class="btn-gold"><i class="fas fa-plus"></i> Add Supplier</a>
    </div>
    <?php if ($msg): ?><div class="alert alert-success"><?php echo e($msg); ?></div><?php endif; ?>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Service</th>
                        <th>Contact</th>
                        <th>Risk</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr><td colspan="7">No suppliers yet. <a href="supplier_add.php" style="color:var(--emmn-gold)">Add your first supplier</a>.</td></tr>
                <?php else: foreach ($suppliers as $s): ?>
                    <tr>
                        <td><?php echo e($s['name']); ?></td>
                        <td><?php echo e($s['service']); ?></td>
                        <td><?php echo e($s['contact_email'] ?: '—'); ?></td>
                        <td><span class="badge <?php echo strtolower($s['risk_level']); ?>"><?php echo e($s['risk_level']); ?></span></td>
                        <td><?php echo (int)$s['risk_score']; ?></td>
                        <td><?php echo e($s['status']); ?></td>
                        <td>
                            <a class="btn-sm" href="assess_new.php?supplier_id=<?php echo (int)$s['id']; ?>">Assess</a>
                            <a class="btn-sm" href="supplier_edit.php?id=<?php echo (int)$s['id']; ?>">Edit</a>
                            <a class="btn-sm" href="?delete=<?php echo (int)$s['id']; ?>" onclick="return confirm('Delete this supplier?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
