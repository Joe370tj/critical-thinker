<?php
require_once dirname(__DIR__) . '/includes/config.php';
if (!isLoggedIn() || !isClient()) redirect('client/login.php');

$userId = (int)$_SESSION['user_id'];
$preId = (int)($_GET['supplier_id'] ?? 0);
$suppliers = dbFetchAll('SELECT id, name FROM suppliers WHERE client_id = ? ORDER BY name', [$userId]);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplierId = (int)($_POST['supplier_id'] ?? 0);
    $security = max(0, min(100, (int)($_POST['security_score'] ?? 0)));
    $data = max(0, min(100, (int)($_POST['data_score'] ?? 0)));
    $access = max(0, min(100, (int)($_POST['access_score'] ?? 0)));
    $response = max(0, min(100, (int)($_POST['response_score'] ?? 0)));
    $third = max(0, min(100, (int)($_POST['third_party_score'] ?? 0)));
    $notes = sanitize($_POST['notes'] ?? '');

    $owned = dbFetchOne('SELECT id, name FROM suppliers WHERE id = ? AND client_id = ?', [$supplierId, $userId]);
    if (!$owned) {
        $error = 'Select a valid supplier.';
    } else {
        $overall = calculateRiskScore($security, $data, $access, $response, $third);
        $level = calculateRiskLevel($overall);
        $aid = dbInsert('assessments', [
            'supplier_id' => $supplierId,
            'client_id' => $userId,
            'security_score' => $security,
            'data_score' => $data,
            'access_score' => $access,
            'response_score' => $response,
            'third_party_score' => $third,
            'overall_score' => $overall,
            'risk_level' => $level,
            'status' => 'final',
            'notes' => $notes ?: null,
        ]);
        dbUpdate('suppliers', [
            'risk_score' => $overall,
            'risk_level' => $level,
            'status' => 'assessed',
        ], 'id = ?', [$supplierId]);
        logActivity($userId, 'ASSESSMENT', "Assessed {$owned['name']} → $level ($overall)");
        $success = "Assessment saved. Risk level: $level (score $overall).";
    }
}
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<div class="container">
    <div class="page-title-row">
        <h1><i class="fas fa-tasks" style="color:var(--emmn-gold,#c9a227)"></i> New Risk Assessment</h1>
        <a href="assessments.php" class="btn-outline-gold">View All</a>
    </div>
    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?> <a href="assessments.php" style="color:var(--emmn-gold)">See assessments</a></div><?php endif; ?>

    <?php if (empty($suppliers)): ?>
        <div class="card"><p>You need a supplier first. <a href="supplier_add.php" style="color:var(--emmn-gold)">Add supplier</a></p></div>
    <?php else: ?>
    <div class="card" style="max-width:700px">
        <p style="color:#aaa;margin-bottom:16px">Score each area from 0 (poor) to 100 (excellent). Overall risk is calculated automatically.</p>
        <form method="POST">
            <div class="form-group">
                <label>Supplier *</label>
                <select name="supplier_id" required>
                    <option value="">— Select —</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?php echo (int)$s['id']; ?>" <?php echo $preId === (int)$s['id'] ? 'selected' : ''; ?>><?php echo e($s['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php
            $factors = [
                'security_score' => 'Security Controls (firewalls, encryption, patching)',
                'data_score' => 'Data Protection (privacy, backups, retention)',
                'access_score' => 'Access Control (identity, MFA, least privilege)',
                'response_score' => 'Incident Response (policies, drills, contacts)',
                'third_party_score' => 'Third-Party Management (their own vendors)',
            ];
            foreach ($factors as $name => $label):
            ?>
            <div class="form-group">
                <label><?php echo e($label); ?></label>
                <div class="score-input">
                    <input type="range" name="<?php echo $name; ?>" min="0" max="100" value="50">
                    <span class="score-val">50</span>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3" placeholder="Findings, evidence, recommendations..."></textarea>
            </div>
            <button type="submit" class="btn-gold"><i class="fas fa-check"></i> Submit Assessment</button>
        </form>
    </div>
    <?php endif; ?>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
