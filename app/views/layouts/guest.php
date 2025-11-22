<!-- FILE: /app/views/layouts/guest.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'SplashForms - Form Builder'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="guest-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="/">SplashForms</a>
            </div>
            <ul class="navbar-menu">
                <li><a href="/pricing">Pricing</a></li>
                <li><a href="/docs">Docs</a></li>
                <li><a href="/login">Login</a></li>
                <li><a href="/register" class="btn btn-primary">Sign Up</a></li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <?php if (Session::hasFlash('success')): ?>
            <div class="container">
                <div class="alert alert-success">
                    <?php echo htmlspecialchars(Session::getFlash('success')); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (Session::hasFlash('error')): ?>
            <div class="container">
                <div class="alert alert-error">
                    <?php echo htmlspecialchars(Session::getFlash('error')); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php echo $content ?? ''; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> SplashForms. All rights reserved.</p>
        </div>
    </footer>

    <script src="/assets/js/app.js"></script>
</body>
</html>
