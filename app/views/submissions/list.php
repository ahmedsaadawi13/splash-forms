<!-- FILE: /app/views/submissions/list.php -->
<?php ob_start(); ?>

<div class="page-header">
    <h1>Submissions: <?php echo htmlspecialchars($form['name']); ?></h1>
    <div class="btn-group">
        <a href="/forms" class="btn">Back to Forms</a>
        <a href="/forms/<?php echo $form['id']; ?>/submissions/export" class="btn btn-primary">Export CSV</a>
    </div>
</div>

<div class="filter-bar">
    <a href="/forms/<?php echo $form['id']; ?>/submissions" class="filter-link <?php echo !$currentStatus ? 'active' : ''; ?>">All</a>
    <a href="/forms/<?php echo $form['id']; ?>/submissions?status=new" class="filter-link <?php echo $currentStatus === 'new' ? 'active' : ''; ?>">New</a>
    <a href="/forms/<?php echo $form['id']; ?>/submissions?status=read" class="filter-link <?php echo $currentStatus === 'read' ? 'active' : ''; ?>">Read</a>
    <a href="/forms/<?php echo $form['id']; ?>/submissions?status=spam" class="filter-link <?php echo $currentStatus === 'spam' ? 'active' : ''; ?>">Spam</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($submissions)): ?>
            <div class="empty-state">
                <h3>No submissions yet</h3>
                <p>Share your form to start receiving submissions</p>
                <div class="form-url">
                    <input type="text" class="form-control" value="<?php echo APP_URL; ?>/f/<?php echo $form['slug']; ?>" readonly>
                    <button class="btn btn-sm" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">Copy</button>
                </div>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php foreach (array_slice($form['schema'], 0, 3) as $field): ?>
                            <th><?php echo htmlspecialchars($field['label']); ?></th>
                        <?php endforeach; ?>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <?php $data = json_decode($submission['data'], true); ?>
                        <tr>
                            <td><?php echo $submission['id']; ?></td>
                            <?php foreach (array_slice($form['schema'], 0, 3) as $field): ?>
                                <td>
                                    <?php
                                    $value = $data[$field['name']] ?? '-';
                                    if (is_array($value)) {
                                        $value = implode(', ', $value);
                                    }
                                    echo htmlspecialchars(substr($value, 0, 50));
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            <td>
                                <span class="badge badge-<?php
                                    echo $submission['status'] === 'new' ? 'primary' :
                                        ($submission['status'] === 'spam' ? 'danger' : 'secondary');
                                ?>">
                                    <?php echo ucfirst($submission['status']); ?>
                                </span>
                            </td>
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
                        <a href="?page=<?php echo $i; ?><?php echo $currentStatus ? '&status=' . $currentStatus : ''; ?>" class="page-link <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
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
$title = 'Form Submissions - SplashForms';
require __DIR__ . '/../layouts/app.php';
?>
