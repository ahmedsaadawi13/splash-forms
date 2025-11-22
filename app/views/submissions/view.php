<!-- FILE: /app/views/submissions/view.php -->
<?php ob_start(); ?>

<div class="page-header">
    <h1>Submission #<?php echo $submission['id']; ?></h1>
    <div class="btn-group">
        <a href="/forms/<?php echo $form['id']; ?>/submissions" class="btn">Back to Submissions</a>
        <form method="POST" action="/submissions/<?php echo $submission['id']; ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this submission?')">
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>

<div class="grid-3">
    <div class="col-span-2">
        <div class="card">
            <div class="card-header">
                <h3>Submission Data</h3>
            </div>
            <div class="card-body">
                <table class="table-details">
                    <?php foreach ($form['schema'] as $field): ?>
                        <tr>
                            <th><?php echo htmlspecialchars($field['label']); ?></th>
                            <td>
                                <?php
                                $value = $submission['data'][$field['name']] ?? '-';
                                if (is_array($value)) {
                                    $value = implode(', ', $value);
                                }
                                if ($field['type'] === 'file' && filter_var($value, FILTER_VALIDATE_URL)) {
                                    echo '<a href="' . htmlspecialchars($value) . '" target="_blank">Download File</a>';
                                } else {
                                    echo nl2br(htmlspecialchars($value));
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">
                <h3>Details</h3>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Status</dt>
                    <dd>
                        <span class="badge badge-<?php
                            echo $submission['status'] === 'new' ? 'primary' :
                                ($submission['status'] === 'spam' ? 'danger' : 'secondary');
                        ?>">
                            <?php echo ucfirst($submission['status']); ?>
                        </span>
                    </dd>

                    <dt>IP Address</dt>
                    <dd><?php echo htmlspecialchars($submission['ip_address']); ?></dd>

                    <dt>User Agent</dt>
                    <dd class="text-small"><?php echo htmlspecialchars($submission['user_agent']); ?></dd>

                    <dt>Submitted At</dt>
                    <dd><?php echo date('F d, Y H:i:s', strtotime($submission['created_at'])); ?></dd>
                </dl>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h3>Actions</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/submissions/<?php echo $submission['id']; ?>/status" id="status-form">
                    <div class="form-group">
                        <label>Change Status</label>
                        <select name="status" class="form-control" onchange="document.getElementById('status-form').submit()">
                            <option value="new" <?php echo $submission['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="read" <?php echo $submission['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                            <option value="spam" <?php echo $submission['status'] === 'spam' ? 'selected' : ''; ?>>Spam</option>
                            <option value="archived" <?php echo $submission['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'View Submission - SplashForms';
require __DIR__ . '/../layouts/app.php';
?>
