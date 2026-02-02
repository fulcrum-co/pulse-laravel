# Deployment Guide

This document covers deploying Pulse to production environments, including Laravel Cloud and traditional server deployments.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Environment Configuration](#environment-configuration)
- [Laravel Cloud Deployment](#laravel-cloud-deployment)
- [Traditional Server Deployment](#traditional-server-deployment)
- [Database Setup](#database-setup)
- [Queue Workers](#queue-workers)
- [Caching](#caching)
- [SSL/TLS Configuration](#ssltls-configuration)
- [Monitoring](#monitoring)
- [Troubleshooting](#troubleshooting)

## Prerequisites

### System Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| PHP | 8.2 | 8.3 |
| PostgreSQL | 15 | 16 |
| Redis | 7 | 7.2 |
| Node.js | 18 | 20 LTS |
| Memory | 2GB | 4GB+ |
| Storage | 20GB | 50GB+ |

### Required Extensions

- PHP: bcmath, ctype, curl, dom, fileinfo, json, mbstring, openssl, pcre, pdo, pdo_pgsql, tokenizer, xml
- PostgreSQL: pgvector extension (for semantic search)

## Environment Configuration

### Required Environment Variables

```env
# Application
APP_NAME=Pulse
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=pulse
DB_USERNAME=pulse_user
DB_PASSWORD=secure-password

# Redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=redis-password
REDIS_PORT=6379

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Search (Meilisearch)
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=https://your-meilisearch-host
MEILISEARCH_KEY=your-master-key

# AI Services
ANTHROPIC_API_KEY=your-claude-api-key

# Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=pulse-storage
```

### Optional Environment Variables

```env
# Monitoring
SENTRY_LARAVEL_DSN=https://your-sentry-dsn

# WebSockets (Laravel Reverb)
REVERB_APP_ID=pulse
REVERB_APP_KEY=your-reverb-key
REVERB_APP_SECRET=your-reverb-secret
REVERB_HOST=localhost
REVERB_PORT=8080

# Rate Limiting
RATE_LIMIT_API=60
RATE_LIMIT_AUTH=5
```

## Laravel Cloud Deployment

Pulse is configured for Laravel Cloud deployment via `cloud.yaml`.

### Initial Setup

1. Connect your repository to Laravel Cloud
2. Configure environment variables in the Cloud dashboard
3. Set up your database and Redis instances

### Deployment Configuration

The `cloud.yaml` file defines:

```yaml
id: pulse-laravel
name: Pulse

environments:
  production:
    compute:
      - name: web
        size: hobby-1x

    database:
      - name: pulse-db
        engine: postgres
        size: hobby-1x

    cache:
      - name: pulse-cache
        engine: redis
        size: hobby-1x

    build:
      - npm ci
      - npm run build

    deploy:
      - php artisan migrate --force
      - php artisan config:cache
      - php artisan route:cache
      - php artisan view:cache
```

### Deployment Steps

1. Push to main branch triggers automatic deployment
2. Build phase runs npm install and asset compilation
3. Deploy phase runs migrations and caches configs
4. Health checks verify deployment success

## Traditional Server Deployment

### Server Setup

1. **Install dependencies:**
   ```bash
   sudo apt update
   sudo apt install php8.2-fpm php8.2-pgsql php8.2-redis php8.2-mbstring \
     php8.2-xml php8.2-curl php8.2-bcmath nginx supervisor redis-server
   ```

2. **Install Composer:**
   ```bash
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

3. **Install Node.js:**
   ```bash
   curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
   sudo apt install nodejs
   ```

### Application Deployment

```bash
# Clone repository
cd /var/www
git clone https://github.com/fulcrum-co/pulse-laravel.git pulse
cd pulse

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Configure environment
cp .env.example .env
php artisan key:generate
# Edit .env with production values

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
```

### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/pulse/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Database Setup

### PostgreSQL with pgvector

```sql
-- Create database and user
CREATE USER pulse_user WITH PASSWORD 'secure-password';
CREATE DATABASE pulse OWNER pulse_user;

-- Connect to database and enable extensions
\c pulse
CREATE EXTENSION IF NOT EXISTS vector;
CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- Grant permissions
GRANT ALL PRIVILEGES ON DATABASE pulse TO pulse_user;
```

### Running Migrations

```bash
# Run all migrations
php artisan migrate --force

# Check migration status
php artisan migrate:status

# Rollback if needed (BE CAREFUL in production)
php artisan migrate:rollback --step=1
```

## Queue Workers

### Supervisor Configuration

Create `/etc/supervisor/conf.d/pulse-worker.conf`:

```ini
[program:pulse-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pulse/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/pulse/storage/logs/worker.log
stopwaitsecs=3600
```

### Start Workers

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pulse-worker:*
```

### Queue Priorities

Configure multiple workers for different priorities:

```ini
[program:pulse-worker-high]
command=php /var/www/pulse/artisan queue:work redis --queue=high,default --sleep=1
numprocs=2

[program:pulse-worker-default]
command=php /var/www/pulse/artisan queue:work redis --queue=default --sleep=3
numprocs=4

[program:pulse-worker-low]
command=php /var/www/pulse/artisan queue:work redis --queue=low --sleep=10
numprocs=1
```

## Caching

### Cache Configuration

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Or use the optimize command
php artisan optimize
```

### Redis Configuration

Ensure Redis is configured for persistence in production:

```conf
# /etc/redis/redis.conf
appendonly yes
appendfsync everysec
```

## SSL/TLS Configuration

### Using Certbot (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

### Force HTTPS

Add to your `.env`:
```env
APP_URL=https://your-domain.com
FORCE_HTTPS=true
```

## Monitoring

### Health Check Endpoint

The application exposes `/up` for health checks:

```bash
curl https://your-domain.com/up
```

### Laravel Telescope (Development)

Telescope is available in non-production environments at `/telescope`.

### Sentry Integration

Add to `.env`:
```env
SENTRY_LARAVEL_DSN=https://your-sentry-dsn
```

### Log Configuration

Configure log channels in `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
    ],
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'level' => 'error',
    ],
],
```

## Troubleshooting

### Common Issues

**500 Server Error:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Check permissions
sudo chown -R www-data:www-data storage bootstrap/cache
```

**Queue Jobs Not Processing:**
```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart pulse-worker:*
```

**Database Connection Issues:**
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo()
```

**Cache Issues:**
```bash
# Clear all caches
php artisan optimize:clear

# Clear specific cache
php artisan cache:clear
php artisan config:clear
```

### Performance Optimization

```bash
# Enable OPcache
sudo phpenmod opcache

# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev

# Optimize Laravel
php artisan optimize
```

---

For additional support, contact the DevOps team or open an issue on GitHub.
