<!-- FILE: /app/views/webhooks/index.php -->
<?php ob_start(); ?>

<div class="page-header">
    <h1>Webhooks: <?php echo htmlspecialchars($form['name']); ?></h1>
    <a href="/forms/<?php echo $form['id']; ?>/builder" class="btn">Back to Form</a>
</div>

<div class="card">
    <div class="card-header">
        <h3>Add Webhook</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/forms/<?php echo $form['id']; ?>/webhooks">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

            <div class="form-group">
                <label for="url">Webhook URL</label>
                <input type="url" id="url" name="url" class="form-control" required placeholder="https://example.com/webhook">
                <small class="text-muted">This URL will receive POST requests when the form is submitted</small>
            </div>

            <button type="submit" class="btn btn-primary">Add Webhook</button>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3>Active Webhooks</h3>
    </div>
    <div class="card-body">
        <?php if (empty($webhooks)): ?>
            <p class="text-muted">No webhooks configured for this form</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($webhooks as $webhook): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($webhook['url']); ?></code></td>
                            <td>
                                <span class="badge badge-<?php echo $webhook['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $webhook['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($webhook['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="/webhooks/<?php echo $webhook['id']; ?>/logs" class="btn btn-sm">Logs</a>
                                    <form method="POST" action="/webhooks/<?php echo $webhook['id']; ?>/test" style="display:inline;">
                                        <button type="submit" class="btn btn-sm">Test</button>
                                    </form>
                                    <form method="POST" action="/webhooks/<?php echo $webhook['id']; ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this webhook?')">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
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
$title = 'Webhooks - SplashForms';
require __DIR__ . '/../layouts/app.php';
?>
