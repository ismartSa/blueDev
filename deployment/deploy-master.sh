#!/bin/bash

# Laravel Brive Master Deployment Script
# Usage: ./deploy-master.sh [setup|deploy|full] [environment]
# Examples:
#   ./deploy-master.sh setup          # Server setup only
#   ./deploy-master.sh deploy prod    # Deploy to production
#   ./deploy-master.sh full prod      # Full setup + deploy

set -e

ACTION=${1:-deploy}
ENVIRONMENT=${2:-production}
DEPLOYMENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$DEPLOYMENT_DIR")"

echo "🚀 Laravel Brive Deployment Manager"
echo "Action: $ACTION | Environment: $ENVIRONMENT"
echo "Deployment Dir: $DEPLOYMENT_DIR"
echo "Project Root: $PROJECT_ROOT"
echo "----------------------------------------"

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
    exit 1
}

# Check if running on server or local
check_environment() {
    if [[ "$ACTION" == "setup" ]] || [[ "$ACTION" == "full" ]]; then
        if [[ $EUID -ne 0 ]]; then
            error "Server setup requires root privileges. Run with sudo."
        fi
    fi
}

# Server setup
setup_server() {
    log "Starting server setup..."
    
    if [[ ! -f "$DEPLOYMENT_DIR/scripts/server-setup.sh" ]]; then
        error "Server setup script not found!"
    fi
    
    chmod +x "$DEPLOYMENT_DIR/scripts/server-setup.sh"
    bash "$DEPLOYMENT_DIR/scripts/server-setup.sh"
    
    success "Server setup completed!"
}

# Environment configuration
configure_environment() {
    log "Configuring environment..."
    
    if [[ ! -f "$PROJECT_ROOT/.env" ]]; then
        if [[ -f "$PROJECT_ROOT/.env.example" ]]; then
            cp "$PROJECT_ROOT/.env.example" "$PROJECT_ROOT/.env"
            warning "Created .env from .env.example. Please update with your settings."
        else
            error ".env.example not found!"
        fi
    fi
    
    if [[ -f "$DEPLOYMENT_DIR/scripts/configure-env.sh" ]]; then
        chmod +x "$DEPLOYMENT_DIR/scripts/configure-env.sh"
        bash "$DEPLOYMENT_DIR/scripts/configure-env.sh"
    fi
    
    success "Environment configuration completed!"
}

# Application deployment
deploy_application() {
    log "Starting application deployment..."
    
    if [[ ! -f "$DEPLOYMENT_DIR/scripts/deploy.sh" ]]; then
        error "Deployment script not found!"
    fi
    
    chmod +x "$DEPLOYMENT_DIR/scripts/deploy.sh"
    bash "$DEPLOYMENT_DIR/scripts/deploy.sh" "$ENVIRONMENT"
    
    success "Application deployment completed!"
}

# Health check
health_check() {
    log "Running health check..."
    
    # Check if services are running
    if command -v systemctl &> /dev/null; then
        services=("nginx" "mysql" "redis-server")
        for service in "${services[@]}"; do
            if systemctl is-active --quiet "$service"; then
                success "$service is running"
            else
                warning "$service is not running"
            fi
        done
    fi
    
    # Check PHP-FPM
    if systemctl is-active --quiet "php8.3-fpm"; then
        success "PHP-FPM is running"
    else
        warning "PHP-FPM is not running"
    fi
    
    success "Health check completed!"
}

# Main execution
main() {
    check_environment
    
    case $ACTION in
        "setup")
            setup_server
            configure_environment
            ;;
        "deploy")
            configure_environment
            deploy_application
            health_check
            ;;
        "full")
            setup_server
            configure_environment
            deploy_application
            health_check
            ;;
        "health")
            health_check
            ;;
        *)
            echo "Usage: $0 [setup|deploy|full|health] [environment]"
            echo ""
            echo "Actions:"
            echo "  setup   - Setup server environment (requires sudo)"
            echo "  deploy  - Deploy application only"
            echo "  full    - Complete setup and deployment (requires sudo)"
            echo "  health  - Run health check only"
            echo ""
            echo "Environment: production (default), staging, development"
            exit 1
            ;;
    esac
    
    success "🎉 Deployment process completed successfully!"
}

# Run main function
main "$@"