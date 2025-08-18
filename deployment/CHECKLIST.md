# Deployment Checklist

## 🔧 Pre-Deployment Setup

### Server Requirements
- [ ] Ubuntu/Debian server with root access
- [ ] Domain name configured (A record pointing to server IP)
- [ ] SSH key pair generated
- [ ] Server firewall configured (ports 80, 443, 22)

### GitHub Repository Setup
- [ ] Repository pushed to GitHub
- [ ] GitHub Actions enabled
- [ ] Required secrets configured:
  - [ ] `SSH_KEY` - Private SSH key
  - [ ] `HOST` - Server IP address
  - [ ] `USERNAME` - Server username (deploy/root)
  - [ ] `PORT` - SSH port (default: 22)
  - [ ] `APP_URL` - Application URL

## 🚀 Initial Server Setup

### 1. Server Preparation
- [ ] Connect to server via SSH
- [ ] Update system packages: `sudo apt update && sudo apt upgrade -y`
- [ ] Clone repository to server
- [ ] Navigate to project directory

### 2. Run Server Setup
```bash
# Make script executable
chmod +x deployment/deploy-master.sh

# Run server setup (requires sudo)
sudo ./deployment/deploy-master.sh setup
```

### 3. Verify Server Setup
- [ ] PHP 8.3 installed and configured
- [ ] Nginx installed and running
- [ ] MySQL installed and secured
- [ ] Redis installed and running
- [ ] Composer installed globally
- [ ] Node.js 20+ installed
- [ ] Supervisor configured for queues

## 🔐 Environment Configuration

### 1. Database Setup
- [ ] MySQL database created: `siteeblue`
- [ ] Database user created with proper permissions
- [ ] Database credentials noted for .env file

### 2. Environment File
- [ ] Copy appropriate template: `cp deployment/templates/.env.production .env`
- [ ] Update database credentials
- [ ] Generate new APP_KEY: `php artisan key:generate`
- [ ] Configure mail settings
- [ ] Set correct APP_URL
- [ ] Configure Redis settings (if using)

### 3. SSL Certificate (Recommended)
- [ ] Install Certbot: `sudo apt install certbot python3-certbot-nginx`
- [ ] Generate certificate: `sudo certbot --nginx -d your-domain.com`
- [ ] Verify auto-renewal: `sudo certbot renew --dry-run`

## 📦 Application Deployment

### 1. Initial Deployment
```bash
# Run deployment
./deployment/deploy-master.sh deploy production
```

### 2. Verify Deployment
- [ ] Application accessible via browser
- [ ] Database migrations completed
- [ ] Assets compiled and served correctly
- [ ] No 500/502 errors
- [ ] Log files created in `storage/logs/`

## 🔍 Post-Deployment Verification

### 1. Service Status
- [ ] Nginx running: `sudo systemctl status nginx`
- [ ] PHP-FPM running: `sudo systemctl status php8.3-fpm`
- [ ] MySQL running: `sudo systemctl status mysql`
- [ ] Redis running: `sudo systemctl status redis-server`
- [ ] Supervisor running: `sudo systemctl status supervisor`

### 2. Application Health
- [ ] Homepage loads correctly
- [ ] User registration/login works
- [ ] Database connections working
- [ ] File uploads working (if applicable)
- [ ] Email sending working (test)
- [ ] Queue jobs processing (if applicable)

### 3. Performance & Security
- [ ] HTTPS redirect working
- [ ] Gzip compression enabled
- [ ] Static assets cached properly
- [ ] Security headers configured
- [ ] File permissions correct (755/644)
- [ ] Storage directories writable

## 🔄 CI/CD Pipeline

### 1. GitHub Actions
- [ ] Push to main branch triggers deployment
- [ ] Tests pass before deployment
- [ ] Deployment completes successfully
- [ ] Health check passes

### 2. Monitoring
- [ ] Set up log monitoring
- [ ] Configure error notifications
- [ ] Monitor disk space usage
- [ ] Monitor application performance

## 🆘 Troubleshooting

### Common Issues
- **502 Bad Gateway**: Check PHP-FPM status and configuration
- **Permission Denied**: Verify file permissions and ownership
- **Database Connection**: Check credentials and MySQL status
- **Assets Not Loading**: Verify Nginx configuration and file permissions
- **Queue Jobs Not Processing**: Check Supervisor configuration

### Log Locations
- Application: `storage/logs/laravel.log`
- Nginx: `/var/log/nginx/error.log`
- PHP-FPM: `/var/log/php8.3-fpm.log`
- MySQL: `/var/log/mysql/error.log`

## 📋 Maintenance Tasks

### Daily
- [ ] Monitor application logs
- [ ] Check disk space usage
- [ ] Verify backup completion

### Weekly
- [ ] Update system packages
- [ ] Review security logs
- [ ] Test backup restoration

### Monthly
- [ ] Update SSL certificates (if not auto-renewed)
- [ ] Review and rotate log files
- [ ] Performance optimization review

---

**✅ Deployment Complete!**

Your Laravel Brive application should now be successfully deployed and running in production.