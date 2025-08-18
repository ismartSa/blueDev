#!/bin/bash

# SiteeBlue Server Setup Completion Script
# Run this script on your server after files are deployed
# Usage: bash server-complete-setup.sh

set -e

PROJECT_PATH="/home/siteeblue/htdocs/siteeblue.so"

echo "🚀 Completing Laravel setup on server..."
echo "📁 Project path: $PROJECT_PATH"

# Navigate to project directory
cd "$PROJECT_PATH"

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Setup environment file
echo "⚙️ Setting up environment file..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "✅ Environment file created"
else
    echo "ℹ️ Environment file already exists"
fi

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate --force

# Configure environment variables
echo "🔧 Configuring environment variables..."
sed -i "s|APP_URL=.*|APP_URL=http://158.101.234.203|" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=siteeblue|" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=siteeblue|" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=askme|" .env

# Set proper permissions
echo "🔐 Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod 600 .env

# Clear and optimize Laravel
echo "🧹 Clearing and optimizing Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Install and build frontend assets
echo "🎨 Installing and building frontend assets..."
npm install
npm run build

# Final optimization
echo "⚡ Final optimization..."
php artisan optimize

echo "✅ Server setup completed successfully!"
echo "🌐 Your application should now be accessible at: http://158.101.234.203"
echo ""
echo "📋 Additional steps you may need:"
echo "1. Configure your web server (Apache/Nginx) to point to the public directory"
echo "2. Set up SSL certificate if needed"
echo "3. Configure cron jobs for Laravel scheduler"
echo "4. Set up queue workers if using queues"
echo "5. Configure backup solutions"