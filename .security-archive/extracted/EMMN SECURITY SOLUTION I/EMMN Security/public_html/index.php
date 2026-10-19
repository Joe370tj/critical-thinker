<?php
require_once __DIR__ . '/includes/config.php';

$totalSuppliers = dbCount('suppliers');
$highRisk = dbCount('suppliers', "risk_level IN ('High', 'Critical')");
$totalAssessments = dbCount('assessments');
if ($totalAssessments === 0) {
    $totalAssessments = $totalSuppliers; // demo fallback
}
$compliance = $totalSuppliers > 0
    ? round((($totalSuppliers - $highRisk) / $totalSuppliers) * 100)
    : 0;

$highRiskList = dbFetchAll("SELECT * FROM suppliers WHERE risk_level IN ('High', 'Critical', 'Medium') ORDER BY risk_score ASC LIMIT 5");
?>
<?php include 'includes/header.php'; ?>

<div class="hero-section">
    <div class="container">
        <h1>Supply Chain Security Risk Checker</h1>
        <p class="subtitle">Assess third-party suppliers before business partnerships are established</p>
        <div class="hero-buttons">
            <a href="client/register.php" class="btn-gold"><i class="fas fa-user-plus"></i> Register as Client</a>
            <a href="client/login.php" class="btn-blue"><i class="fas fa-sign-in-alt"></i> Login</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?php echo $totalSuppliers; ?></div>
            <div class="label">Total Suppliers</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $highRisk; ?></div>
            <div class="label">High Risk</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $totalAssessments; ?></div>
            <div class="label">Assessments</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $compliance; ?>%</div>
            <div class="label">Compliance Rate</div>
        </div>
    </div>

    <div class="card">
        <h2><i class="fas fa-exclamation-triangle" style="color: var(--emmn-gold, #c9a227);"></i> High Risk Suppliers</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Service</th>
                        <th>Risk Level</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($highRiskList)): ?>
                        <tr><td colspan="4">No suppliers in database yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($highRiskList as $s): ?>
                        <tr>
                            <td><?php echo e($s['name']); ?></td>
                            <td><?php echo e($s['service']); ?></td>
                            <td><span class="badge <?php echo strtolower($s['risk_level']); ?>"><?php echo e($s['risk_level']); ?></span></td>
                            <td><?php echo (int)$s['risk_score']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
