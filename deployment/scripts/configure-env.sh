#!/bin/bash

# SiteeBlue Environment Configuration Script
# Usage: ./configure-env.sh [environment]
# Example: ./configure-env.sh production

set -e

ENVIRONMENT=${1:-production}

# Determine if running locally or on server
if [[ -f "composer.json" ]]; then
    APP_DIR="$(pwd)"
else
    APP_DIR="/home/siteeblue/htdocs/siteeblue.so"
fi

ENV_FILE="$APP_DIR/.env"

echo "🔧 Configuring environment for $ENVIRONMENT..."
echo "📁 Working directory: $APP_DIR"

# Check if .env exists
if [ ! -f "$ENV_FILE" ]; then
    echo "📋 Creating .env file from example..."
    cp "$APP_DIR/.env.example" "$ENV_FILE"
fi

# Function to update or add environment variable
update_env_var() {
    local key=$1
    local value=$2
    
    if grep -q "^$key=" "$ENV_FILE"; then
        sed -i.bak "s|^$key=.*|$key=$value|" "$ENV_FILE"
    else
        echo "$key=$value" >> "$ENV_FILE"
    fi
}

# Basic application settings
echo "⚙️ Setting basic application configuration..."
update_env_var "APP_NAME" "SiteeBlue"
update_env_var "APP_ENV" "$ENVIRONMENT"
update_env_var "APP_DEBUG" "false"

# Generate application key if not exists
if ! grep -q "^APP_KEY=base64:" "$ENV_FILE"; then
    echo "🔑 Generating application key..."
    cd "$APP_DIR"
    php artisan key:generate --force
fi

# Set domain for server deployment
if [[ "$ENVIRONMENT" == "production" ]]; then
    update_env_var "APP_URL" "http://158.101.234.203"
else
    read -p "🌐 Enter your domain (e.g., example.com): " DOMAIN
    if [ ! -z "$DOMAIN" ]; then
        update_env_var "APP_URL" "https://$DOMAIN"
    fi
fi

# Database configuration
echo "🗄️ Configuring database..."
if [[ "$ENVIRONMENT" == "production" ]]; then
    # Production database settings
    update_env_var "DB_DATABASE" "siteeblue"
    update_env_var "DB_USERNAME" "siteeblue"
    update_env_var "DB_PASSWORD" "askme"
else
    # Interactive configuration for other environments
    read -p "Database name [siteeblue]: " DB_NAME
    DB_NAME=${DB_NAME:-siteeblue}
    update_env_var "DB_DATABASE" "$DB_NAME"
    
    read -p "Database username [siteeblue]: " DB_USER
    DB_USER=${DB_USER:-siteeblue}
    update_env_var "DB_USERNAME" "$DB_USER"
    
    read -s -p "Database password: " DB_PASS
    echo
    if [ ! -z "$DB_PASS" ]; then
        update_env_var "DB_PASSWORD" "$DB_PASS"
    fi
fi

# Cache and session configuration
echo "⚡ Configuring cache and sessions..."
update_env_var "CACHE_DRIVER" "redis"
update_env_var "SESSION_DRIVER" "redis"
update_env_var "QUEUE_CONNECTION" "redis"

# Mail configuration (optional)
if [[ "$ENVIRONMENT" != "production" ]]; then
    read -p "📧 Configure email? (y/n) [n]: " CONFIGURE_MAIL
    if [[ $CONFIGURE_MAIL =~ ^[Yy]$ ]]; then
        read -p "SMTP Host: " MAIL_HOST
        read -p "SMTP Port [587]: " MAIL_PORT
        MAIL_PORT=${MAIL_PORT:-587}
        read -p "SMTP Username: " MAIL_USERNAME
        read -s -p "SMTP Password: " MAIL_PASSWORD
        echo
        
        update_env_var "MAIL_MAILER" "smtp"
        update_env_var "MAIL_HOST" "$MAIL_HOST"
        update_env_var "MAIL_PORT" "$MAIL_PORT"
        update_env_var "MAIL_USERNAME" "$MAIL_USERNAME"
        update_env_var "MAIL_PASSWORD" "$MAIL_PASSWORD"
        update_env_var "MAIL_ENCRYPTION" "tls"
    fi
fi

# Set proper permissions
echo "🔐 Setting file permissions..."
if [[ -f "composer.json" ]]; then
    # Local mode - skip permission changes
    echo "Local mode: Skipping permission changes"
else
    # Server mode - set proper permissions
    chown www-data:www-data "$ENV_FILE"
    chmod 600 "$ENV_FILE"
fi

# Test configuration
echo "🧪 Testing configuration..."
cd "$APP_DIR"
php artisan config:clear
php artisan config:cache

# Test database connection
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection successful!';" 2>/dev/null; then
    echo "✅ Database connection successful!"
else
    echo "❌ Database connection failed. Please check your credentials."
fi

echo "✅ Environment configuration completed!"
echo "📋 Next steps:"
echo "1. Run database migrations: php artisan migrate"
echo "2. Seed database (if needed): php artisan db:seed"
echo "3. Build frontend assets: npm run build"
echo "4. Clear and cache configurations: php artisan optimize"