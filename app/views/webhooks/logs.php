<!-- FILE: /app/views/webhooks/logs.php -->
<?php ob_start(); ?>

<div class="page-header">
    <h1>Webhook Logs</h1>
    <a href="/forms/<?php echo $webhook['form_id']; ?>/webhooks" class="btn">Back to Webhooks</a>
</div>

<div class="card">
    <div class="card-header">
        <h3>Webhook: <?php echo htmlspecialchars($webhook['url']); ?></h3>
    </div>
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <p class="text-muted">No webhook logs yet</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Submission ID</th>
                        <th>Status</th>
                        <th>Status Code</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <?php if ($log['submission_id']): ?>
                                    <a href="/submissions/<?php echo $log['submission_id']; ?>">
                                        #<?php echo $log['submission_id']; ?>
                                    </a>
                                <?php else: ?>
                                    Test
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $log['success'] ? 'success' : 'danger'; ?>">
                                    <?php echo $log['success'] ? 'Success' : 'Failed'; ?>
                                </span>
                            </td>
                            <td><?php echo $log['status_code'] ?? 'N/A'; ?></td>
                            <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm" onclick="showLogDetails(<?php echo htmlspecialchars(json_encode($log)); ?>)">
                                    Details
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div id="log-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('log-modal').style.display='none'">&times;</span>
        <h3>Webhook Log Details</h3>
        <div id="log-details"></div>
    </div>
</div>

<script>
function showLogDetails(log) {
    const modal = document.getElementById('log-modal');
    const details = document.getElementById('log-details');

    details.innerHTML = `
        <h4>Payload</h4>
        <pre>${log.payload || 'N/A'}</pre>
        <h4>Response</h4>
        <pre>${log.response || 'N/A'}</pre>
    `;

    modal.style.display = 'block';
}
</script>

<?php
$content = ob_get_clean();
$title = 'Webhook Logs - SplashForms';
require __DIR__ . '/../layouts/app.php';
?>
