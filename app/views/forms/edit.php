<!-- FILE: /app/views/forms/edit.php -->
<?php ob_start(); ?>

<div class="page-header">
    <h1>Form Settings: <?php echo htmlspecialchars($form['name']); ?></h1>
    <div class="btn-group">
        <a href="/forms" class="btn">Back to Forms</a>
        <a href="/forms/<?php echo $form['id']; ?>/builder" class="btn btn-primary">Edit Builder</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>General Settings</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/forms/<?php echo $form['id']; ?>">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

            <div class="form-group">
                <label for="name">Form Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($form['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($form['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Form URL</label>
                <div class="input-group">
                    <span class="input-addon"><?php echo APP_URL; ?>/f/</span>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($form['slug']); ?>" readonly>
                </div>
            </div>

            <h4>Submission Settings</h4>

            <div class="form-group">
                <label for="notification_email">Notification Email</label>
                <input type="email" id="notification_email" name="settings[notification_email]" class="form-control" value="<?php echo htmlspecialchars($form['settings']['notification_email'] ?? ''); ?>">
                <small class="text-muted">Receive email notifications for new submissions</small>
            </div>

            <div class="form-group">
                <label for="success_message">Success Message</label>
                <textarea id="success_message" name="settings[success_message]" class="form-control" rows="2"><?php echo htmlspecialchars($form['settings']['success_message'] ?? 'Thank you for your submission!'); ?></textarea>
            </div>

            <div class="form-group">
                <label for="redirect_url">Redirect URL (optional)</label>
                <input type="url" id="redirect_url" name="settings[redirect_url]" class="form-control" value="<?php echo htmlspecialchars($form['settings']['redirect_url'] ?? ''); ?>">
                <small class="text-muted">Redirect users after form submission</small>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="settings[enable_spam_protection]" value="1" <?php echo !empty($form['settings']['enable_spam_protection']) ? 'checked' : ''; ?>>
                    Enable spam protection
                </label>
            </div>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3>Danger Zone</h3>
    </div>
    <div class="card-body">
        <div class="danger-actions">
            <div>
                <h4>Toggle Form Status</h4>
                <p>Make this form <?php echo $form['is_active'] ? 'inactive' : 'active'; ?></p>
                <form method="POST" action="/forms/<?php echo $form['id']; ?>/toggle" style="display:inline;">
                    <button type="submit" class="btn btn-warning">
                        <?php echo $form['is_active'] ? 'Deactivate' : 'Activate'; ?>
                    </button>
                </form>
            </div>
            <div>
                <h4>Delete Form</h4>
                <p>Permanently delete this form and all its submissions</p>
                <form method="POST" action="/forms/<?php echo $form['id']; ?>/delete" onsubmit="return confirm('Are you sure? This cannot be undone.')">
                    <button type="submit" class="btn btn-danger">Delete Form</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Edit Form - SplashForms';
require __DIR__ . '/../layouts/app.php';
?>
