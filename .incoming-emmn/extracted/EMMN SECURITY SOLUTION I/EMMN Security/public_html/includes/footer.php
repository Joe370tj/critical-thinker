<?php
// includes/footer.php
?>
    </main>
    <footer>
        <div class="footer-content">
            <div class="footer-info">
                <h3><?php echo defined('COMPANY_NAME') ? COMPANY_NAME : 'EMMN Security Solutions'; ?></h3>
                <p>Supply Chain Security Risk Assessment</p>
                <div class="contact-info">
                    <p><i class="fas fa-phone"></i> <?php echo defined('CONTACT_PHONE_1') ? CONTACT_PHONE_1 : '0816074626'; ?> / <?php echo defined('CONTACT_PHONE_2') ? CONTACT_PHONE_2 : '0816606306'; ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo defined('CONTACT_EMAIL') ? CONTACT_EMAIL : 'emmnsecuritysolution4@gmail.com'; ?></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo defined('COMPANY_NAME') ? COMPANY_NAME : 'EMMN Security Solutions'; ?>. All rights reserved.</p>
                <p class="gold-text"><i class="fas fa-shield-alt"></i> Protecting Your Supply Chain</p>
            </div>
        </div>
    </footer>
    <script src="<?php echo defined('SITE_URL') ? SITE_URL : ''; ?>assets/js/main.js"></script>
</body>
</html>
