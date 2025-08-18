#!/bin/bash

# SiteeBlue Server Setup Script
# Run this script on a fresh Ubuntu/Debian server
# Usage: sudo bash server-setup.sh

set -e

PHP_VERSION="8.3"
NODE_VERSION="20"
APP_DIR="/var/www/siteeblue"
DOMAIN="your-domain.com"

echo "🚀 Setting up SiteeBlue server environment..."

# Update system packages
echo "📦 Updating system packages..."
apt update && apt upgrade -y

# Install essential packages
echo "🔧 Installing essential packages..."
apt install -y curl wget git unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release

# Add PHP repository
echo "🐘 Adding PHP repository..."
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP and extensions
echo "📦 Installing PHP $PHP_VERSION and extensions..."
apt install -y php$PHP_VERSION php$PHP_VERSION-fpm php$PHP_VERSION-mysql php$PHP_VERSION-xml php$PHP_VERSION-gd php$PHP_VERSION-curl php$PHP_VERSION-mbstring php$PHP_VERSION-zip php$PHP_VERSION-bcmath php$PHP_VERSION-intl php$PHP_VERSION-redis php$PHP_VERSION-imagick

# Install Nginx
echo "🌐 Installing Nginx..."
apt install -y nginx

# Install MySQL
echo "🗄️ Installing MySQL..."
apt install -y mysql-server

# Secure MySQL installation
echo "🔐 Securing MySQL..."
mysql_secure_installation

# Install Composer
echo "🎼 Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js and npm
echo "📦 Installing Node.js $NODE_VERSION..."
curl -fsSL https://deb.nodesource.com/setup_$NODE_VERSION.x | bash -
apt install -y nodejs

# Install Redis
echo "🔴 Installing Redis..."
apt install -y redis-server
systemctl enable redis-server
systemctl start redis-server

# Install Supervisor for queue management
echo "👷 Installing Supervisor..."
apt install -y supervisor

# Create application directory
echo "📁 Creating application directory..."
mkdir -p $APP_DIR
chown -R www-data:www-data $APP_DIR

# Configure PHP-FPM
echo "⚙️ Configuring PHP-FPM..."
sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/$PHP_VERSION/fpm/php.ini
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/$PHP_VERSION/fpm/php.ini
sed -i 's/post_max_size = 8M/post_max_size = 100M/' /etc/php/$PHP_VERSION/fpm/php.ini
sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/$PHP_VERSION/fpm/php.ini
sed -i 's/memory_limit = 128M/memory_limit = 512M/' /etc/php/$PHP_VERSION/fpm/php.ini

# Restart PHP-FPM
systemctl restart php$PHP_VERSION-fpm
systemctl enable php$PHP_VERSION-fpm

# Configure Nginx
echo "🌐 Configuring Nginx..."
cp nginx.conf /etc/nginx/sites-available/siteeblue
ln -sf /etc/nginx/sites-available/siteeblue /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx
systemctl enable nginx

# Create database and user
echo "🗄️ Setting up database..."
mysql -e "CREATE DATABASE IF NOT EXISTS siteeblue CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'siteeblue'@'localhost' IDENTIFIED BY 'secure_password_here';"
mysql -e "GRANT ALL PRIVILEGES ON siteeblue.* TO 'siteeblue'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Configure firewall
echo "🔥 Configuring firewall..."
ufw allow 9040/tcp
ufw allow 'Nginx Full'
ufw --force enable

# Create deployment user
echo "👤 Creating deployment user..."
useradd -m -s /bin/bash deploy
usermod -aG www-data deploy
usermod -aG sudo deploy

# Set up SSH key for deployment (you'll need to add your public key)
echo "🔑 Setting up SSH for deployment user..."
mkdir -p /home/deploy/.ssh
chown deploy:deploy /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
echo "# Add your public SSH key here" > /home/deploy/.ssh/authorized_keys
chown deploy:deploy /home/deploy/.ssh/authorized_keys
chmod 600 /home/deploy/.ssh/authorized_keys

# Create supervisor configuration for Laravel queues
echo "👷 Setting up queue worker..."
cat > /etc/supervisor/conf.d/siteeblue-worker.conf << EOF
[program:siteeblue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/worker.log
stopwaitsecs=3600
EOF

supervisorctl reread
supervisorctl update

# Create log rotation
echo "📝 Setting up log rotation..."
cat > /etc/logrotate.d/siteeblue << EOF
$APP_DIR/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 0644 www-data www-data
    postrotate
        systemctl reload php$PHP_VERSION-fpm
    endscript
}
EOF

# Make deployment script executable
chmod +x deploy.sh

echo "✅ Server setup completed!"
echo "📋 Next steps:"
echo "1. Update domain name in /etc/nginx/sites-available/siteeblue"
echo "2. Add your SSH public key to /home/deploy/.ssh/authorized_keys"
echo "3. Update database credentials in your .env file"
echo "4. Clone your repository to $APP_DIR"
echo "5. Run the deployment script: ./deploy.sh"
echo "6. Configure SSL certificate (recommended)"