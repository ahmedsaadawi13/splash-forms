<!-- FILE: /app/views/forms/create.php -->
<?php ob_start(); ?>

<div class="page-header">
    <h1>Create New Form</h1>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h3>Start from Scratch</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="/forms">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="name">Form Name</label>
                    <input type="text" id="name" name="name" class="form-control" required autofocus>
                </div>

                <div class="form-group">
                    <label for="description">Description (optional)</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Create Form</button>
                <a href="/forms" class="btn">Cancel</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Start from Template</h3>
        </div>
        <div class="card-body">
            <?php if (empty($templates)): ?>
                <p class="text-muted">No templates available</p>
            <?php else: ?>
                <?php foreach ($templates as $template): ?>
                    <div class="template-item">
                        <h4><?php echo htmlspecialchars($template['name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($template['description']); ?></p>
                        <form method="POST" action="/forms" class="inline-form">
                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                            <input type="text" name="name" placeholder="Form name" class="form-control form-control-sm" required>
                            <button type="submit" class="btn btn-sm btn-primary">Use Template</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Create Form - SplashForms';
require __DIR__ . '/../layouts/app.php';
?>
