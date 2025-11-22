<!-- FILE: /app/views/layouts/app.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'SplashForms - Form Builder'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="/dashboard">SplashForms</a>
            </div>
            <ul class="navbar-menu">
                <li><a href="/dashboard">Dashboard</a></li>
                <li><a href="/forms">Forms</a></li>
                <li><a href="/submissions">Submissions</a></li>
                <li><a href="/settings">Settings</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></a>
                    <ul class="dropdown-menu">
                        <li><a href="/settings">Settings</a></li>
                        <li><a href="/logout">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">
            <?php if (Session::hasFlash('success')): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars(Session::getFlash('success')); ?>
                </div>
            <?php endif; ?>

            <?php if (Session::hasFlash('error')): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars(Session::getFlash('error')); ?>
                </div>
            <?php endif; ?>

            <?php echo $content ?? ''; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> SplashForms. All rights reserved.</p>
        </div>
    </footer>

    <script src="/assets/js/app.js"></script>
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
