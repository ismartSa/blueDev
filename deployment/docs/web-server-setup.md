# Web Server Configuration Guide

This guide covers the complete setup of Nginx web server with SSL certificates for Laravel-Brive production deployment.

## Prerequisites

- Ubuntu/Debian server with root access
- Domain name pointing to your server IP
- Laravel-Brive application deployed
- PHP 8.1+ and PHP-FPM installed

## 1. Install Nginx

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install Nginx
sudo apt install nginx -y

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Check status
sudo systemctl status nginx
```

## 2. Configure Nginx for Laravel-Brive

### Copy Configuration File

```bash
# Copy the optimized nginx configuration
sudo cp /path/to/laravel-brive/deployment/configs/nginx.conf /etc/nginx/sites-available/laravel-brive

# Create symbolic link to enable the site
sudo ln -s /etc/nginx/sites-available/laravel-brive /etc/nginx/sites-enabled/

# Remove default nginx site
sudo rm /etc/nginx/sites-enabled/default
```

### Update Configuration

Edit `/etc/nginx/sites-available/laravel-brive` and update:

1. **Server Name**: Replace `laravel-brive.com` with your actual domain
2. **Root Path**: Update `/var/www/laravel-brive/public` to your actual path
3. **SSL Paths**: Update certificate paths if different
4. **Admin IPs**: Add your admin IP addresses for Telescope access

### Test Configuration

```bash
# Test nginx configuration
sudo nginx -t

# If successful, reload nginx
sudo systemctl reload nginx
```

## 3. SSL Certificate Setup

### Automatic Setup (Recommended)

Use the provided SSL generation script:

```bash
# Make script executable (already done)
chmod +x deployment/scripts/generate-ssl.sh

# Run the SSL setup script
sudo ./deployment/scripts/generate-ssl.sh
```

The script will:
- Install Certbot
- Generate Let's Encrypt SSL certificates
- Configure automatic renewal
- Set up proper file permissions
- Test the HTTPS connection

### Manual SSL Setup

If you prefer manual setup:

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Generate certificate
sudo certbot --nginx -d laravel-brive.com -d www.laravel-brive.com

# Test automatic renewal
sudo certbot renew --dry-run
```

## 4. PHP-FPM Configuration

### Install PHP-FPM

```bash
# Install PHP 8.1 FPM
sudo apt install php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip php8.1-gd php8.1-intl -y

# Start and enable PHP-FPM
sudo systemctl start php8.1-fpm
sudo systemctl enable php8.1-fpm
```

### Optimize PHP-FPM

Edit `/etc/php/8.1/fpm/pool.d/www.conf`:

```ini
; Process management
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

; Performance tuning
request_terminate_timeout = 300
rlimit_files = 65536
rlimit_core = 0
```

Edit `/etc/php/8.1/fpm/php.ini`:

```ini
; Memory and execution limits
memory_limit = 512M
max_execution_time = 300
max_input_time = 300

; File upload limits
upload_max_filesize = 100M
post_max_size = 100M
max_file_uploads = 20

; OPcache settings
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.1-fpm
```

## 5. Security Configuration

### Firewall Setup

```bash
# Install UFW if not installed
sudo apt install ufw -y

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable

# Check status
sudo ufw status
```

### Additional Security

1. **Hide Nginx Version**:
   Add to `/etc/nginx/nginx.conf` in the `http` block:
   ```nginx
   server_tokens off;
   ```

2. **Rate Limiting**:
   Add to `/etc/nginx/nginx.conf` in the `http` block:
   ```nginx
   limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
   limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
   ```

3. **Fail2Ban** (Optional):
   ```bash
   sudo apt install fail2ban -y
   sudo systemctl enable fail2ban
   sudo systemctl start fail2ban
   ```

## 6. Performance Optimization

### Enable HTTP/2

HTTP/2 is already enabled in the nginx configuration.

### Enable Brotli Compression (Optional)

```bash
# Install nginx brotli module
sudo apt install nginx-module-brotli -y

# Add to /etc/nginx/nginx.conf
load_module modules/ngx_http_brotli_filter_module.so;
load_module modules/ngx_http_brotli_static_module.so;
```

Add brotli configuration:

```nginx
# Brotli compression
brotli on;
brotli_comp_level 6;
brotli_types
    text/plain
    text/css
    text/xml
    text/javascript
    application/javascript
    application/xml+rss
    application/json;
```

## 7. Monitoring and Logs

### Log Rotation

Create `/etc/logrotate.d/laravel-brive`:

```
/var/log/nginx/laravel-brive-*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data adm
    postrotate
        systemctl reload nginx
    endscript
}
```

### Health Check Script

Create `/usr/local/bin/laravel-brive-health.sh`:

```bash
#!/bin/bash
# Health check script for Laravel-Brive

DOMAIN="laravel-brive.com"
LOG_FILE="/var/log/laravel-brive-health.log"

echo "$(date): Starting health check..." >> $LOG_FILE

# Check HTTPS
if curl -s -I https://$DOMAIN | grep -q "200 OK"; then
    echo "$(date): HTTPS OK" >> $LOG_FILE
else
    echo "$(date): HTTPS FAILED" >> $LOG_FILE
fi

# Check SSL certificate expiry
EXPIRY=$(openssl s_client -connect $DOMAIN:443 -servername $DOMAIN 2>/dev/null | openssl x509 -noout -enddate | cut -d= -f2)
EXPIRY_EPOCH=$(date -d "$EXPIRY" +%s)
CURRENT_EPOCH=$(date +%s)
DAYS_LEFT=$(( (EXPIRY_EPOCH - CURRENT_EPOCH) / 86400 ))

if [ $DAYS_LEFT -lt 30 ]; then
    echo "$(date): SSL certificate expires in $DAYS_LEFT days - RENEWAL NEEDED" >> $LOG_FILE
else
    echo "$(date): SSL certificate OK ($DAYS_LEFT days left)" >> $LOG_FILE
fi
```

Make executable and add to cron:

```bash
sudo chmod +x /usr/local/bin/laravel-brive-health.sh
(crontab -l 2>/dev/null; echo "0 */6 * * * /usr/local/bin/laravel-brive-health.sh") | crontab -
```

## 8. Troubleshooting

### Common Issues

1. **502 Bad Gateway**:
   - Check PHP-FPM status: `sudo systemctl status php8.1-fpm`
   - Check socket permissions: `ls -la /var/run/php/`
   - Check nginx error logs: `sudo tail -f /var/log/nginx/laravel-brive-error.log`

2. **SSL Certificate Issues**:
   - Verify certificate files exist and have correct permissions
   - Check certificate validity: `openssl x509 -in /etc/ssl/certs/laravel-brive.crt -text -noout`
   - Test SSL: `openssl s_client -connect laravel-brive.com:443`

3. **Permission Issues**:
   - Ensure web root has correct ownership: `sudo chown -R www-data:www-data /var/www/laravel-brive`
   - Set proper permissions: `sudo chmod -R 755 /var/www/laravel-brive`

### Useful Commands

```bash
# Check nginx status
sudo systemctl status nginx

# Test nginx configuration
sudo nginx -t

# Reload nginx
sudo systemctl reload nginx

# Check SSL certificate
openssl x509 -in /etc/ssl/certs/laravel-brive.crt -text -noout

# Monitor logs
sudo tail -f /var/log/nginx/laravel-brive-access.log
sudo tail -f /var/log/nginx/laravel-brive-error.log

# Check PHP-FPM
sudo systemctl status php8.1-fpm
```

## 9. Final Steps

After completing the web server setup:

1. Update your Laravel `.env` file:
   ```env
   APP_URL=https://laravel-brive.com
   SESSION_SECURE_COOKIE=true
   ```

2. Clear Laravel caches:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. Test all application functionality
4. Set up monitoring and alerting
5. Create backup procedures

## Security Checklist

- [ ] SSL certificates installed and working
- [ ] HTTP to HTTPS redirect configured
- [ ] Security headers enabled
- [ ] Rate limiting configured
- [ ] Sensitive files blocked
- [ ] Firewall configured
- [ ] Regular security updates scheduled
- [ ] Log monitoring in place
- [ ] Backup procedures tested

Your Laravel-Brive application is now ready for production with a secure, optimized web server configuration!