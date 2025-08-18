# SiteeBlue - Traditional Server Deployment (No Docker)

This project is configured for traditional server deployment without Docker containers. All deployment scripts and configurations are optimized for direct server installation.

## 📁 Deployment Files Overview

### Core Deployment Scripts
- **`deploy.sh`** - Main deployment script for code updates and application optimization
- **`server-setup.sh`** - Complete server environment setup (PHP, Nginx, MySQL, Node.js, Redis)
- **`configure-env.sh`** - Interactive environment configuration script

### Configuration Files
- **`nginx.conf`** - Nginx web server configuration with security headers and caching
- **`.github/workflows/ci-cd.yml`** - GitHub Actions CI/CD pipeline for automated deployment
- **`DEPLOYMENT.md`** - Comprehensive deployment documentation

## 🚀 Quick Start Deployment

### 1. Server Setup (One-time)
```bash
# On your Ubuntu/Debian server
wget https://raw.githubusercontent.com/your-repo/siteeblue/main/server-setup.sh
sudo bash server-setup.sh
```

### 2. Application Deployment
```bash
# Clone repository
sudo -u www-data git clone https://github.com/your-repo/siteeblue.git /var/www/siteeblue
cd /var/www/siteeblue

# Configure environment
./configure-env.sh production

# Deploy application
./deploy.sh production
```

### 3. CI/CD Setup
Add these GitHub secrets:
- `HOST` - Server IP address
- `USERNAME` - SSH username (deploy)
- `SSH_KEY` - Private SSH key
- `PORT` - SSH port (default: 9040)
- `APP_URL` - Application URL

## 🏗️ Architecture

### Server Stack
- **Web Server**: Nginx with PHP-FPM
- **Database**: MySQL 8.0+
- **Cache**: Redis
- **Queue**: Supervisor + Redis
- **Assets**: Node.js 20+ for building

### Security Features
- Firewall configuration (UFW)
- SSL/TLS support (Let's Encrypt ready)
- Secure file permissions
- Environment variable protection
- Security headers in Nginx

### Performance Optimizations
- Gzip compression
- Static asset caching
- PHP OPcache
- Redis caching
- Optimized Nginx configuration

## 📊 Monitoring & Maintenance

### Log Locations
- Application: `/var/www/siteeblue/storage/logs/`
- Nginx: `/var/log/nginx/siteeblue_*.log`
- PHP-FPM: `/var/log/php8.3-fpm.log`
- Queue Workers: `/var/www/siteeblue/storage/logs/worker.log`

### Health Checks
```bash
# Service status
sudo systemctl status nginx php8.3-fpm mysql redis-server

# Queue workers
sudo supervisorctl status siteeblue-worker:*

# Application health
curl -I https://your-domain.com
```

## 🔄 Deployment Workflow

1. **Code Push** → GitHub repository
2. **CI Pipeline** → Automated testing
3. **CD Pipeline** → SSH deployment to server
4. **Health Check** → Verify deployment success

### Manual Deployment
```bash
cd /var/www/siteeblue
git pull origin main
./deploy.sh production
```

## 🛡️ Security Checklist

- ✅ Firewall configured (UFW)
- ✅ SSL certificate (Let's Encrypt)
- ✅ Secure file permissions
- ✅ Environment variables protected
- ✅ Database user with limited privileges
- ✅ Regular security updates
- ✅ Backup strategy implemented

## 📚 Documentation

For detailed instructions, see:
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Complete deployment guide
- **[Server Setup](server-setup.sh)** - Automated server configuration
- **[Deployment Script](deploy.sh)** - Application deployment process

## 🆘 Support

For deployment issues:
1. Check logs in `/var/www/siteeblue/storage/logs/`
2. Verify service status: `sudo systemctl status nginx php8.3-fpm mysql`
3. Test database connection: `php artisan tinker`
4. Review deployment documentation

---

**Note**: This deployment setup is optimized for traditional server environments and does not use Docker containers, as requested.