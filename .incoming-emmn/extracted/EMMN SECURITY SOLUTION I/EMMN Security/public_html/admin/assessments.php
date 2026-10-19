<?php
require_once dirname(__DIR__) . '/includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect('admin/login.php');

$rows = dbFetchAll(
    "SELECT a.*, s.name AS supplier_name, u.full_name AS client_name
     FROM assessments a
     JOIN suppliers s ON s.id = a.supplier_id
     LEFT JOIN users u ON u.id = a.client_id
     ORDER BY a.id DESC"
);
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<div class="container">
    <div class="page-title-row">
        <h1><i class="fas fa-clipboard-list" style="color:var(--emmn-gold,#c9a227)"></i> All Assessments</h1>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Client</th>
                        <th>Score</th>
                        <th>Risk</th>
                        <th>Breakdown</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="6">No assessments yet.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><?php echo e(formatDate($r['created_at'])); ?></td>
                        <td><?php echo e($r['supplier_name']); ?></td>
                        <td><?php echo e($r['client_name'] ?: '—'); ?></td>
                        <td>
                            <?php echo (int)$r['overall_score']; ?>
                            <div class="risk-meter"><div class="risk-meter-fill" style="width:<?php echo (int)$r['overall_score']; ?>%;background:<?php echo getRiskColor($r['risk_level']); ?>"></div></div>
                        </td>
                        <td><span class="badge <?php echo strtolower($r['risk_level']); ?>"><?php echo e($r['risk_level']); ?></span></td>
                        <td><small style="color:#aaa">S<?php echo (int)$r['security_score']; ?> D<?php echo (int)$r['data_score']; ?> A<?php echo (int)$r['access_score']; ?> R<?php echo (int)$r['response_score']; ?> T<?php echo (int)$r['third_party_score']; ?></small></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
