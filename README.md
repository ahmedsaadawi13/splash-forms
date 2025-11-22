# SplashForms - Multi-Tenant Form Builder SaaS

A complete, production-ready multi-tenant form builder SaaS platform built with pure PHP (7.0+), MySQL, and vanilla JavaScript. No frameworks required!

## Features

- **Drag & Drop Form Builder** - Intuitive interface for creating forms
- **Multi-Tenant Architecture** - Complete tenant isolation with subscription management
- **Form Types** - Text, email, number, phone, textarea, select, checkbox, radio, file upload
- **Public Form Pages** - Embeddable forms with custom URLs
- **Submission Management** - View, filter, and manage form submissions
- **Spam Protection** - Built-in honeypot spam protection
- **Webhooks** - Real-time notifications via webhooks
- **CSV Export** - Export submissions to CSV
- **REST API** - Full API for programmatic access
- **Form Templates** - Pre-built form templates
- **Role-Based Access Control** - Platform admin, tenant admin, staff, and user roles
- **Subscription Plans** - Multiple pricing tiers with quota enforcement
- **API Key Management** - Secure API access with key generation
- **Usage Tracking** - Monitor form and submission usage

## Requirements

- PHP 7.0 or higher (compatible with PHP 8.x)
- MySQL 5.7+ or MariaDB 10.2+
- Apache with mod_rewrite or Nginx
- Composer (optional, for polyfills if needed)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/ahmedsaadawi13/SplashForms.git
cd SplashForms
```

### 2. Configure Database

Create a MySQL database and import the schema:

```bash
mysql -u root -p
CREATE DATABASE splashforms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE splashforms;
source database.sql;
exit;
```

### 3. Configure Application

Edit `/config/config.php` with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'splashforms');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('APP_URL', 'http://yourdomain.com');
```

### 4. Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 storage/uploads/
```

### 5. Configure Web Server

#### Apache

The project includes `.htaccess` files for Apache. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Point your virtual host document root to the `/public` directory:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/SplashForms/public

    <Directory /path/to/SplashForms/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/SplashForms/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 6. Default Login

**Platform Admin:**
- Email: `admin@splashforms.local`
- Password: `password`

**Demo Tenant Admin:**
- Email: `demo@example.com`
- Password: `password`

**Important:** Change these passwords immediately in production!

## Project Structure

```
SplashForms/
├── app/
│   ├── controllers/      # Application controllers
│   ├── core/            # Core MVC framework
│   ├── models/          # Database models
│   └── views/           # View templates
├── config/
│   └── config.php       # Application configuration
├── public/
│   ├── assets/          # CSS, JS, images
│   ├── .htaccess        # Apache rewrite rules
│   └── index.php        # Application entry point
├── storage/
│   └── uploads/         # File uploads
├── database.sql         # Database schema + seed data
├── routes.php           # Application routes
└── README.md
```

## Usage Guide

### Creating Forms

1. Login to your account
2. Navigate to **Forms** > **Create New Form**
3. Choose to start from scratch or use a template
4. Use the drag-and-drop builder to add fields
5. Configure field properties (label, placeholder, required, etc.)
6. Save your form

### Managing Submissions

1. Go to **Forms** and select a form
2. Click **Submissions** to view all submissions
3. Filter by status (new, read, spam, archived)
4. View individual submissions for details
5. Export to CSV for analysis

### Setting Up Webhooks

1. Navigate to a form
2. Click **Webhooks**
3. Add your webhook URL
4. Test the webhook to verify it's working
5. View webhook logs for debugging

### Using the API

Generate an API key in **Settings** > **API Keys**, then use it in your requests:

```bash
curl -H "X-API-KEY: your_api_key" \
  https://yourdomain.com/api/forms
```

## API Documentation

### Authentication

All API requests require an API key in the `X-API-KEY` header:

```
X-API-KEY: sk_yourapikeyhere
```

### Endpoints

#### GET /api/forms
List all forms for your tenant.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Contact Form",
      "slug": "contact-form",
      "is_active": true,
      "submissions_count": 10
    }
  ]
}
```

#### GET /api/forms/:id
Get a specific form.

#### POST /api/forms
Create a new form.

**Request:**
```json
{
  "name": "Contact Form",
  "slug": "contact",
  "schema": [
    {
      "type": "text",
      "name": "name",
      "label": "Name",
      "required": true
    },
    {
      "type": "email",
      "name": "email",
      "label": "Email",
      "required": true
    }
  ]
}
```

#### POST /api/forms/:id/submit
Submit data to a form.

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com"
}
```

#### GET /api/forms/:id/submissions
Get all submissions for a form (paginated).

#### GET /api/submissions/:id
Get a specific submission.

### Webhook Payload

Webhooks send POST requests with this structure:

```json
{
  "event": "submission.created",
  "form_id": 1,
  "submission_id": 123,
  "data": {
    "name": "John Doe",
    "email": "john@example.com"
  },
  "timestamp": "2025-01-01T12:00:00Z"
}
```

The `X-Webhook-Signature` header contains an HMAC SHA256 signature for verification.

## Multi-Tenant Architecture

### Tenant Isolation

- Each tenant has a unique `tenant_id`
- All main tables include `tenant_id` for data isolation
- Queries automatically filter by tenant context
- API keys are tenant-specific

### Subscription Management

- Plans with configurable limits (forms, submissions, file size)
- Usage tracking per tenant
- Quota enforcement at creation time
- Automatic subscription expiration handling

### Roles & Permissions

- **platform_admin**: Full system access
- **tenant_admin**: Manage tenant settings, users, and forms
- **staff**: Create and manage forms
- **user**: View forms only

## Security Features

### Input Validation & Sanitization
- PDO prepared statements for SQL injection prevention
- CSRF token validation on all POST requests
- Input validation with custom rules
- XSS protection via htmlspecialchars

### Password Security
- Password hashing with password_hash (bcrypt)
- Minimum password length enforcement
- Secure session management

### File Upload Security
- File type validation
- File size limits per plan
- Unique filename generation
- Directory traversal prevention

### API Security
- API key authentication
- Rate limiting (configurable)
- Webhook signature verification

## Deployment Guide

### Production Checklist

1. **Environment Configuration**
   - Set `APP_ENV` to `production` in `config.php`
   - Disable error display in production
   - Use strong database credentials

2. **Database**
   - Regular backups
   - Enable slow query logging
   - Optimize indexes for performance

3. **Web Server**
   - Enable HTTPS/SSL
   - Configure proper cache headers
   - Enable gzip compression
   - Set up firewall rules

4. **PHP Configuration**
   - Set appropriate `upload_max_filesize`
   - Configure `post_max_size`
   - Enable OPcache for performance
   - Disable dangerous functions

5. **File Permissions**
   ```bash
   chmod 755 -R app/ config/ public/
   chmod 755 storage/
   chmod 775 storage/uploads/
   ```

6. **Security Headers**
   ```apache
   Header set X-Content-Type-Options "nosniff"
   Header set X-Frame-Options "SAMEORIGIN"
   Header set X-XSS-Protection "1; mode=block"
   Header set Strict-Transport-Security "max-age=31536000"
   ```

7. **Monitoring**
   - Set up error logging
   - Monitor disk space for uploads
   - Track database performance
   - Monitor API usage

## Testing

### Manual Testing Checklist

- [ ] User registration and login
- [ ] Form creation with all field types
- [ ] Form builder drag & drop
- [ ] Public form submission
- [ ] File upload functionality
- [ ] Webhook triggers
- [ ] CSV export
- [ ] API endpoints
- [ ] Role permissions
- [ ] Quota enforcement
- [ ] Multi-tenant isolation

### Test Credentials

Use the seed data provided in `database.sql` for testing.

## Troubleshooting

### Common Issues

**500 Internal Server Error**
- Check Apache error logs: `tail -f /var/log/apache2/error.log`
- Verify `.htaccess` is being read
- Ensure `mod_rewrite` is enabled

**Database Connection Failed**
- Verify credentials in `config/config.php`
- Check MySQL is running: `systemctl status mysql`
- Ensure database exists and is accessible

**File Uploads Not Working**
- Check `storage/uploads/` permissions
- Verify `upload_max_filesize` in php.ini
- Ensure directory is writable by web server

**Forms Not Saving**
- Check browser console for JavaScript errors
- Verify CSRF token is present
- Check database write permissions

## Performance Optimization

### Database
- Add indexes on frequently queried columns
- Use query caching for read-heavy operations
- Implement pagination for large datasets

### PHP
- Enable OPcache
- Use PHP 7.4+ for better performance
- Implement response caching where appropriate

### Frontend
- Minify CSS and JavaScript
- Enable browser caching
- Use CDN for static assets

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch
3. Write clean, documented code
4. Test thoroughly
5. Submit a pull request

## License

This project is open-source and available under the MIT License.

## Support

For issues and questions:
- GitHub Issues: https://github.com/yourusername/SplashForms/issues
- Documentation: https://yourdomain.com/docs

## Credits

Built with:
- Pure PHP (no frameworks)
- MySQL/MariaDB
- Vanilla JavaScript
- Modern CSS

## Changelog

### Version 1.0.0 (2025-01-01)
- Initial release
- Multi-tenant form builder
- REST API
- Webhook support
- Subscription management
- Role-based access control
