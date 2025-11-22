<!-- FILE: /app/views/home/index.php -->
<?php ob_start(); ?>

<section class="hero">
    <div class="container text-center">
        <h1 class="hero-title">Build Beautiful Forms in Minutes</h1>
        <p class="hero-subtitle">Create, customize, and embed forms with our powerful drag-and-drop builder</p>
        <div class="hero-actions">
            <a href="/register" class="btn btn-primary btn-lg">Get Started Free</a>
            <a href="/pricing" class="btn btn-lg">View Pricing</a>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <h2 class="text-center">Features</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <h3>Drag & Drop Builder</h3>
                <p>Easily create forms with our intuitive drag-and-drop interface</p>
            </div>
            <div class="feature-card">
                <h3>Multi-Tenant SaaS</h3>
                <p>Built for teams with complete tenant isolation and management</p>
            </div>
            <div class="feature-card">
                <h3>Webhooks</h3>
                <p>Integrate with your favorite tools using powerful webhooks</p>
            </div>
            <div class="feature-card">
                <h3>CSV Export</h3>
                <p>Export all your submissions to CSV with one click</p>
            </div>
            <div class="feature-card">
                <h3>Spam Protection</h3>
                <p>Built-in spam protection keeps your submissions clean</p>
            </div>
            <div class="feature-card">
                <h3>REST API</h3>
                <p>Powerful API for programmatic access to your forms</p>
            </div>
        </div>
    </div>
</section>

<section class="cta">
    <div class="container text-center">
        <h2>Ready to get started?</h2>
        <p>Join thousands of users building better forms</p>
        <a href="/register" class="btn btn-primary btn-lg">Start Free Trial</a>
    </div>
</section>

<?php
$content = ob_get_clean();
$title = 'SplashForms - Modern Form Builder';
require __DIR__ . '/../layouts/guest.php';
?>
