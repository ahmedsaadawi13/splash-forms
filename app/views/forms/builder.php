<!-- FILE: /app/views/forms/builder.php -->
<?php ob_start(); ?>

<div class="page-header">
    <h1>Form Builder: <?php echo htmlspecialchars($form['name']); ?></h1>
    <div class="btn-group">
        <a href="/forms" class="btn">Back to Forms</a>
        <a href="/forms/<?php echo $form['id']; ?>" class="btn">Settings</a>
        <a href="/f/<?php echo $form['slug']; ?>" class="btn" target="_blank">Preview</a>
    </div>
</div>

<div class="form-builder">
    <div class="builder-sidebar">
        <h3>Fields</h3>
        <div class="field-types">
            <div class="field-type" data-type="text">
                <span class="icon">T</span>
                <span>Text Input</span>
            </div>
            <div class="field-type" data-type="email">
                <span class="icon">@</span>
                <span>Email</span>
            </div>
            <div class="field-type" data-type="number">
                <span class="icon">#</span>
                <span>Number</span>
            </div>
            <div class="field-type" data-type="tel">
                <span class="icon">☎</span>
                <span>Phone</span>
            </div>
            <div class="field-type" data-type="textarea">
                <span class="icon">¶</span>
                <span>Textarea</span>
            </div>
            <div class="field-type" data-type="select">
                <span class="icon">▼</span>
                <span>Select</span>
            </div>
            <div class="field-type" data-type="checkbox">
                <span class="icon">☑</span>
                <span>Checkbox</span>
            </div>
            <div class="field-type" data-type="radio">
                <span class="icon">◉</span>
                <span>Radio</span>
            </div>
            <div class="field-type" data-type="file">
                <span class="icon">📎</span>
                <span>File Upload</span>
            </div>
        </div>
    </div>

    <div class="builder-canvas">
        <div id="form-canvas" class="form-canvas">
            <p class="empty-message">Drag fields here to build your form</p>
        </div>
        <button id="save-form" class="btn btn-primary btn-lg">Save Form</button>
    </div>

    <div class="builder-properties" id="field-properties" style="display:none;">
        <h3>Field Properties</h3>
        <div id="properties-content"></div>
    </div>
</div>

<input type="hidden" id="csrf-token" value="<?php echo $csrf_token; ?>">
<input type="hidden" id="form-id" value="<?php echo $form['id']; ?>">
<input type="hidden" id="form-schema" value='<?php echo json_encode($form['schema'] ?? []); ?>'>

<?php
$content = ob_get_clean();
$title = 'Form Builder - SplashForms';
$scripts = ['/assets/js/form-builder.js'];
require __DIR__ . '/../layouts/app.php';
?>
