# Screenshots
<p align="center">
  <img alt="Light" src="https://i.postimg.cc/HLjcWFM4/Snipaste-2022-12-26-13-47-15.png" width="45%">&nbsp;
  <img alt="Dark" src="https://i.postimg.cc/X7QYZQ42/Snipaste-2022-12-26-13-47-21.png" width="45%">
  <img alt="Login" src="https://i.postimg.cc/8zcXDPZy/Snipaste-2022-12-26-13-58-34.png" width="45%">&nbsp;
  <img alt="Register" src="https://i.postimg.cc/wMbJkHDv/Snipaste-2022-12-26-13-59-48.png" width="45%">
  <img alt="Dashboard" src="https://i.postimg.cc/XJqYk5Lf/Snipaste-2022-12-26-14-01-12.png" width="45%">&nbsp;
  <img alt="User" src="https://i.postimg.cc/wj9ys7CV/Snipaste-2022-12-26-14-01-58.png" width="45%">
  <img alt="User" src="https://i.postimg.cc/DwWZGLC1/Snipaste-2022-12-26-14-10-29.png" width="45%">&nbsp;
  <img alt="Permission" src="https://i.postimg.cc/QC6PkB6T/Snipaste-2022-12-26-14-04-01.png" width="45%">
  <img alt="Modal Form" src="https://i.postimg.cc/vmcWc4nK/Snipaste-2022-12-26-14-08-50.png" width="45%">&nbsp;
  <img alt="Modal Confirm" src="https://i.postimg.cc/7L3q1VSJ/Snipaste-2022-12-26-14-11-02.png" width="45%">
  <img alt="Toast Notification Success" src="https://i.postimg.cc/pXF4ndfp/Snipaste-2022-12-26-14-11-53.png" width="45%">&nbsp;
  <img alt="Toast Notification Error" src="https://i.postimg.cc/TPp4fY3d/Snipaste-2022-12-26-14-13-50.png" width="45%">
</p>

# Laravel-Brive

A modern, production-ready Laravel application built with Vue.js 3, Inertia.js, and Tailwind CSS. Features enterprise-grade deployment infrastructure with comprehensive monitoring, security, and performance optimizations.

## 🚀 Features

### Core Application
- **Role Based Access Control** - Comprehensive permission system
- **Responsive Design** - Mobile-first responsive interface
- **Modal Forms** - Interactive modal-based forms
- **Bulk Actions** - Efficient batch operations
- **Light/Dark Mode** - Theme switching capability
- **Toast Notifications** - User-friendly notifications
- **Rich Datatable** - Server-side processing with advanced features
- **Tooltips** - Contextual help and information
- **Localization** - Multi-language support (EN/ID)
- **SSR** - Server-side rendering for better SEO

### Production Infrastructure
- **Automated Deployment** - One-command production setup
- **Performance Optimization** - Caching, compression, and asset optimization
- **Security Hardening** - SSL/TLS, security headers, secure configurations
- **Monitoring & Analytics** - Laravel Telescope, health checks, error tracking
- **Backup System** - Automated database and file backups with S3 integration
- **Queue Processing** - Redis-based queues with multiple priorities
- **Web Server Optimization** - Nginx/Apache configurations with performance tuning
## 📋 Requirements

- **PHP** 8.1 or higher
- **Node.js** 18+ and npm
- **Database** MySQL 8.0+ or PostgreSQL 13+
- **Redis** 6.0+ (for queues and caching)
- **Composer** 2.0+
- **Web Server** Nginx (recommended) or Apache
## 🛠 Installation

### Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/ismartSa/blueDev.git laravel-brive
   cd laravel-brive
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   # Configure your database in .env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=brive
   DB_USERNAME=root
   DB_PASSWORD=
   
   # Run migrations and seeders
   php artisan migrate:fresh --seed
   ```

5. **Build assets and start development**
   ```bash
   npm run dev
   php artisan serve
   ```

### Production Deployment

For production deployment, use the automated setup scripts:

```bash
# Make scripts executable
chmod +x deployment/scripts/*.sh

# Run main production setup
./deployment/scripts/setup-production.sh
```

For detailed deployment instructions, see [Production Deployment Guide](deployment/docs/production-deployment.md).
## Login With
### Superadmin
``` bash
email : superadmin@superadmin.com
password : superadmin
```
### Admin
``` bash
email : admin@admin.com
password : admin
```
### Operator
``` bash
email : operator@operator.com
password : operator
```
## 📚 Documentation

### Deployment Guides
- [Production Deployment](deployment/docs/production-deployment.md) - Complete production setup guide
- [Monitoring Setup](deployment/docs/monitoring-setup.md) - Analytics and monitoring configuration
- [Backup System](deployment/docs/backup-system.md) - Automated backup and recovery
- [Queue Workers](deployment/docs/queue-workers-setup.md) - Queue processing and job management
- [Web Server Configuration](deployment/docs/web-server-config.md) - Nginx/Apache optimization

## 🔧 Development Commands

```bash
# Development
npm run dev          # Start development server
npm run build        # Build for production
npm run watch        # Watch for changes

# Laravel
php artisan serve    # Start Laravel development server
php artisan migrate  # Run database migrations
php artisan queue:work # Start queue worker

# Production
php artisan optimize # Optimize for production
php artisan config:cache # Cache configuration
php artisan route:cache  # Cache routes
php artisan view:cache   # Cache views
```

## 📦 Key Packages
- [Vue.js 3](https://vuejs.org/) - Progressive JavaScript framework
- [Inertia.js](https://inertiajs.com/) - Modern monolith approach
- [Tailwind CSS](https://tailwindcss.com/) - Utility-first CSS framework
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v5/introduction) - Role and permission management
- [Floating Vue](https://floating-vue.starpad.dev/) - Tooltip and popover library
- [VueUse](https://vueuse.org/) - Vue composition utilities
- [Hero Icons](https://heroicons.com/) - Beautiful hand-crafted SVG icons
- [HeadlessUI](https://headlessui.com/) - Unstyled, accessible UI components
# Build With
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>