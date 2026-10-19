<?php
require_once dirname(__DIR__) . '/includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('admin/login.php');
}

if (isset($_GET['read'])) {
    dbUpdate('messages', ['status' => 'read'], 'id = ?', [(int)$_GET['read']]);
}

if (isset($_GET['view'])) {
    $msg = dbFetchOne('SELECT * FROM messages WHERE id = ?', [(int)$_GET['view']]);
} else {
    $msg = null;
}

$messages = dbFetchAll("SELECT * FROM messages ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include dirname(__DIR__) . '/includes/header.php'; ?>
    
    <section class="admin-section">
        <div class="container">
            <h2>Contact Messages</h2>

            <?php if ($msg): ?>
            <div class="card" style="margin-bottom:20px;">
                <h3><?php echo e($msg['subject'] ?: 'No subject'); ?></h3>
                <p><strong>From:</strong> <?php echo e($msg['name']); ?> &lt;<?php echo e($msg['email']); ?>&gt;</p>
                <p><strong>Date:</strong> <?php echo e(formatDateTime($msg['created_at'])); ?></p>
                <p style="margin-top:12px; white-space:pre-wrap;"><?php echo e($msg['message']); ?></p>
                <p style="margin-top:12px;"><a href="message.php" class="btn-sm">Close</a></p>
            </div>
            <?php endif; ?>
            
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr><td colspan="7">No messages yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($messages as $i => $m): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo e($m['name']); ?></td>
                                <td><?php echo e($m['email']); ?></td>
                                <td><?php echo e($m['subject'] ?: '—'); ?></td>
                                <td><span class="badge <?php echo $m['status'] === 'unread' ? 'high' : 'low'; ?>"><?php echo e($m['status']); ?></span></td>
                                <td><?php echo e(formatDate($m['created_at'])); ?></td>
                                <td>
                                    <a href="?view=<?php echo (int)$m['id']; ?>&read=<?php echo (int)$m['id']; ?>" class="btn-sm">View</a>
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
