# SiteeBlue Deployment Guide

This guide covers deploying SiteeBlue Laravel application to traditional servers without Docker.

## Prerequisites

- Ubuntu/Debian server with root access
- Domain name pointing to your server
- SSH access to the server

## Server Requirements

- PHP 8.3+
- Nginx
- MySQL 8.0+
- Node.js 20+
- Redis
- Composer
- Supervisor

## Quick Setup

### 1. Initial Server Setup

Run the automated server setup script:

```bash
# On your server
wget https://raw.githubusercontent.com/your-repo/siteeblue/main/server-setup.sh
sudo bash server-setup.sh
```

### 2. Manual Configuration

After running the setup script, configure these items:

#### Update Nginx Configuration
```bash
sudo nano /etc/nginx/sites-available/siteeblue
# Update server_name with your domain
sudo nginx -t
sudo systemctl reload nginx
```

#### Add SSH Key for Deployment
```bash
sudo nano /home/deploy/.ssh/authorized_keys
# Add your public SSH key
```

#### Create Database
```bash
mysql -u root -p
CREATE DATABASE siteeblue CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'siteeblue'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON siteeblue.* TO 'siteeblue'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Application Deployment

#### Clone Repository
```bash
sudo -u www-data git clone https://github.com/your-repo/siteeblue.git /var/www/siteeblue
cd /var/www/siteeblue
```

#### Configure Environment
```bash
sudo -u www-data cp .env.example .env
sudo -u www-data nano .env
```

Update these variables in `.env`:
```env
APP_NAME=SiteeBlue
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siteeblue
DB_USERNAME=siteeblue
DB_PASSWORD=your_secure_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

#### Run Initial Deployment
```bash
chmod +x deploy.sh
./deploy.sh production
```

## CI/CD Setup

### GitHub Secrets

Add these secrets to your GitHub repository:

- `HOST`: Your server IP address
- `USERNAME`: SSH username (deploy)
- `SSH_KEY`: Private SSH key for deployment
- `PORT`: SSH port (default: 9040)
- `APP_URL`: Your application URL

### Deployment Workflow

The CI/CD pipeline automatically:
1. Runs tests on pull requests
2. Deploys to production on main branch pushes
3. Performs health checks after deployment

## SSL Certificate Setup

### Using Let's Encrypt (Recommended)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

## Monitoring and Maintenance

### Log Files

- Application logs: `/var/www/siteeblue/storage/logs/`
- Nginx logs: `/var/log/nginx/siteeblue_*.log`
- PHP-FPM logs: `/var/log/php8.3-fpm.log`
- Queue worker logs: `/var/www/siteeblue/storage/logs/worker.log`

### Useful Commands

```bash
# Check application status
sudo systemctl status nginx php8.3-fpm mysql redis-server

# Monitor queue workers
sudo supervisorctl status siteeblue-worker:*

# Restart services
sudo systemctl restart nginx php8.3-fpm
sudo supervisorctl restart siteeblue-worker:*

# View logs
tail -f /var/www/siteeblue/storage/logs/laravel.log
tail -f /var/log/nginx/siteeblue_error.log
```

### Database Backup

```bash
# Create backup script
sudo nano /usr/local/bin/backup-siteeblue.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/siteeblue"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR
mysqldump -u siteeblue -p siteeblue > $BACKUP_DIR/siteeblue_$DATE.sql
gzip $BACKUP_DIR/siteeblue_$DATE.sql

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete
```

```bash
# Make executable and schedule
sudo chmod +x /usr/local/bin/backup-siteeblue.sh
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-siteeblue.sh
```

## Performance Optimization

### PHP-FPM Tuning

```bash
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

Optimize these settings based on your server resources:
```ini
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### MySQL Optimization

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Add optimizations:
```ini
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
query_cache_type = 1
query_cache_size = 64M
```

### Redis Configuration

```bash
sudo nano /etc/redis/redis.conf
```

Optimize for Laravel:
```ini
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

## Troubleshooting

### Common Issues

1. **Permission Errors**
   ```bash
   sudo chown -R www-data:www-data /var/www/siteeblue
   sudo chmod -R 755 /var/www/siteeblue
   sudo chmod -R 775 /var/www/siteeblue/storage
   sudo chmod -R 775 /var/www/siteeblue/bootstrap/cache
   ```

2. **Queue Workers Not Running**
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start siteeblue-worker:*
   ```

3. **Database Connection Issues**
   ```bash
   # Test connection
   php artisan tinker
   DB::connection()->getPdo();
   ```

4. **Asset Loading Issues**
   ```bash
   npm run build
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Health Checks

```bash
# Application health
curl -I https://your-domain.com

# Database health
mysql -u siteeblue -p -e "SELECT 1;"

# Redis health
redis-cli ping

# Queue health
php artisan queue:work --once
```

## Security Considerations

1. **Firewall Configuration**
   ```bash
   sudo ufw allow 9040/tcp  # Custom SSH port
   sudo ufw allow 'Nginx Full'
   sudo ufw enable
   ```

2. **Regular Updates**
   ```bash
   sudo apt update && sudo apt upgrade
   composer update
   npm update
   ```

3. **File Permissions**
   - Never set 777 permissions
   - Keep sensitive files out of web root
   - Regular security audits

4. **Environment Variables**
   - Use strong passwords
   - Rotate API keys regularly
   - Never commit secrets to version control

## Support

For deployment issues:
1. Check application logs
2. Verify server requirements
3. Test individual components
4. Review this documentation

For additional help, contact the development team.