#!/bin/bash

# SSL Certificate Generation Script for Laravel-Brive Production
# This script generates SSL certificates using Let's Encrypt (Certbot)

set -e

# Configuration
DOMAIN="laravel-brive.com"
WWW_DOMAIN="www.laravel-brive.com"
EMAIL="admin@laravel-brive.com"
WEBROOT="/var/www/laravel-brive/public"
SSL_DIR="/etc/ssl"
NGINX_AVAILABLE="/etc/nginx/sites-available"
NGINX_ENABLED="/etc/nginx/sites-enabled"

echo "🔐 Starting SSL certificate generation for Laravel-Brive..."

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo "❌ This script must be run as root (use sudo)"
   exit 1
fi

# Update system packages
echo "📦 Updating system packages..."
apt update && apt upgrade -y

# Install Certbot if not already installed
if ! command -v certbot &> /dev/null; then
    echo "📥 Installing Certbot..."
    apt install -y certbot python3-certbot-nginx
else
    echo "✅ Certbot is already installed"
fi

# Stop nginx temporarily
echo "⏸️  Stopping Nginx temporarily..."
systemctl stop nginx

# Generate SSL certificate using standalone mode
echo "🔑 Generating SSL certificate for $DOMAIN and $WWW_DOMAIN..."
certbot certonly \
    --standalone \
    --email $EMAIL \
    --agree-tos \
    --no-eff-email \
    --domains $DOMAIN,$WWW_DOMAIN

if [ $? -eq 0 ]; then
    echo "✅ SSL certificate generated successfully!"
else
    echo "❌ Failed to generate SSL certificate"
    systemctl start nginx
    exit 1
fi

# Create symbolic links for easier nginx configuration
echo "🔗 Creating SSL certificate symbolic links..."
mkdir -p $SSL_DIR/certs $SSL_DIR/private

# Link certificates
ln -sf /etc/letsencrypt/live/$DOMAIN/fullchain.pem $SSL_DIR/certs/laravel-brive.crt
ln -sf /etc/letsencrypt/live/$DOMAIN/privkey.pem $SSL_DIR/private/laravel-brive.key

# Set proper permissions
chmod 644 $SSL_DIR/certs/laravel-brive.crt
chmod 600 $SSL_DIR/private/laravel-brive.key

# Test nginx configuration
echo "🧪 Testing Nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Nginx configuration is valid"
    # Start nginx
    systemctl start nginx
    systemctl enable nginx
else
    echo "❌ Nginx configuration has errors"
    exit 1
fi

# Set up automatic renewal
echo "🔄 Setting up automatic SSL certificate renewal..."

# Create renewal script
cat > /usr/local/bin/renew-laravel-brive-ssl.sh << 'EOF'
#!/bin/bash
# Auto-renewal script for Laravel-Brive SSL certificates

echo "$(date): Starting SSL certificate renewal check..."

# Renew certificates
certbot renew --quiet --no-self-upgrade

# Reload nginx if certificates were renewed
if [ $? -eq 0 ]; then
    echo "$(date): Checking if nginx reload is needed..."
    systemctl reload nginx
    echo "$(date): SSL certificate renewal completed successfully"
else
    echo "$(date): SSL certificate renewal failed"
fi
EOF

# Make renewal script executable
chmod +x /usr/local/bin/renew-laravel-brive-ssl.sh

# Add cron job for automatic renewal (runs twice daily)
echo "📅 Adding cron job for automatic renewal..."
(crontab -l 2>/dev/null; echo "0 2,14 * * * /usr/local/bin/renew-laravel-brive-ssl.sh >> /var/log/ssl-renewal.log 2>&1") | crontab -

# Create log file
touch /var/log/ssl-renewal.log
chmod 644 /var/log/ssl-renewal.log

# Display certificate information
echo "📋 SSL Certificate Information:"
echo "   Domain: $DOMAIN, $WWW_DOMAIN"
echo "   Certificate: $SSL_DIR/certs/laravel-brive.crt"
echo "   Private Key: $SSL_DIR/private/laravel-brive.key"
echo "   Expiry: $(openssl x509 -in $SSL_DIR/certs/laravel-brive.crt -noout -enddate | cut -d= -f2)"

# Test HTTPS connection
echo "🌐 Testing HTTPS connection..."
if curl -s -I https://$DOMAIN | grep -q "200 OK"; then
    echo "✅ HTTPS is working correctly!"
else
    echo "⚠️  HTTPS test failed - please check your configuration"
fi

echo "🎉 SSL certificate setup completed successfully!"
echo "📝 Next steps:"
echo "   1. Update your .env file with HTTPS URLs"
echo "   2. Update APP_URL to https://$DOMAIN"
echo "   3. Test all application functionality"
echo "   4. Monitor /var/log/ssl-renewal.log for renewal status"

exit 0