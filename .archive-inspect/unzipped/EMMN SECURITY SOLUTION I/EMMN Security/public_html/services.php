<?php
require_once __DIR__ . '/includes/config.php';
include 'includes/header.php';
?>
<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-cogs" style="color: var(--emmn-gold, #c9a227);"></i> Our Services</h1>
    </div>
    <div class="services-grid">
        <div class="service-card" id="assessment">
            <i class="fas fa-clipboard-check" style="color: var(--emmn-gold, #c9a227); font-size: 2.5rem;"></i>
            <h3>Supplier Risk Assessment</h3>
            <p>Comprehensive evaluation of supplier cyber security practices including data protection, access controls, and incident response capabilities.</p>
            <a href="client/register.php" class="btn-gold">Get Started</a>
        </div>
        <div class="service-card" id="reporting">
            <i class="fas fa-file-pdf" style="color: var(--emmn-gold, #c9a227); font-size: 2.5rem;"></i>
            <h3>Risk Reporting</h3>
            <p>Generate detailed reports including supplier risk scores, monthly summaries, and high-risk supplier lists with actionable insights.</p>
            <a href="client/register.php" class="btn-gold">Get Started</a>
        </div>
        <div class="service-card" id="analytics">
            <i class="fas fa-chart-pie" style="color: var(--emmn-gold, #c9a227); font-size: 2.5rem;"></i>
            <h3>Analytics Dashboard</h3>
            <p>Real-time dashboards with overall statistics, risk trends, and compliance monitoring for your entire supply chain.</p>
            <a href="client/login.php" class="btn-gold">View Dashboard</a>
        </div>
        <div class="service-card">
            <i class="fas fa-envelope" style="color: var(--emmn-gold, #c9a227); font-size: 2.5rem;"></i>
            <h3>Email Notifications</h3>
            <p>Automated alerts and reports sent directly to your inbox using our integrated contact system.</p>
            <a href="contacts.php" class="btn-gold">Contact Us</a>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
