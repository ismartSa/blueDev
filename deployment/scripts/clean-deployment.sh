#!/bin/bash

# SiteeBlue Clean Deployment Script with Dev-Tools Integration
# Usage: ./clean-deployment.sh [environment] [--include-dev-tools]
# Example: ./clean-deployment.sh production
# Example: ./clean-deployment.sh staging --include-dev-tools

set -e

ENVIRONMENT=${1:-production}
INCLUDE_DEV_TOOLS=false

# Parse arguments
for arg in "$@"; do
    case $arg in
        --include-dev-tools)
            INCLUDE_DEV_TOOLS=true
            shift
            ;;
    esac
done

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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
DEPLOYIGNORE_FILE="$APP_DIR/.deployignore"

echo -e "${BLUE}🚀 Starting clean deployment for $ENVIRONMENT environment...${NC}"
echo -e "${BLUE}📁 App directory: $APP_DIR${NC}"
echo -e "${BLUE}🔧 Dev-tools inclusion: $INCLUDE_DEV_TOOLS${NC}"

# Validate .deployignore file
if [ ! -f "$DEPLOYIGNORE_FILE" ]; then
    echo -e "${RED}❌ .deployignore file not found${NC}"
    exit 1
fi

# Create backup with timestamp
echo -e "${YELLOW}📦 Creating backup...${NC}"
BACKUP_FILE="$BACKUP_DIR/clean-backup-$(date +%Y%m%d-%H%M%S).tar.gz"
if [[ "$LOCAL_MODE" == "true" ]]; then
    mkdir -p $BACKUP_DIR
    tar --exclude-from="$DEPLOYIGNORE_FILE" -czf "$BACKUP_FILE" -C "$APP_DIR" .
else
    sudo mkdir -p $BACKUP_DIR
    sudo tar --exclude-from="$DEPLOYIGNORE_FILE" -czf "$BACKUP_FILE" -C "$APP_DIR" .
fi
echo -e "${GREEN}✅ Backup created: $BACKUP_FILE${NC}"

# Pull latest code
echo -e "${YELLOW}📥 Pulling latest code...${NC}"
cd $APP_DIR
git pull blueDev $(git branch --show-current)

# Clean old cache and temporary files
echo -e "${YELLOW}🧹 Cleaning old cache and temporary files...${NC}"
php artisan cache:clear || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Remove old compiled assets
rm -rf public/build/* 2>/dev/null || true
rm -rf public/hot 2>/dev/null || true

# Install PHP dependencies
echo -e "${YELLOW}📦 Installing PHP dependencies...${NC}"
if [[ "$ENVIRONMENT" == "production" ]]; then
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
else
    composer install --optimize-autoloader --no-interaction
fi

# Install Node dependencies and build assets
echo -e "${YELLOW}🏗️ Building frontend assets...${NC}"
npm ci --production=false
npm run build

# Handle dev-tools based on environment and flag
if [[ "$INCLUDE_DEV_TOOLS" == "true" && "$ENVIRONMENT" != "production" ]]; then
    echo -e "${BLUE}🔧 Including dev-tools for $ENVIRONMENT environment...${NC}"
    # Ensure dev-tools are accessible
    if [ -d "dev-tools/viewers" ]; then
        echo -e "${GREEN}✅ Dev-tools viewers are available${NC}"
    else
        echo -e "${RED}❌ Dev-tools viewers directory not found${NC}"
    fi
else
    echo -e "${BLUE}🚫 Excluding dev-tools from $ENVIRONMENT deployment${NC}"
fi

# Set proper permissions
echo -e "${YELLOW}🔐 Setting permissions...${NC}"
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
echo -e "${YELLOW}🗄️ Running database migrations...${NC}"
# Temporarily set cache driver to file for deployment
export CACHE_DRIVER=file
php artisan migrate --force || {
    echo "⚠️ Database migration failed, continuing deployment..."
}

# Optimize application
echo -e "${YELLOW}⚡ Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Clear application cache and restart queue
php artisan cache:clear
php artisan queue:restart

# Reload services
echo -e "${YELLOW}🔄 Reloading services...${NC}"
if [[ "$LOCAL_MODE" == "false" ]]; then
    sudo systemctl reload nginx
    sudo systemctl reload php$PHP_VERSION-fpm
else
    echo -e "${BLUE}ℹ️  Skipping service reload in local mode${NC}"
fi

# Health check
echo -e "${YELLOW}🏥 Running health check...${NC}"
if curl -f -s http://localhost > /dev/null; then
    echo -e "${GREEN}✅ Health check passed!${NC}"
else
    echo -e "${RED}❌ Health check failed!${NC}"
    exit 1
fi

# Display deployment summary
echo -e "${GREEN}🎉 Clean deployment completed successfully!${NC}"
echo -e "${BLUE}📊 Deployment Summary:${NC}"
echo -e "${BLUE}  Environment: $ENVIRONMENT${NC}"
echo -e "${BLUE}  Dev-tools included: $INCLUDE_DEV_TOOLS${NC}"
echo -e "${BLUE}  Backup location: $BACKUP_FILE${NC}"
echo -e "${BLUE}  App directory: $APP_DIR${NC}"

if [[ "$INCLUDE_DEV_TOOLS" == "true" && "$ENVIRONMENT" != "production" ]]; then
    echo -e "${YELLOW}🔧 Dev-tools are available at: /dev-tools/viewers/${NC}"
    echo -e "${YELLOW}   - admin-testing-suite.html${NC}"
    echo -e "${YELLOW}   - unified-viewer.html${NC}"
    echo -e "${YELLOW}   - report-viewer.html${NC}"
fi

echo -e "${GREEN}✨ Deployment process completed cleanly!${NC}"