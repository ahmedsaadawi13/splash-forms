<!-- FILE: /app/views/submissions/index.php -->
<?php ob_start(); ?>

<div class="page-header">
    <h1>All Submissions</h1>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($submissions)): ?>
            <div class="empty-state">
                <h3>No submissions yet</h3>
                <p>Submissions will appear here when someone fills out your forms</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Form</th>
                        <th>Status</th>
                        <th>IP Address</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td>
                                <a href="/forms/<?php echo $submission['form_id']; ?>/submissions">
                                    <?php echo htmlspecialchars($submission['form_name']); ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-<?php
                                    echo $submission['status'] === 'new' ? 'primary' :
                                        ($submission['status'] === 'spam' ? 'danger' : 'secondary');
                                ?>">
                                    <?php echo ucfirst($submission['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($submission['ip_address']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($submission['created_at'])); ?></td>
                            <td>
                                <a href="/submissions/<?php echo $submission['id']; ?>" class="btn btn-sm">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($pagination['last_page'] > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Submissions - SplashForms';
require __DIR__ . '/../layouts/app.php';
?>
