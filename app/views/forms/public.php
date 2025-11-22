<!-- FILE: /app/views/forms/public.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($form['name']); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="public-form-page">
    <div class="public-form-container">
        <div class="public-form-card">
            <h1><?php echo htmlspecialchars($form['name']); ?></h1>
            <?php if (!empty($form['description'])): ?>
                <p class="form-description"><?php echo nl2br(htmlspecialchars($form['description'])); ?></p>
            <?php endif; ?>

            <div id="form-messages"></div>

            <form id="public-form" method="POST" action="/forms/<?php echo $form['id']; ?>/submit" enctype="multipart/form-data">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

                <?php if (!empty($form['settings']['enable_spam_protection'])): ?>
                    <input type="text" name="_honeypot" style="display:none;" tabindex="-1" autocomplete="off">
                <?php endif; ?>

                <?php foreach ($form['schema'] as $field): ?>
                    <div class="form-group">
                        <label for="field_<?php echo $field['name']; ?>">
                            <?php echo htmlspecialchars($field['label']); ?>
                            <?php if (!empty($field['required'])): ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if ($field['type'] === 'textarea'): ?>
                            <textarea
                                id="field_<?php echo $field['name']; ?>"
                                name="<?php echo $field['name']; ?>"
                                class="form-control"
                                rows="4"
                                <?php echo !empty($field['required']) ? 'required' : ''; ?>
                                placeholder="<?php echo htmlspecialchars($field['placeholder'] ?? ''); ?>"
                            ></textarea>

                        <?php elseif ($field['type'] === 'select'): ?>
                            <select
                                id="field_<?php echo $field['name']; ?>"
                                name="<?php echo $field['name']; ?>"
                                class="form-control"
                                <?php echo !empty($field['required']) ? 'required' : ''; ?>
                            >
                                <option value="">Select an option</option>
                                <?php foreach ($field['options'] ?? [] as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>">
                                        <?php echo htmlspecialchars($option); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($field['type'] === 'checkbox'): ?>
                            <label class="checkbox-label">
                                <input
                                    type="checkbox"
                                    id="field_<?php echo $field['name']; ?>"
                                    name="<?php echo $field['name']; ?>"
                                    value="1"
                                    <?php echo !empty($field['required']) ? 'required' : ''; ?>
                                >
                                <?php echo htmlspecialchars($field['label']); ?>
                            </label>

                        <?php elseif ($field['type'] === 'radio'): ?>
                            <?php foreach ($field['options'] ?? [] as $option): ?>
                                <label class="radio-label">
                                    <input
                                        type="radio"
                                        name="<?php echo $field['name']; ?>"
                                        value="<?php echo htmlspecialchars($option); ?>"
                                        <?php echo !empty($field['required']) ? 'required' : ''; ?>
                                    >
                                    <?php echo htmlspecialchars($option); ?>
                                </label>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <input
                                type="<?php echo $field['type']; ?>"
                                id="field_<?php echo $field['name']; ?>"
                                name="<?php echo $field['name']; ?>"
                                class="form-control"
                                <?php echo !empty($field['required']) ? 'required' : ''; ?>
                                placeholder="<?php echo htmlspecialchars($field['placeholder'] ?? ''); ?>"
                            >
                        <?php endif; ?>

                        <div class="field-error" id="error_<?php echo $field['name']; ?>"></div>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Submit</button>
            </form>

            <div class="form-footer">
                <p class="text-muted">Powered by <a href="/">SplashForms</a></p>
            </div>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/public-form.js"></script>
</body>
</html>
