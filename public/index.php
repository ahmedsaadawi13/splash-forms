<?php
// FILE: /public/index.php

require_once __DIR__ . '/../config/config.php';

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Request.php';
require_once __DIR__ . '/../app/core/Response.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Validator.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/TenantContext.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/App.php';

require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Tenant.php';
require_once __DIR__ . '/../app/models/Form.php';
require_once __DIR__ . '/../app/models/Submission.php';
require_once __DIR__ . '/../app/models/Webhook.php';
require_once __DIR__ . '/../app/models/ApiKey.php';
require_once __DIR__ . '/../app/models/Plan.php';
require_once __DIR__ . '/../app/models/Subscription.php';
require_once __DIR__ . '/../app/models/Template.php';

require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/FormController.php';
require_once __DIR__ . '/../app/controllers/SubmissionController.php';
require_once __DIR__ . '/../app/controllers/WebhookController.php';
require_once __DIR__ . '/../app/controllers/ApiController.php';
require_once __DIR__ . '/../app/controllers/SettingsController.php';

$app = new App();
$app->run();
