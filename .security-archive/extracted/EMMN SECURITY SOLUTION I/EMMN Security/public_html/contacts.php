<?php
require_once __DIR__ . '/includes/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in name, email, and message.';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email.';
    } else {
        $id = dbInsert('messages', [
            'name' => $name,
            'email' => $email,
            'subject' => $subject ?: 'Website contact',
            'message' => $message,
            'status' => 'unread',
        ]);
        if ($id) {
            $success = 'Thank you! Your message has been sent.';
        } else {
            $error = 'Could not send message. Please try again.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-envelope" style="color: var(--emmn-gold, #c9a227);"></i> Contact Us</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="contact-grid">
        <div class="contact-info card">
            <h2>Get in Touch</h2>
            <div class="contact-details">
                <p><i class="fas fa-phone" style="color: var(--emmn-gold, #c9a227);"></i> <?php echo CONTACT_PHONE_1; ?></p>
                <p><i class="fas fa-phone" style="color: var(--emmn-gold, #c9a227);"></i> <?php echo CONTACT_PHONE_2; ?></p>
                <p><i class="fas fa-phone" style="color: var(--emmn-gold, #c9a227);"></i> <?php echo CONTACT_PHONE_3; ?></p>
                <p><i class="fas fa-phone" style="color: var(--emmn-gold, #c9a227);"></i> <?php echo CONTACT_PHONE_4; ?></p>
                <p><i class="fas fa-envelope" style="color: var(--emmn-gold, #c9a227);"></i> <?php echo CONTACT_EMAIL; ?></p>
                <p><i class="fas fa-map-marker-alt" style="color: var(--emmn-gold, #c9a227);"></i> <?php echo CONTACT_ADDRESS; ?></p>
            </div>
        </div>

        <div class="contact-form card">
            <h2>Send Message</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" placeholder="Your name" required value="<?php echo e($_POST['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Your email" required value="<?php echo e($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" placeholder="Subject" value="<?php echo e($_POST['subject'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="4" placeholder="Your message" required><?php echo e($_POST['message'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn-gold"><i class="fas fa-paper-plane"></i> Send</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
