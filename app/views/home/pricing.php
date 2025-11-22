<!-- FILE: /app/views/home/pricing.php -->
<?php ob_start(); ?>

<section class="pricing">
    <div class="container">
        <div class="text-center mb-5">
            <h1>Simple, Transparent Pricing</h1>
            <p class="text-muted">Choose the plan that fits your needs</p>
        </div>

        <div class="pricing-grid">
            <?php foreach ($plans as $plan): ?>
                <div class="pricing-card <?php echo $plan['slug'] === 'professional' ? 'featured' : ''; ?>">
                    <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
                    <div class="price">
                        <span class="amount">$<?php echo number_format($plan['price'], 0); ?></span>
                        <span class="period">/<?php echo $plan['billing_period']; ?></span>
                    </div>

                    <ul class="features-list">
                        <li><?php echo $plan['max_forms']; ?> Forms</li>
                        <li><?php echo number_format($plan['max_submissions_per_month']); ?> Submissions/month</li>
                        <li><?php echo $plan['max_file_size_mb']; ?>MB File Uploads</li>
                        <?php
                        $features = json_decode($plan['features'], true);
                        foreach ($features as $feature):
                        ?>
                            <li><?php echo ucwords(str_replace('_', ' ', $feature)); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <a href="/register" class="btn <?php echo $plan['slug'] === 'professional' ? 'btn-primary' : ''; ?> btn-block">
                        Get Started
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
$title = 'Pricing - SplashForms';
require __DIR__ . '/../layouts/guest.php';
?>
