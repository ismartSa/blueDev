#!/bin/bash

# SiteeBlue Deployment Script
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production

set -e

ENVIRONMENT=${1:-production}

# Determine if running locally or on server
if [[ -f "composer.json" ]]; then
    APP_DIR="$(pwd)"
    BACKUP_DIR="$APP_DIR/backups"
    LOCAL_MODE=true
else
    APP_DIR="/home/siteeblue/htdocs/siteeblue.so"
    BACKUP_DIR="/home/siteeblue/backups"
    LOCAL_MODE=false
fi

PHP_VERSION="8.3"

echo "🚀 Starting deployment for $ENVIRONMENT environment..."

# Create backup
echo "📦 Creating backup..."
if [[ "$LOCAL_MODE" == "true" ]]; then
    mkdir -p $BACKUP_DIR
    tar -czf $BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).tar.gz -C $APP_DIR .
else
    sudo mkdir -p $BACKUP_DIR
    sudo tar -czf $BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).tar.gz -C $APP_DIR .
fi

# Pull latest code
echo "📥 Pulling latest code..."
cd $APP_DIR
git pull origin main

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies and build assets
echo "🏗️ Building frontend assets..."
npm ci --production=false
npm run build

# Set proper permissions
echo "🔐 Setting permissions..."
if [[ "$LOCAL_MODE" == "true" ]]; then
    chmod -R 755 $APP_DIR
    chmod -R 775 $APP_DIR/storage 2>/dev/null || true
    chmod -R 775 $APP_DIR/bootstrap/cache 2>/dev/null || true
else
    sudo chown -R www-data:www-data $APP_DIR
    sudo chmod -R 755 $APP_DIR
    sudo chmod -R 775 $APP_DIR/storage
    sudo chmod -R 775 $APP_DIR/bootstrap/cache
fi

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Clear and cache configurations
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Clear application cache
php artisan cache:clear
php artisan queue:restart

# Reload services
echo "🔄 Reloading services..."
if [[ "$LOCAL_MODE" == "false" ]]; then
    sudo systemctl reload nginx
    sudo systemctl reload php$PHP_VERSION-fpm
else
    echo "ℹ️  Skipping service reload in local mode"
fi

# Health check
echo "🏥 Running health check..."
if curl -f -s http://localhost > /dev/null; then
    echo "✅ Deployment successful!"
else
    echo "❌ Health check failed!"
    exit 1
fi

echo "🎉 Deployment completed successfully!"