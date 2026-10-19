<?php
require_once dirname(__DIR__) . '/includes/config.php';
if (!isLoggedIn() || !isClient()) redirect('client/login.php');

$userId = (int)$_SESSION['user_id'];
$rows = dbFetchAll(
    "SELECT a.*, s.name AS supplier_name, s.service
     FROM assessments a
     JOIN suppliers s ON s.id = a.supplier_id
     WHERE a.client_id = ?
     ORDER BY a.id DESC",
    [$userId]
);
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<div class="container">
    <div class="page-title-row">
        <h1><i class="fas fa-clipboard-list" style="color:var(--emmn-gold,#c9a227)"></i> My Assessments</h1>
        <a href="assess_new.php" class="btn-gold"><i class="fas fa-plus"></i> New Assessment</a>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Service</th>
                        <th>Overall</th>
                        <th>Risk</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="6">No assessments yet.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><?php echo e(formatDate($r['created_at'])); ?></td>
                        <td><?php echo e($r['supplier_name']); ?></td>
                        <td><?php echo e($r['service']); ?></td>
                        <td>
                            <?php echo (int)$r['overall_score']; ?>
                            <div class="risk-meter"><div class="risk-meter-fill" style="width:<?php echo (int)$r['overall_score']; ?>%;background:<?php echo getRiskColor($r['risk_level']); ?>"></div></div>
                        </td>
                        <td><span class="badge <?php echo strtolower($r['risk_level']); ?>"><?php echo e($r['risk_level']); ?></span></td>
                        <td>
                            <small style="color:#aaa">
                                Sec <?php echo (int)$r['security_score']; ?> ·
                                Data <?php echo (int)$r['data_score']; ?> ·
                                Access <?php echo (int)$r['access_score']; ?> ·
                                IR <?php echo (int)$r['response_score']; ?> ·
                                3P <?php echo (int)$r['third_party_score']; ?>
                            </small>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
