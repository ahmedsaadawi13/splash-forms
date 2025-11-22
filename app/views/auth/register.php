<!-- FILE: /app/views/auth/register.php -->
<?php ob_start(); ?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Create Your Account</h1>
        <p class="text-muted">Start building forms in minutes!</p>

        <form method="POST" action="/register" class="form">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" required autofocus>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="8">
                <small class="text-muted">Minimum 8 characters</small>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="tenant_name">Organization Name</label>
                <input type="text" id="tenant_name" name="tenant_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="plan_id">Select Plan</label>
                <select id="plan_id" name="plan_id" class="form-control" required>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?php echo $plan['id']; ?>">
                            <?php echo htmlspecialchars($plan['name']); ?> -
                            $<?php echo number_format($plan['price'], 2); ?>/<?php echo $plan['billing_period']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>

        <p class="text-center mt-3">
            Already have an account? <a href="/login">Login here</a>
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Register - SplashForms';
require __DIR__ . '/../layouts/guest.php';
?>
