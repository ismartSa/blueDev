#!/bin/bash

# Quick Deploy Script for SiteeBlue Laravel Project
# Usage: ./deployment/scripts/quick-deploy.sh [commit-message]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Server configuration
SERVER_IP="158.101.234.203"
SERVER_USER="siteeblue"
SERVER_PATH="/home/siteeblue/htdocs/siteeblue.so"
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
REMOTE_NAME="blueDev"
DEFAULT_BRANCH="clean-deployment"

echo -e "${BLUE}🚀 SiteeBlue Quick Deploy${NC}"
echo "================================"

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo -e "${RED}❌ Error: Must run from Laravel project root${NC}"
    exit 1
fi

# Get commit message
if [ -z "$1" ]; then
    read -p "📝 Enter commit message: " COMMIT_MSG
else
    COMMIT_MSG="$1"
fi

if [ -z "$COMMIT_MSG" ]; then
    echo -e "${RED}❌ Commit message is required${NC}"
    exit 1
fi

# Check for uncommitted changes
if ! git diff --quiet || ! git diff --cached --quiet; then
    echo -e "${YELLOW}📝 Committing changes...${NC}"
    git add .
    git commit -m "$COMMIT_MSG" || {
        echo -e "${YELLOW}⚠️  No changes to commit${NC}"
    }
else
    echo -e "${GREEN}✅ No changes to commit${NC}"
fi

# Push to repository
echo -e "${YELLOW}📤 Pushing to repository...${NC}"
# Get current branch name
CURRENT_BRANCH=$(git branch --show-current)
git push blueDev $CURRENT_BRANCH || {
    echo -e "${RED}❌ Failed to push to repository${NC}"
    exit 1
}

# Deploy to server
echo -e "${YELLOW}🚀 Deploying to server...${NC}"
rsync -avz --progress \
    --exclude-from=".deployignore" \
    --delete \
    "$PROJECT_ROOT/" \
    "$SERVER_USER@$SERVER_IP:$SERVER_PATH/"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Files deployed successfully!${NC}"
    
    # Run server-side commands
    echo -e "${YELLOW}⚙️ Running server-side updates...${NC}"
    ssh "$SERVER_USER@$SERVER_IP" << EOF
cd "$SERVER_PATH"

# Install/update dependencies
echo "📦 Updating dependencies..."
composer install --no-dev --optimize-autoloader --quiet

# Clear and cache configurations
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations if needed
echo "🗄️ Checking for new migrations..."
php artisan migrate --force

# Build frontend assets if package.json exists
if [ -f "package.json" ]; then
    echo "🎨 Building frontend assets..."
    npm install --production
    npm run build
fi

echo "✅ Server-side updates completed!"
EOF

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
        echo -e "${BLUE}🌐 Your site: http://$SERVER_IP${NC}"
        
        # Test the deployment
        echo -e "${YELLOW}🧪 Testing deployment...${NC}"
        HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://$SERVER_IP")
        if [ "$HTTP_CODE" = "200" ]; then
            echo -e "${GREEN}✅ Site is responding correctly (HTTP $HTTP_CODE)${NC}"
        else
            echo -e "${YELLOW}⚠️ Site returned HTTP $HTTP_CODE - please check manually${NC}"
        fi
    else
        echo -e "${RED}❌ Server-side updates failed!${NC}"
        exit 1
    fi
else
    echo -e "${RED}❌ File deployment failed!${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}📋 Deployment Summary:${NC}"
echo "• Commit: $COMMIT_MSG"
echo "• Server: $SERVER_USER@$SERVER_IP"
echo "• Path: $SERVER_PATH"
echo "• Time: $(date)"
echo ""
echo -e "${BLUE}💡 Next time, just run: ./deployment/scripts/quick-deploy.sh 'your message'${NC}"