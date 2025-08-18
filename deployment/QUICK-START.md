# 🚀 Quick Start Deployment Guide

## One-Command Deployment

### For New Servers (First Time)
```bash
# Complete setup + deployment
sudo ./deployment/deploy-master.sh full production
```

### For Existing Servers
```bash
# Deploy application only
./deployment/deploy-master.sh deploy production
```

### For Server Setup Only
```bash
# Setup server environment
sudo ./deployment/deploy-master.sh setup
```

## 📋 Before You Start

1. **Server Requirements**: Ubuntu/Debian with root access
2. **Domain**: Point your domain to server IP
3. **GitHub Secrets**: Configure in repository settings
   - `SSH_KEY`, `HOST`, `USERNAME`, `PORT`, `APP_URL`

## 🔧 GitHub Secrets Setup

1. Go to: `https://github.com/YOUR_USERNAME/YOUR_REPO/settings/secrets/actions`
2. Add these secrets:
   ```
   SSH_KEY: [Your private SSH key]
   HOST: [Server IP address]
   USERNAME: [Server username]
   PORT: 22
   APP_URL: https://your-domain.com
   ```

## 📁 What's in This Folder

- **`deploy-master.sh`** - Main deployment orchestrator
- **`scripts/`** - Individual deployment scripts
- **`configs/`** - Server configuration files
- **`templates/`** - Environment file templates
- **`docs/`** - Detailed documentation

## 🆘 Quick Troubleshooting

### 502 Bad Gateway
```bash
sudo systemctl restart php8.3-fpm nginx
```

### Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/siteeblue
sudo chmod -R 755 /var/www/siteeblue
```

### Check Services
```bash
./deployment/deploy-master.sh health
```

## 📞 Need Help?

Check the detailed guides:
- `CHECKLIST.md` - Step-by-step deployment checklist
- `docs/DEPLOYMENT.md` - Comprehensive deployment guide
- `README.md` - Complete folder overview

---

**🎉 Happy Deploying!**