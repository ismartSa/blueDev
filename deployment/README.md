# Laravel Brive Deployment Guide

This folder contains all deployment-related files and scripts for the Laravel Brive application.

## 📁 Folder Structure

```
deployment/
├── scripts/           # Deployment and setup scripts
│   ├── deploy.sh      # Main deployment script
│   ├── server-setup.sh # Server initialization script
│   └── configure-env.sh # Environment configuration
├── configs/           # Configuration files
│   └── nginx.conf     # Nginx server configuration
├── docs/             # Documentation
│   └── DEPLOYMENT.md  # Detailed deployment guide
├── templates/        # Template files
└── README.md         # This file
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