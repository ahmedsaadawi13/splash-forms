<!-- FILE: /app/views/home/docs.php -->
<?php ob_start(); ?>

<section class="docs">
    <div class="container">
        <h1>API Documentation</h1>

        <div class="doc-section">
            <h2>Authentication</h2>
            <p>All API requests require an API key. Include your API key in the <code>X-API-KEY</code> header.</p>
            <pre><code>X-API-KEY: your_api_key_here</code></pre>
        </div>

        <div class="doc-section">
            <h2>Endpoints</h2>

            <h3>GET /api/forms</h3>
            <p>List all forms for your tenant.</p>
            <pre><code>curl -H "X-API-KEY: your_key" <?php echo APP_URL; ?>/api/forms</code></pre>

            <h3>GET /api/forms/:id</h3>
            <p>Get a specific form by ID.</p>
            <pre><code>curl -H "X-API-KEY: your_key" <?php echo APP_URL; ?>/api/forms/1</code></pre>

            <h3>POST /api/forms</h3>
            <p>Create a new form.</p>
            <pre><code>curl -X POST -H "X-API-KEY: your_key" \
  -H "Content-Type: application/json" \
  -d '{"name":"Contact Form","slug":"contact"}' \
  <?php echo APP_URL; ?>/api/forms</code></pre>

            <h3>POST /api/forms/:id/submit</h3>
            <p>Submit data to a form.</p>
            <pre><code>curl -X POST -H "X-API-KEY: your_key" \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com"}' \
  <?php echo APP_URL; ?>/api/forms/1/submit</code></pre>

            <h3>GET /api/forms/:id/submissions</h3>
            <p>Get all submissions for a form.</p>
            <pre><code>curl -H "X-API-KEY: your_key" <?php echo APP_URL; ?>/api/forms/1/submissions</code></pre>

            <h3>GET /api/submissions/:id</h3>
            <p>Get a specific submission.</p>
            <pre><code>curl -H "X-API-KEY: your_key" <?php echo APP_URL; ?>/api/submissions/1</code></pre>
        </div>

        <div class="doc-section">
            <h2>Webhooks</h2>
            <p>Configure webhooks to receive real-time notifications when forms are submitted.</p>
            <p>Webhook payloads are sent as POST requests with the following structure:</p>
            <pre><code>{
  "event": "submission.created",
  "form_id": 1,
  "submission_id": 123,
  "data": {
    "name": "John Doe",
    "email": "john@example.com"
  },
  "timestamp": "2025-01-01T12:00:00Z"
}</code></pre>
            <p>Webhooks include a <code>X-Webhook-Signature</code> header with an HMAC SHA256 signature for verification.</p>
        </div>

        <div class="doc-section">
            <h2>Embedding Forms</h2>
            <p>You can embed forms directly on your website using an iframe:</p>
            <pre><code>&lt;iframe src="<?php echo APP_URL; ?>/f/your-form-slug" width="100%" height="600"&gt;&lt;/iframe&gt;</code></pre>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
$title = 'API Documentation - SplashForms';
require __DIR__ . '/../layouts/guest.php';
?>
