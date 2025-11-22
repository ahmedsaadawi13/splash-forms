<!-- FILE: /app/views/dashboard/index.php -->
<?php ob_start(); ?>

<div class="dashboard">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p class="text-muted">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $stats['total_forms']; ?></h3>
            <p>Total Forms</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['total_submissions']; ?></h3>
            <p>Total Submissions</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $submissionsThisMonth; ?></h3>
            <p>This Month</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['active_webhooks']; ?></h3>
            <p>Active Webhooks</p>
        </div>
    </div>

    <?php if ($subscription): ?>
    <div class="subscription-info">
        <h3>Current Plan: <?php echo htmlspecialchars($subscription['name']); ?></h3>
        <div class="progress-bar">
            <div class="progress-label">
                Forms: <?php echo $stats['total_forms']; ?> / <?php echo $subscription['max_forms']; ?>
            </div>
            <div class="progress">
                <div class="progress-fill" style="width: <?php echo ($stats['total_forms'] / $subscription['max_forms']) * 100; ?>%"></div>
            </div>
        </div>
        <div class="progress-bar">
            <div class="progress-label">
                Submissions: <?php echo $submissionsThisMonth; ?> / <?php echo $subscription['max_submissions_per_month']; ?>
            </div>
            <div class="progress">
                <div class="progress-fill" style="width: <?php echo ($submissionsThisMonth / $subscription['max_submissions_per_month']) * 100; ?>%"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid-2">
        <div class="card">
            <div class="card-header">
                <h3>Recent Forms</h3>
                <a href="/forms/create" class="btn btn-sm btn-primary">New Form</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentForms)): ?>
                    <p class="text-muted">No forms yet. <a href="/forms/create">Create your first form</a></p>
                <?php else: ?>
                    <ul class="list">
                        <?php foreach ($recentForms as $form): ?>
                            <li>
                                <a href="/forms/<?php echo $form['id']; ?>/builder">
                                    <?php echo htmlspecialchars($form['name']); ?>
                                </a>
                                <span class="badge"><?php echo $form['submissions_count']; ?> submissions</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Recent Submissions</h3>
                <a href="/submissions" class="btn btn-sm">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentSubmissions)): ?>
                    <p class="text-muted">No submissions yet.</p>
                <?php else: ?>
                    <ul class="list">
                        <?php foreach ($recentSubmissions as $submission): ?>
                            <li>
                                <a href="/submissions/<?php echo $submission['id']; ?>">
                                    <?php echo htmlspecialchars($submission['form_name']); ?>
                                </a>
                                <small class="text-muted"><?php echo $submission['created_at']; ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Dashboard - SplashForms';
require __DIR__ . '/../layouts/app.php';
?>
