<?php
require_once dirname(__DIR__) . '/includes/config.php';
if (!isLoggedIn() || !isClient()) redirect('client/login.php');

$userId = (int)$_SESSION['user_id'];
$total = dbCount('suppliers', 'client_id = ?', [$userId]);
$high = dbCount('suppliers', "client_id = ? AND risk_level IN ('High','Critical')", [$userId]);
$assessed = dbCount('assessments', 'client_id = ?', [$userId]);
$recent = dbFetchAll(
    "SELECT s.*, (SELECT overall_score FROM assessments a WHERE a.supplier_id = s.id ORDER BY a.id DESC LIMIT 1) AS last_score
     FROM suppliers s WHERE s.client_id = ? ORDER BY s.id DESC LIMIT 8",
    [$userId]
);
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<div class="container">
    <div class="page-title-row">
        <div>
            <h1>Client Dashboard</h1>
            <p style="color:#aaa">Welcome, <?php echo e($_SESSION['full_name'] ?? ''); ?><?php if (!empty($_SESSION['company_name'])): ?> · <?php echo e($_SESSION['company_name']); ?><?php endif; ?></p>
        </div>
        <div class="action-bar">
            <a href="supplier_add.php" class="btn-gold"><i class="fas fa-plus"></i> Add Supplier</a>
            <a href="assess_new.php" class="btn-blue"><i class="fas fa-tasks"></i> New Assessment</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="number"><?php echo $total; ?></div><div class="label">My Suppliers</div></div>
        <div class="stat-card"><div class="number"><?php echo $high; ?></div><div class="label">High / Critical</div></div>
        <div class="stat-card"><div class="number"><?php echo $assessed; ?></div><div class="label">Assessments</div></div>
        <div class="stat-card"><div class="number"><?php echo $total ? round((($total - $high) / $total) * 100) : 0; ?>%</div><div class="label">Lower Risk Rate</div></div>
    </div>

    <div class="card">
        <h2><i class="fas fa-building" style="color:var(--emmn-gold,#c9a227)"></i> Recent Suppliers</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Supplier</th><th>Service</th><th>Risk</th><th>Score</th><th></th></tr></thead>
                <tbody>
                <?php if (empty($recent)): ?>
                    <tr><td colspan="5">No suppliers yet. Start by adding one.</td></tr>
                <?php else: foreach ($recent as $s): ?>
                    <tr>
                        <td><?php echo e($s['name']); ?></td>
                        <td><?php echo e($s['service']); ?></td>
                        <td><span class="badge <?php echo strtolower($s['risk_level']); ?>"><?php echo e($s['risk_level']); ?></span></td>
                        <td><?php echo (int)$s['risk_score']; ?></td>
                        <td><a class="btn-sm" href="assess_new.php?supplier_id=<?php echo (int)$s['id']; ?>">Assess</a></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
