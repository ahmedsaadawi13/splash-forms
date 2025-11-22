<!-- FILE: /app/views/forms/index.php -->
<?php ob_start(); ?>

<div class="page-header">
    <h1>Forms</h1>
    <a href="/forms/create" class="btn btn-primary">Create New Form</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($forms)): ?>
            <div class="empty-state">
                <h3>No forms yet</h3>
                <p>Create your first form to start collecting submissions</p>
                <a href="/forms/create" class="btn btn-primary">Create Form</a>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Submissions</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form): ?>
                        <tr>
                            <td>
                                <a href="/forms/<?php echo $form['id']; ?>/builder">
                                    <?php echo htmlspecialchars($form['name']); ?>
                                </a>
                            </td>
                            <td><code><?php echo htmlspecialchars($form['slug']); ?></code></td>
                            <td>
                                <a href="/forms/<?php echo $form['id']; ?>/submissions">
                                    <?php echo $form['submissions_count']; ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $form['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $form['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($form['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="/forms/<?php echo $form['id']; ?>/builder" class="btn btn-sm">Edit</a>
                                    <a href="/f/<?php echo $form['slug']; ?>" class="btn btn-sm" target="_blank">View</a>
                                    <a href="/forms/<?php echo $form['id']; ?>/submissions" class="btn btn-sm">Submissions</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Forms - SplashForms';
require __DIR__ . '/../layouts/app.php';
?>
