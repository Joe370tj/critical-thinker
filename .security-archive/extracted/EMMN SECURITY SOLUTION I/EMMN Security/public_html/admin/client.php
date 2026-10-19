<?php
require_once dirname(__DIR__) . '/includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('admin/login.php');
}

$message = '';

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $status = $_GET['action'] === 'activate' ? 'active' : 'inactive';
    dbUpdate('users', ['status' => $status], 'id = ? AND role = ?', [$id, 'client']);
    $message = 'Client ' . ($status === 'active' ? 'activated' : 'deactivated') . ' successfully!';
}

$clients = dbFetchAll("SELECT * FROM users WHERE role = 'client' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include dirname(__DIR__) . '/includes/header.php'; ?>
    
    <section class="admin-section">
        <div class="container">
            <h2>Manage Clients</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo e($message); ?></div>
            <?php endif; ?>
            
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clients)): ?>
                            <tr><td colspan="7">No clients registered yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($clients as $i => $client): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo e($client['full_name']); ?></td>
                                <td><?php echo e($client['company_name'] ?? '—'); ?></td>
                                <td><?php echo e($client['email']); ?></td>
                                <td><?php echo e($client['phone'] ?? '—'); ?></td>
                                <td><span class="badge <?php echo $client['status'] === 'active' ? 'low' : 'high'; ?>"><?php echo e($client['status']); ?></span></td>
                                <td>
                                    <?php if ($client['status'] === 'active'): ?>
                                        <a href="?action=deactivate&id=<?php echo (int)$client['id']; ?>" class="btn-sm">Deactivate</a>
                                    <?php else: ?>
                                        <a href="?action=activate&id=<?php echo (int)$client['id']; ?>" class="btn-sm">Activate</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p style="margin-top:16px;"><a href="dashboard.php" class="btn-outline-gold">← Back to Dashboard</a></p>
        </div>
    </section>
    
    <?php include dirname(__DIR__) . '/includes/footer.php'; ?>
</body>
</html>
