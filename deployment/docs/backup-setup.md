# Backup System Setup Guide

This guide covers the complete setup and configuration of the automated backup system for Laravel-Brive production deployment.

## Overview

The backup system provides:
- **Database backups**: MySQL dumps with compression
- **Application files backup**: Complete codebase excluding temporary files
- **Storage backup**: User uploads and application storage
- **Automated rotation**: Configurable retention policies
- **S3 integration**: Optional cloud storage
- **Monitoring**: Logging and notifications
- **Restoration**: Complete restore capabilities

## Prerequisites

- Ubuntu/Debian server with root access
- MySQL/PostgreSQL database
- Sufficient disk space for backups
- Optional: AWS CLI for S3 integration
- Optional: Mail server for notifications

## 1. Initial Setup

### Create Backup Directories

```bash
# Create backup directories
sudo mkdir -p /var/backups/laravel-brive/{database,files,storage}
sudo chown -R www-data:www-data /var/backups/laravel-brive
sudo chmod -R 750 /var/backups/laravel-brive

# Create log directory
sudo touch /var/log/laravel-brive-backup.log
sudo touch /var/log/laravel-brive-restore.log
sudo chown www-data:www-data /var/log/laravel-brive-*.log
sudo chmod 644 /var/log/laravel-brive-*.log
```

### Copy Backup Scripts

```bash
# Copy scripts to system location
sudo cp /path/to/laravel-brive/deployment/scripts/backup-system.sh /usr/local/bin/
sudo cp /path/to/laravel-brive/deployment/scripts/restore-backup.sh /usr/local/bin/

# Make scripts executable
sudo chmod +x /usr/local/bin/backup-system.sh
sudo chmod +x /usr/local/bin/restore-backup.sh

# Create symbolic links for easier access
sudo ln -sf /usr/local/bin/backup-system.sh /usr/local/bin/laravel-brive-backup
sudo ln -sf /usr/local/bin/restore-backup.sh /usr/local/bin/laravel-brive-restore
```

## 2. Configuration

### Environment Variables

Ensure your `.env` file contains the correct database configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_brive
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Backup Script Configuration

Edit `/usr/local/bin/backup-system.sh` to customize:

```bash
# Retention settings (days)
DB_RETENTION=30        # Keep database backups for 30 days
FILE_RETENTION=7       # Keep file backups for 7 days
STORAGE_RETENTION=14   # Keep storage backups for 14 days

# AWS S3 configuration (optional)
S3_BUCKET="laravel-brive-backups"
S3_REGION="us-east-1"
ENABLE_S3_UPLOAD=false  # Set to true to enable S3 uploads
```

## 3. AWS S3 Integration (Optional)

### Install AWS CLI

```bash
# Install AWS CLI
curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
unzip awscliv2.zip
sudo ./aws/install

# Configure AWS credentials
aws configure
# Enter your AWS Access Key ID, Secret Access Key, region, and output format
```

### Create S3 Bucket

```bash
# Create S3 bucket
aws s3 mb s3://laravel-brive-backups --region us-east-1

# Set bucket policy for lifecycle management
cat > lifecycle-policy.json << 'EOF'
{
    "Rules": [
        {
            "ID": "DatabaseBackupLifecycle",
            "Status": "Enabled",
            "Filter": {
                "Prefix": "database/"
            },
            "Transitions": [
                {
                    "Days": 30,
                    "StorageClass": "STANDARD_IA"
                },
                {
                    "Days": 90,
                    "StorageClass": "GLACIER"
                }
            ],
            "Expiration": {
                "Days": 365
            }
        },
        {
            "ID": "FilesBackupLifecycle",
            "Status": "Enabled",
            "Filter": {
                "Prefix": "files/"
            },
            "Transitions": [
                {
                    "Days": 7,
                    "StorageClass": "STANDARD_IA"
                },
                {
                    "Days": 30,
                    "StorageClass": "GLACIER"
                }
            ],
            "Expiration": {
                "Days": 90
            }
        }
    ]
}
EOF

# Apply lifecycle policy
aws s3api put-bucket-lifecycle-configuration \
    --bucket laravel-brive-backups \
    --lifecycle-configuration file://lifecycle-policy.json
```

### Enable S3 in Backup Script

```bash
# Edit backup script
sudo nano /usr/local/bin/backup-system.sh

# Change ENABLE_S3_UPLOAD to true
ENABLE_S3_UPLOAD=true
```

## 4. Automated Scheduling

### Setup Cron Jobs

```bash
# Edit crontab for www-data user
sudo crontab -u www-data -e

# Add the following cron jobs:

# Full backup daily at 2:00 AM
0 2 * * * /usr/local/bin/backup-system.sh >> /var/log/laravel-brive-backup.log 2>&1

# Database backup every 6 hours
0 */6 * * * /usr/local/bin/backup-system.sh database >> /var/log/laravel-brive-backup.log 2>&1

# Storage backup twice daily
0 6,18 * * * /usr/local/bin/backup-system.sh storage >> /var/log/laravel-brive-backup.log 2>&1

# Cleanup old backups weekly
0 3 * * 0 /usr/local/bin/backup-system.sh cleanup >> /var/log/laravel-brive-backup.log 2>&1

# Generate backup report daily
30 2 * * * /usr/local/bin/backup-system.sh report >> /var/log/laravel-brive-backup.log 2>&1
```

### Alternative: Systemd Timers

Create systemd service and timer files:

```bash
# Create service file
sudo tee /etc/systemd/system/laravel-brive-backup.service << 'EOF'
[Unit]
Description=Laravel-Brive Backup Service
After=network.target

[Service]
Type=oneshot
User=www-data
Group=www-data
ExecStart=/usr/local/bin/backup-system.sh
StandardOutput=append:/var/log/laravel-brive-backup.log
StandardError=append:/var/log/laravel-brive-backup.log
EOF

# Create timer file
sudo tee /etc/systemd/system/laravel-brive-backup.timer << 'EOF'
[Unit]
Description=Run Laravel-Brive backup daily
Requires=laravel-brive-backup.service

[Timer]
OnCalendar=daily
RandomizedDelaySec=1800
Persistent=true

[Install]
WantedBy=timers.target
EOF

# Enable and start timer
sudo systemctl daemon-reload
sudo systemctl enable laravel-brive-backup.timer
sudo systemctl start laravel-brive-backup.timer

# Check timer status
sudo systemctl status laravel-brive-backup.timer
sudo systemctl list-timers laravel-brive-backup.timer
```

## 5. Monitoring and Notifications

### Email Notifications

Install and configure mail server:

```bash
# Install postfix
sudo apt install postfix mailutils -y

# Configure postfix (select "Internet Site" during setup)
sudo dpkg-reconfigure postfix

# Test email
echo "Test email from Laravel-Brive backup system" | mail -s "Test" admin@laravel-brive.com
```

### Log Rotation

Create logrotate configuration:

```bash
sudo tee /etc/logrotate.d/laravel-brive-backup << 'EOF'
/var/log/laravel-brive-backup.log /var/log/laravel-brive-restore.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload rsyslog > /dev/null 2>&1 || true
    endscript
}
EOF
```

### Monitoring Script

Create monitoring script:

```bash
sudo tee /usr/local/bin/backup-monitor.sh << 'EOF'
#!/bin/bash

# Backup monitoring script
LOG_FILE="/var/log/laravel-brive-backup.log"
BACKUP_DIR="/var/backups/laravel-brive"
ALERT_EMAIL="admin@laravel-brive.com"

# Check if backup ran in last 25 hours
if ! find $BACKUP_DIR -name "*.gz" -o -name "*.tar.gz" -mtime -1 | grep -q .; then
    echo "ALERT: No recent backups found" | mail -s "Laravel-Brive Backup Alert" $ALERT_EMAIL
fi

# Check backup log for errors
if tail -100 $LOG_FILE | grep -q "ERROR"; then
    echo "ALERT: Backup errors detected" | mail -s "Laravel-Brive Backup Error" $ALERT_EMAIL
fi

# Check disk space
USED_SPACE=$(df $BACKUP_DIR | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $USED_SPACE -gt 85 ]; then
    echo "ALERT: Backup disk usage is ${USED_SPACE}%" | mail -s "Laravel-Brive Disk Space Alert" $ALERT_EMAIL
fi
EOF

sudo chmod +x /usr/local/bin/backup-monitor.sh

# Add to crontab
(sudo crontab -l 2>/dev/null; echo "0 8 * * * /usr/local/bin/backup-monitor.sh") | sudo crontab -
```

## 6. Usage Examples

### Manual Backup Operations

```bash
# Run full backup
sudo -u www-data /usr/local/bin/backup-system.sh

# Backup only database
sudo -u www-data /usr/local/bin/backup-system.sh database

# Backup only files
sudo -u www-data /usr/local/bin/backup-system.sh files

# Backup only storage
sudo -u www-data /usr/local/bin/backup-system.sh storage

# Cleanup old backups
sudo -u www-data /usr/local/bin/backup-system.sh cleanup

# Generate backup report
sudo -u www-data /usr/local/bin/backup-system.sh report
```

### Restoration Operations

```bash
# List available backups
sudo -u www-data /usr/local/bin/restore-backup.sh list-backups

# Restore from latest backups (dry run)
sudo -u www-data /usr/local/bin/restore-backup.sh restore-all --dry-run

# Restore everything from latest backups
sudo -u www-data /usr/local/bin/restore-backup.sh restore-all --force

# Restore from specific timestamp
sudo -u www-data /usr/local/bin/restore-backup.sh restore-all -t 20240118_120000

# Restore only database
sudo -u www-data /usr/local/bin/restore-backup.sh restore-database

# Restore specific database backup
sudo -u www-data /usr/local/bin/restore-backup.sh restore-database -d laravel-brive_db_20240118_120000.sql.gz
```

## 7. Testing and Validation

### Test Backup System

```bash
# Test database backup
sudo -u www-data /usr/local/bin/backup-system.sh database

# Verify backup was created
ls -la /var/backups/laravel-brive/database/

# Test backup integrity
gunzip -t /var/backups/laravel-brive/database/laravel-brive_db_*.sql.gz
```

### Test Restoration

```bash
# Create test database
mysql -u root -p -e "CREATE DATABASE laravel_brive_test;"

# Test restore to test database
# (Modify restore script temporarily to use test database)

# Verify restored data
mysql -u root -p laravel_brive_test -e "SHOW TABLES;"

# Cleanup test database
mysql -u root -p -e "DROP DATABASE laravel_brive_test;"
```

## 8. Troubleshooting

### Common Issues

1. **Permission Denied**:
   ```bash
   # Fix backup directory permissions
   sudo chown -R www-data:www-data /var/backups/laravel-brive
   sudo chmod -R 750 /var/backups/laravel-brive
   ```

2. **MySQL Access Denied**:
   ```bash
   # Verify database credentials in .env
   mysql -h$DB_HOST -P$DB_PORT -u$DB_USERNAME -p$DB_PASSWORD $DB_DATABASE -e "SELECT 1;"
   ```

3. **Disk Space Issues**:
   ```bash
   # Check disk usage
   df -h /var/backups/laravel-brive
   
   # Clean old backups manually
   find /var/backups/laravel-brive -name "*.gz" -mtime +30 -delete
   ```

4. **S3 Upload Failures**:
   ```bash
   # Test AWS credentials
   aws s3 ls s3://laravel-brive-backups
   
   # Check AWS CLI configuration
   aws configure list
   ```

### Log Analysis

```bash
# View recent backup logs
tail -f /var/log/laravel-brive-backup.log

# Search for errors
grep -i error /var/log/laravel-brive-backup.log

# View backup statistics
grep -i "backup size" /var/log/laravel-brive-backup.log | tail -10
```

## 9. Security Considerations

### File Permissions

```bash
# Secure backup directories
sudo chmod 750 /var/backups/laravel-brive
sudo chmod 640 /var/backups/laravel-brive/*/*.gz
sudo chmod 640 /var/backups/laravel-brive/*/*.tar.gz
```

### Database Security

- Use dedicated backup user with minimal privileges
- Store database credentials securely
- Encrypt backups if storing sensitive data

### Network Security

- Use VPN or private networks for backup transfers
- Enable S3 bucket encryption
- Implement access logging

## 10. Maintenance

### Regular Tasks

- [ ] Monitor backup logs weekly
- [ ] Test restoration monthly
- [ ] Review retention policies quarterly
- [ ] Update backup scripts as needed
- [ ] Verify S3 lifecycle policies
- [ ] Check disk space regularly
- [ ] Test notification system

### Performance Optimization

```bash
# Optimize MySQL for backups
# Add to /etc/mysql/mysql.conf.d/backup.cnf
[mysqldump]
single-transaction
routines
triggers
events
lock-tables=false
```

## Backup System Summary

Your Laravel-Brive backup system is now configured with:

✅ **Automated daily backups**
✅ **Database, files, and storage backup**
✅ **Configurable retention policies**
✅ **S3 cloud storage integration**
✅ **Complete restoration capabilities**
✅ **Monitoring and alerting**
✅ **Comprehensive logging**
✅ **Security best practices**

The system will automatically:
- Create daily backups at 2:00 AM
- Rotate old backups based on retention policies
- Upload to S3 (if configured)
- Send notifications on failures
- Generate daily backup reports

Your data is now protected with enterprise-grade backup and recovery capabilities!