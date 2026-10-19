<?php
require_once dirname(__DIR__) . '/includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect('admin/login.php');

$msg = '';
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    dbDelete('assessments', 'supplier_id = ?', [$id]);
    dbDelete('suppliers', 'id = ?', [$id]);
    $msg = 'Supplier removed.';
}

$rows = dbFetchAll(
    "SELECT s.*, u.full_name AS client_name, u.company_name
     FROM suppliers s
     LEFT JOIN users u ON u.id = s.client_id
     ORDER BY s.id DESC"
);
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<div class="container">
    <div class="page-title-row">
        <h1><i class="fas fa-building" style="color:var(--emmn-gold,#c9a227)"></i> All Suppliers</h1>
    </div>
    <?php if ($msg): ?><div class="alert alert-success"><?php echo e($msg); ?></div><?php endif; ?>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Service</th>
                        <th>Client</th>
                        <th>Risk</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7">No suppliers in the system.</td></tr>
                <?php else: foreach ($rows as $s): ?>
                    <tr>
                        <td><?php echo e($s['name']); ?></td>
                        <td><?php echo e($s['service']); ?></td>
                        <td><?php echo e($s['client_name'] ?: ($s['client_id'] == 0 ? 'System sample' : '—')); ?></td>
                        <td><span class="badge <?php echo strtolower($s['risk_level']); ?>"><?php echo e($s['risk_level']); ?></span></td>
                        <td><?php echo (int)$s['risk_score']; ?></td>
                        <td><?php echo e($s['status']); ?></td>
                        <td>
                            <a class="btn-sm" href="?delete=<?php echo (int)$s['id']; ?>" onclick="return confirm('Delete supplier and its assessments?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
