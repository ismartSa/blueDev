# Clean Deployment Guide | دليل النشر النظيف

This guide explains how to use the clean deployment system with integrated dev-tools for the SiteeBlue Laravel project.

## Overview | نظرة عامة

The clean deployment system provides:
- **Optimized file deployment** with `.deployignore` integration
- **Environment-specific configurations** (production, staging, development)
- **Dev-tools management** with conditional inclusion
- **Automated backup and rollback** capabilities
- **Performance optimization** and caching
- **Health checks** and validation

## Quick Start | البدء السريع

### Basic Deployment
```bash
# Production deployment (excludes dev-tools)
./deployment/scripts/clean-deployment.sh production

# Staging deployment (includes dev-tools)
./deployment/scripts/clean-deployment.sh staging --include-dev-tools

# Development deployment
./deployment/scripts/clean-deployment.sh development --include-dev-tools
```

### File-Only Deployment
```bash
# Deploy files only without server setup
./deployment/scripts/deploy-files-only.sh
```

## Dev-Tools Integration | تكامل أدوات التطوير

### Optimized Viewer Files
The system includes three optimized HTML viewer files:

1. **admin-testing-suite.html**
   - Comprehensive admin functionality testing
   - 60% code reduction through DRY principles
   - Object-oriented JavaScript architecture
   - CSS variables for consistent theming

2. **unified-viewer.html**
   - Site map and route visualization
   - 70% code reduction
   - Dynamic content generation
   - Modern CSS Grid layout

3. **report-viewer.html**
   - Advanced report generation
   - Dynamic rendering system
   - Performance optimizations
   - Interactive data visualization

### Environment-Specific Behavior

| Environment | Dev-Tools Included | Optimization Level | Debug Mode |
|-------------|-------------------|-------------------|------------|
| Production  | ❌ No             | Maximum           | ❌ Off      |
| Staging     | ✅ Yes            | Standard          | ✅ On       |
| Development | ✅ Yes            | Minimal           | ✅ On       |

## Configuration | التكوين

### Deployment Configuration
Edit `deployment/configs/clean-deployment.conf` to customize:

```ini
# Environment settings
[environments]
production.include_dev_tools=false
staging.include_dev_tools=true

# Dev-tools settings
[dev_tools]
viewers_path=dev-tools/viewers
include_in_staging=true
include_in_production=false
```

### File Exclusions
The `.deployignore` file controls which files are excluded:

```
# Development tools (excluded from production)
dev-tools/
LessonModal.vue
Untitled-*

# Testing files
tests/
coverage/

# Dependencies (installed on server)
node_modules/
vendor/
```

## Advanced Usage | الاستخدام المتقدم

### Custom Deployment Hooks
```bash
# Pre-deployment validation
export PRE_DEPLOY_HOOK="php artisan config:validate"

# Post-deployment notifications
export POST_DEPLOY_HOOK="curl -X POST webhook-url"

./deployment/scripts/clean-deployment.sh production
```

### Backup Management
```bash
# List available backups
ls -la backups/clean-backup-*

# Restore from backup
tar -xzf backups/clean-backup-20250101-120000.tar.gz
```

### Health Checks
The deployment script automatically performs:
- ✅ HTTP endpoint validation
- ✅ Database connectivity check
- ✅ File permissions verification
- ✅ Cache functionality test

## Troubleshooting | استكشاف الأخطاء

### Common Issues

**1. Dev-tools not accessible in staging**
```bash
# Ensure dev-tools are included
./deployment/scripts/clean-deployment.sh staging --include-dev-tools
```

**2. Permission errors**
```bash
# Fix permissions manually
sudo chown -R www-data:www-data /path/to/app
sudo chmod -R 755 /path/to/app
sudo chmod -R 775 /path/to/app/storage
```

**3. Cache issues**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Rollback Procedure
```bash
# 1. Stop services
sudo systemctl stop nginx php8.3-fpm

# 2. Restore from backup
cd /path/to/app
tar -xzf /path/to/backup/clean-backup-TIMESTAMP.tar.gz

# 3. Restart services
sudo systemctl start php8.3-fpm nginx
```

## Security Considerations | اعتبارات الأمان

### Production Security
- ❌ Dev-tools are **automatically excluded** from production
- ❌ Debug mode is **disabled** in production
- ✅ Sensitive files are **removed** during deployment
- ✅ File permissions are **properly set**

### Development Security
- ⚠️ Dev-tools are **accessible** in staging/development
- ⚠️ Debug mode is **enabled** for troubleshooting
- ✅ Access should be **restricted** to authorized users

## Performance Optimization | تحسين الأداء

### Automatic Optimizations
- **Composer autoloader optimization**
- **Laravel configuration caching**
- **Route caching**
- **View compilation**
- **Asset minification**
- **OPcache enablement**

### Manual Optimizations
```bash
# Additional performance tuning
php artisan optimize
php artisan queue:work --daemon
php artisan horizon:start
```

## Monitoring | المراقبة

### Deployment Logs
```bash
# View deployment logs
tail -f deployment/logs/clean-deployment.log

# Check system logs
sudo journalctl -u nginx -f
sudo journalctl -u php8.3-fpm -f
```

### Health Monitoring
```bash
# Manual health check
curl -f http://your-domain.com/health

# Monitor application status
php artisan queue:monitor
php artisan schedule:list
```

## Integration with CI/CD | التكامل مع CI/CD

### GitHub Actions Example
```yaml
name: Clean Deployment
on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Deploy to Production
        run: |
          ssh user@server 'cd /path/to/app && ./deployment/scripts/clean-deployment.sh production'
```

### GitLab CI Example
```yaml
deploy_production:
  stage: deploy
  script:
    - ssh user@server 'cd /path/to/app && ./deployment/scripts/clean-deployment.sh production'
  only:
    - main
```

## Support | الدعم

For issues or questions:
1. Check the troubleshooting section above
2. Review deployment logs
3. Verify configuration files
4. Test in staging environment first

---

**Note**: Always test deployments in staging environment before deploying to production.

**ملاحظة**: اختبر دائماً عمليات النشر في بيئة التجريب قبل النشر في الإنتاج.