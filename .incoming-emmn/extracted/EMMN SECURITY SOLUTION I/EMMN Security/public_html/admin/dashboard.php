<?php
require_once dirname(__DIR__) . '/includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect('admin/login.php');

$totalClients = dbCount('users', "role = 'client'");
$totalSuppliers = dbCount('suppliers');
$totalAssessments = dbCount('assessments');
$totalMessages = dbCount('messages', "status = 'unread'");
$highRisk = dbCount('suppliers', "risk_level IN ('High', 'Critical')");
$recentSuppliers = dbFetchAll('SELECT * FROM suppliers ORDER BY id DESC LIMIT 6');
$recentMessages = dbFetchAll("SELECT * FROM messages ORDER BY id DESC LIMIT 5");
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<div class="container">
    <div class="page-title-row">
        <div>
            <h1>Admin Dashboard</h1>
            <p style="color:#aaa">Welcome, <?php echo e($_SESSION['full_name'] ?? 'Admin'); ?></p>
        </div>
        <div class="action-bar">
            <a href="client.php" class="btn-gold"><i class="fas fa-users"></i> Clients</a>
            <a href="suppliers.php" class="btn-blue"><i class="fas fa-building"></i> Suppliers</a>
            <a href="message.php" class="btn-outline-gold"><i class="fas fa-envelope"></i> Messages</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="number"><?php echo $totalClients; ?></div><div class="label">Clients</div></div>
        <div class="stat-card"><div class="number"><?php echo $totalSuppliers; ?></div><div class="label">Suppliers</div></div>
        <div class="stat-card"><div class="number"><?php echo $totalAssessments; ?></div><div class="label">Assessments</div></div>
        <div class="stat-card"><div class="number"><?php echo $totalMessages; ?></div><div class="label">Unread Messages</div></div>
        <div class="stat-card"><div class="number"><?php echo $highRisk; ?></div><div class="label">High Risk</div></div>
    </div>

    <div class="card">
        <h2>Recent Suppliers</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Supplier</th><th>Service</th><th>Risk</th><th>Score</th></tr></thead>
                <tbody>
                <?php foreach ($recentSuppliers as $s): ?>
                    <tr>
                        <td><?php echo e($s['name']); ?></td>
                        <td><?php echo e($s['service']); ?></td>
                        <td><span class="badge <?php echo strtolower($s['risk_level']); ?>"><?php echo e($s['risk_level']); ?></span></td>
                        <td><?php echo (int)$s['risk_score']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentSuppliers)): ?><tr><td colspan="4">None yet</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>Latest Messages</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Subject</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($recentMessages as $m): ?>
                    <tr>
                        <td><?php echo e($m['name']); ?></td>
                        <td><?php echo e($m['subject'] ?: '—'); ?></td>
                        <td><span class="badge <?php echo $m['status']==='unread'?'high':'low'; ?>"><?php echo e($m['status']); ?></span></td>
                        <td><?php echo e(formatDate($m['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentMessages)): ?><tr><td colspan="4">No messages</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <p style="margin-top:12px"><a href="message.php" class="btn-sm">View all messages</a></p>
    </div>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
