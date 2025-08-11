# SiteeBlue Deployment

This directory contains deployment scripts and configurations for the SiteeBlue Laravel application, including the new **Clean Deployment System** with integrated dev-tools management.

## 🚀 Quick Start

### Clean Deployment (Recommended)
```bash
# Production deployment (excludes dev-tools)
./scripts/clean-deployment.sh production

# Staging deployment (includes dev-tools)
./scripts/clean-deployment.sh staging --include-dev-tools
```

### Traditional Deployment
```bash
# Full deployment with server setup
./scripts/deploy.sh production

# Files-only deployment
./scripts/deploy-files-only.sh
```

## 📁 Folder Structure

```
deployment/
├── scripts/                    # Deployment scripts
│   ├── clean-deployment.sh     # 🆕 Clean deployment with dev-tools integration
│   ├── deploy.sh              # Traditional full deployment
│   └── deploy-files-only.sh   # Files-only deployment
├── configs/                    # Configuration files
│   ├── clean-deployment.conf   # 🆕 Clean deployment settings
│   └── deployment.conf         # Traditional deployment settings
├── templates/                  # Template files
├── docs/                       # 🆕 Documentation
│   └── clean-deployment-guide.md  # Comprehensive deployment guide
└── logs/                      # Deployment logs
```

## 🛠️ Dev-Tools Integration

The clean deployment system includes optimized development tools:

### Optimized Viewer Files
- **admin-testing-suite.html** - Admin functionality testing (60% code reduction)
- **unified-viewer.html** - Site map and route visualization (70% code reduction)  
- **report-viewer.html** - Advanced report generation with dynamic rendering

### Environment-Specific Behavior
| Environment | Dev-Tools | Optimization | Debug Mode |
|-------------|-----------|--------------|------------|
| Production  | ❌ Excluded | Maximum     | ❌ Off      |
| Staging     | ✅ Included | Standard    | ✅ On       |
| Development | ✅ Included | Minimal     | ✅ On       |

## 🔧 Configuration

### Clean Deployment Settings
Edit `configs/clean-deployment.conf` to customize:
- Environment-specific settings
- Dev-tools inclusion rules
- Performance optimization levels
- Backup and security configurations

### File Exclusions
The `.deployignore` file controls deployment exclusions:
```
# Development tools (excluded from production)
dev-tools/
LessonModal.vue
Untitled-*

# Testing and dependencies
tests/
node_modules/
vendor/
```

## 🚀 Quick Deployment Steps

### 1. Server Setup (First Time Only)
```bash
# On your server
sudo bash deployment/scripts/server-setup.sh
```

### 2. Configure Environment
```bash
# Copy and configure environment file
cp .env.example .env
bash deployment/scripts/configure-env.sh
```

### 3. Deploy Application
```bash
# Run deployment script
bash deployment/scripts/deploy.sh production
```

## 🔧 GitHub Actions Setup

Required secrets in your GitHub repository:
- `SSH_KEY` - Private SSH key for server access
- `HOST` - Server IP address
- `USERNAME` - Server username
- `PORT` - SSH port (default: 22)
- `APP_URL` - Application URL for health checks

## 📋 Prerequisites

- Ubuntu/Debian server
- PHP 8.3+
- Node.js 20+
- MySQL 8.0+
- Redis
- Nginx

## 🔗 Related Files

- CI/CD Pipeline: `.github/workflows/ci-cd.yml`
- Environment Template: `.env.example`
- Application Config: `config/`

## 📖 Documentation

For detailed instructions, see `docs/DEPLOYMENT.md`

## 🆘 Troubleshooting

### Common Issues
1. **502 Bad Gateway**: Check PHP-FPM status
2. **Permission Denied**: Verify file permissions
3. **Database Connection**: Check credentials in `.env`

### Logs Location
- Application: `storage/logs/`
- Nginx: `/var/log/nginx/`
- PHP-FPM: `/var/log/php8.3-fpm.log`

## 🔄 Rollback

Backups are automatically created in `/var/backups/siteeblue/`

```bash
# Restore from backup
sudo tar -xzf /var/backups/siteeblue/backup-YYYYMMDD-HHMMSS.tar.gz -C /var/www/siteeblue
```