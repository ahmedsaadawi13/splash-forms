<!-- FILE: /app/views/settings/index.php -->
<?php ob_start(); ?>

<div class="page-header">
    <h1>Settings</h1>
</div>

<div class="tabs">
    <button class="tab-btn active" onclick="showTab('general')">General</button>
    <button class="tab-btn" onclick="showTab('subscription')">Subscription</button>
    <button class="tab-btn" onclick="showTab('api')">API Keys</button>
    <button class="tab-btn" onclick="showTab('users')">Users</button>
</div>

<div id="general" class="tab-content active">
    <div class="card">
        <div class="card-header">
            <h3>Organization Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="/settings/tenant">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="name">Organization Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($tenant['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="domain">Custom Domain (optional)</label>
                    <input type="text" id="domain" name="domain" class="form-control" value="<?php echo htmlspecialchars($tenant['domain'] ?? ''); ?>" placeholder="forms.yourdomain.com">
                    <small class="text-muted">Configure a custom domain for your forms</small>
                </div>

                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</div>

<div id="subscription" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h3>Current Subscription</h3>
        </div>
        <div class="card-body">
            <?php if ($subscription): ?>
                <h2><?php echo htmlspecialchars($subscription['name']); ?></h2>
                <p class="text-muted">$<?php echo number_format($subscription['price'], 2); ?> / <?php echo $subscription['billing_period']; ?></p>

                <h4>Plan Limits</h4>
                <ul>
                    <li>Forms: <?php echo $subscription['max_forms']; ?></li>
                    <li>Submissions per month: <?php echo number_format($subscription['max_submissions_per_month']); ?></li>
                    <li>Max file size: <?php echo $subscription['max_file_size_mb']; ?> MB</li>
                </ul>

                <h4>Features</h4>
                <ul>
                    <?php
                    $features = json_decode($subscription['features'], true);
                    foreach ($features as $feature):
                    ?>
                        <li><?php echo ucwords(str_replace('_', ' ', $feature)); ?></li>
                    <?php endforeach; ?>
                </ul>

                <p class="text-muted">
                    <?php if ($subscription['expires_at']): ?>
                        Expires: <?php echo date('F d, Y', strtotime($subscription['expires_at'])); ?>
                    <?php else: ?>
                        No expiration
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <p class="text-muted">No active subscription</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="api" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h3>API Keys</h3>
        </div>
        <div class="card-body">
            <?php if (Session::hasFlash('new_api_key')): ?>
                <div class="alert alert-success">
                    <p><strong>Your new API key:</strong></p>
                    <code><?php echo htmlspecialchars(Session::getFlash('new_api_key')); ?></code>
                    <p class="text-muted mt-2">Make sure to copy this key now. You won't be able to see it again!</p>
                </div>
            <?php endif; ?>

            <form method="POST" action="/settings/api-keys" class="mb-3">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">
                <div class="input-group">
                    <input type="text" name="name" class="form-control" placeholder="Key name (optional)">
                    <button type="submit" class="btn btn-primary">Generate New Key</button>
                </div>
            </form>

            <?php if (empty($apiKeys)): ?>
                <p class="text-muted">No API keys yet</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Key</th>
                            <th>Last Used</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($apiKeys as $key): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($key['name'] ?? 'Unnamed'); ?></td>
                                <td><code><?php echo substr($key['api_key'], 0, 20); ?>...</code></td>
                                <td><?php echo $key['last_used_at'] ? date('M d, Y', strtotime($key['last_used_at'])) : 'Never'; ?></td>
                                <td>
                                    <form method="POST" action="/settings/api-keys/<?php echo $key['id']; ?>/revoke" style="display:inline;">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Revoke this API key?')">Revoke</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="users" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h3>Team Members</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="/settings/users" class="mb-3">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

                <div class="form-grid">
                    <input type="text" name="name" class="form-control" placeholder="Name" required>
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <select name="role" class="form-control" required>
                        <option value="user">User</option>
                        <option value="staff">Staff</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><span class="badge"><?php echo ucfirst($u['role']); ?></span></td>
                            <td>
                                <?php if ($u['id'] != $user['id']): ?>
                                    <form method="POST" action="/settings/users/<?php echo $u['id']; ?>/delete" style="display:inline;">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    const contents = document.querySelectorAll('.tab-content');
    const buttons = document.querySelectorAll('.tab-btn');

    contents.forEach(content => content.classList.remove('active'));
    buttons.forEach(btn => btn.classList.remove('active'));

    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}
</script>

<?php
$content = ob_get_clean();
$title = 'Settings - SplashForms';
require __DIR__ . '/../layouts/app.php';
?>
