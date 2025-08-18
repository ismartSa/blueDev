#!/bin/bash

# SiteeBlue Direct File Deployment Script
# Usage: ./deploy-files-only.sh
# This script only uploads project files without server setup

set -e

# Server configuration
SERVER_IP="158.101.234.203"
SERVER_USER="siteeblue"
SERVER_PATH="/home/siteeblue/htdocs/siteeblue.so"
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

echo "🚀 Starting file deployment to server..."
echo "📁 Project root: $PROJECT_ROOT"
echo "🖥️  Server: $SERVER_USER@$SERVER_IP:$SERVER_PATH"

# Check if rsync is available
if ! command -v rsync &> /dev/null; then
    echo "❌ rsync is required but not installed. Please install rsync first."
    exit 1
fi

# Use project's .deployignore file
DEPLOYIGNORE_FILE="$PROJECT_ROOT/.deployignore"
if [ ! -f "$DEPLOYIGNORE_FILE" ]; then
    echo -e "${RED}❌ .deployignore file not found${NC}"
    exit 1
fi

echo "📦 Syncing files to server..."
rsync -avz --progress \
    --exclude-from="$DEPLOYIGNORE_FILE" \
    --delete \
    "$PROJECT_ROOT/" \
    "$SERVER_USER@$SERVER_IP:$SERVER_PATH/"

if [ $? -eq 0 ]; then
    echo "✅ Files deployed successfully!"
    echo ""
    echo "📋 Next steps on your server:"
    echo "1. SSH to your server: ssh $SERVER_USER@$SERVER_IP"
    echo "2. Navigate to project: cd $SERVER_PATH"
    echo "3. Install dependencies: composer install --no-dev --optimize-autoloader"
    echo "4. Copy environment file: cp .env.example .env"
    echo "5. Generate app key: php artisan key:generate"
    echo "6. Configure database in .env file"
    echo "7. Run migrations: php artisan migrate"
    echo "8. Install npm packages: npm install"
    echo "9. Build assets: npm run build"
    echo "10. Set permissions: chmod -R 755 storage bootstrap/cache"
else
    echo "❌ Deployment failed!"
    exit 1
fi

# No cleanup needed for .deployignore

echo "🎉 Deployment completed!"