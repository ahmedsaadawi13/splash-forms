<!-- FILE: /app/views/auth/login.php -->
<?php ob_start(); ?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Login to SplashForms</h1>
        <p class="text-muted">Welcome back! Please login to your account.</p>

        <form method="POST" action="/login" class="form">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <p class="text-center mt-3">
            Don't have an account? <a href="/register">Sign up here</a>
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Login - SplashForms';
require __DIR__ . '/../layouts/guest.php';
?>
