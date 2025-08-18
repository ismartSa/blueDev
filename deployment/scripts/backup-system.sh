#!/bin/bash

# Laravel-Brive Automated Backup System
# Handles database, files, and storage backups with rotation and compression

set -e

# Configuration
APP_NAME="laravel-brive"
APP_PATH="/var/www/laravel-brive"
BACKUP_BASE_DIR="/var/backups/laravel-brive"
DB_BACKUP_DIR="$BACKUP_BASE_DIR/database"
FILE_BACKUP_DIR="$BACKUP_BASE_DIR/files"
STORAGE_BACKUP_DIR="$BACKUP_BASE_DIR/storage"
LOG_FILE="/var/log/laravel-brive-backup.log"

# Retention settings (days)
DB_RETENTION=30
FILE_RETENTION=7
STORAGE_RETENTION=14

# Database configuration (read from .env)
if [ -f "$APP_PATH/.env" ]; then
    DB_HOST=$(grep DB_HOST $APP_PATH/.env | cut -d '=' -f2)
    DB_PORT=$(grep DB_PORT $APP_PATH/.env | cut -d '=' -f2)
    DB_DATABASE=$(grep DB_DATABASE $APP_PATH/.env | cut -d '=' -f2)
    DB_USERNAME=$(grep DB_USERNAME $APP_PATH/.env | cut -d '=' -f2)
    DB_PASSWORD=$(grep DB_PASSWORD $APP_PATH/.env | cut -d '=' -f2)
else
    echo "$(date): ERROR - .env file not found at $APP_PATH/.env" >> $LOG_FILE
    exit 1
fi

# AWS S3 configuration (optional)
S3_BUCKET="laravel-brive-backups"
S3_REGION="us-east-1"
ENABLE_S3_UPLOAD=false

# Functions
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S'): $1" >> $LOG_FILE
    echo "$(date '+%Y-%m-%d %H:%M:%S'): $1"
}

create_directories() {
    log_message "Creating backup directories..."
    mkdir -p $DB_BACKUP_DIR $FILE_BACKUP_DIR $STORAGE_BACKUP_DIR
    chmod 750 $BACKUP_BASE_DIR
    chown -R www-data:www-data $BACKUP_BASE_DIR
}

backup_database() {
    log_message "Starting database backup..."
    
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    local backup_file="$DB_BACKUP_DIR/${APP_NAME}_db_${timestamp}.sql"
    local compressed_file="${backup_file}.gz"
    
    # Create database dump
    if mysqldump -h$DB_HOST -P$DB_PORT -u$DB_USERNAME -p$DB_PASSWORD \
        --single-transaction --routines --triggers --events \
        $DB_DATABASE > $backup_file; then
        
        # Compress the backup
        gzip $backup_file
        
        # Set permissions
        chmod 640 $compressed_file
        chown www-data:www-data $compressed_file
        
        log_message "Database backup completed: $compressed_file"
        
        # Get file size
        local size=$(du -h $compressed_file | cut -f1)
        log_message "Database backup size: $size"
        
        return 0
    else
        log_message "ERROR - Database backup failed"
        return 1
    fi
}

backup_application_files() {
    log_message "Starting application files backup..."
    
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    local backup_file="$FILE_BACKUP_DIR/${APP_NAME}_files_${timestamp}.tar.gz"
    
    # Create compressed archive of application files (excluding storage and vendor)
    if tar -czf $backup_file \
        --exclude='storage/logs/*' \
        --exclude='storage/framework/cache/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        --exclude='vendor' \
        --exclude='node_modules' \
        --exclude='.git' \
        -C $(dirname $APP_PATH) $(basename $APP_PATH); then
        
        # Set permissions
        chmod 640 $backup_file
        chown www-data:www-data $backup_file
        
        log_message "Application files backup completed: $backup_file"
        
        # Get file size
        local size=$(du -h $backup_file | cut -f1)
        log_message "Application files backup size: $size"
        
        return 0
    else
        log_message "ERROR - Application files backup failed"
        return 1
    fi
}

backup_storage() {
    log_message "Starting storage backup..."
    
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    local backup_file="$STORAGE_BACKUP_DIR/${APP_NAME}_storage_${timestamp}.tar.gz"
    
    # Create compressed archive of storage directory
    if tar -czf $backup_file \
        --exclude='storage/logs/*' \
        --exclude='storage/framework/cache/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        -C $APP_PATH storage; then
        
        # Set permissions
        chmod 640 $backup_file
        chown www-data:www-data $backup_file
        
        log_message "Storage backup completed: $backup_file"
        
        # Get file size
        local size=$(du -h $backup_file | cut -f1)
        log_message "Storage backup size: $size"
        
        return 0
    else
        log_message "ERROR - Storage backup failed"
        return 1
    fi
}

upload_to_s3() {
    if [ "$ENABLE_S3_UPLOAD" = true ]; then
        log_message "Uploading backups to S3..."
        
        # Check if AWS CLI is installed
        if ! command -v aws &> /dev/null; then
            log_message "WARNING - AWS CLI not installed, skipping S3 upload"
            return 1
        fi
        
        # Upload latest backups to S3
        local latest_db=$(ls -t $DB_BACKUP_DIR/*.gz 2>/dev/null | head -1)
        local latest_files=$(ls -t $FILE_BACKUP_DIR/*.tar.gz 2>/dev/null | head -1)
        local latest_storage=$(ls -t $STORAGE_BACKUP_DIR/*.tar.gz 2>/dev/null | head -1)
        
        if [ -n "$latest_db" ]; then
            aws s3 cp "$latest_db" "s3://$S3_BUCKET/database/" --region $S3_REGION
            log_message "Database backup uploaded to S3"
        fi
        
        if [ -n "$latest_files" ]; then
            aws s3 cp "$latest_files" "s3://$S3_BUCKET/files/" --region $S3_REGION
            log_message "Files backup uploaded to S3"
        fi
        
        if [ -n "$latest_storage" ]; then
            aws s3 cp "$latest_storage" "s3://$S3_BUCKET/storage/" --region $S3_REGION
            log_message "Storage backup uploaded to S3"
        fi
    fi
}

cleanup_old_backups() {
    log_message "Cleaning up old backups..."
    
    # Clean database backups
    find $DB_BACKUP_DIR -name "*.gz" -mtime +$DB_RETENTION -delete
    local db_deleted=$(find $DB_BACKUP_DIR -name "*.gz" -mtime +$DB_RETENTION 2>/dev/null | wc -l)
    log_message "Deleted $db_deleted old database backups"
    
    # Clean file backups
    find $FILE_BACKUP_DIR -name "*.tar.gz" -mtime +$FILE_RETENTION -delete
    local files_deleted=$(find $FILE_BACKUP_DIR -name "*.tar.gz" -mtime +$FILE_RETENTION 2>/dev/null | wc -l)
    log_message "Deleted $files_deleted old file backups"
    
    # Clean storage backups
    find $STORAGE_BACKUP_DIR -name "*.tar.gz" -mtime +$STORAGE_RETENTION -delete
    local storage_deleted=$(find $STORAGE_BACKUP_DIR -name "*.tar.gz" -mtime +$STORAGE_RETENTION 2>/dev/null | wc -l)
    log_message "Deleted $storage_deleted old storage backups"
}

generate_backup_report() {
    log_message "Generating backup report..."
    
    local report_file="$BACKUP_BASE_DIR/backup_report_$(date +%Y%m%d).txt"
    
    cat > $report_file << EOF
Laravel-Brive Backup Report
Generated: $(date)

=== BACKUP SUMMARY ===
Database Backups: $(ls -1 $DB_BACKUP_DIR/*.gz 2>/dev/null | wc -l) files
File Backups: $(ls -1 $FILE_BACKUP_DIR/*.tar.gz 2>/dev/null | wc -l) files
Storage Backups: $(ls -1 $STORAGE_BACKUP_DIR/*.tar.gz 2>/dev/null | wc -l) files

=== DISK USAGE ===
Total Backup Size: $(du -sh $BACKUP_BASE_DIR | cut -f1)
Database Backups: $(du -sh $DB_BACKUP_DIR | cut -f1)
File Backups: $(du -sh $FILE_BACKUP_DIR | cut -f1)
Storage Backups: $(du -sh $STORAGE_BACKUP_DIR | cut -f1)

=== LATEST BACKUPS ===
Latest Database: $(ls -t $DB_BACKUP_DIR/*.gz 2>/dev/null | head -1 | xargs basename 2>/dev/null || echo "None")
Latest Files: $(ls -t $FILE_BACKUP_DIR/*.tar.gz 2>/dev/null | head -1 | xargs basename 2>/dev/null || echo "None")
Latest Storage: $(ls -t $STORAGE_BACKUP_DIR/*.tar.gz 2>/dev/null | head -1 | xargs basename 2>/dev/null || echo "None")

=== RETENTION POLICY ===
Database: $DB_RETENTION days
Files: $FILE_RETENTION days
Storage: $STORAGE_RETENTION days
EOF

    chmod 644 $report_file
    log_message "Backup report generated: $report_file"
}

check_disk_space() {
    log_message "Checking disk space..."
    
    local available_space=$(df $BACKUP_BASE_DIR | awk 'NR==2 {print $4}')
    local available_gb=$((available_space / 1024 / 1024))
    
    if [ $available_gb -lt 5 ]; then
        log_message "WARNING - Low disk space: ${available_gb}GB available"
        return 1
    else
        log_message "Disk space OK: ${available_gb}GB available"
        return 0
    fi
}

send_notification() {
    local status=$1
    local message=$2
    
    # Email notification (requires mail command)
    if command -v mail &> /dev/null; then
        echo "$message" | mail -s "Laravel-Brive Backup $status" admin@laravel-brive.com
    fi
    
    # Slack notification (webhook URL required)
    # if [ -n "$SLACK_WEBHOOK_URL" ]; then
    #     curl -X POST -H 'Content-type: application/json' \
    #         --data "{\"text\":\"Laravel-Brive Backup $status: $message\"}" \
    #         $SLACK_WEBHOOK_URL
    # fi
}

# Main execution
main() {
    log_message "=== Starting Laravel-Brive backup process ==="
    
    # Check if running as root or www-data
    if [[ $EUID -ne 0 ]] && [[ $(whoami) != "www-data" ]]; then
        log_message "ERROR - This script must be run as root or www-data user"
        exit 1
    fi
    
    # Check disk space
    if ! check_disk_space; then
        send_notification "FAILED" "Backup failed due to low disk space"
        exit 1
    fi
    
    # Create directories
    create_directories
    
    # Perform backups
    local backup_success=true
    
    if ! backup_database; then
        backup_success=false
    fi
    
    if ! backup_application_files; then
        backup_success=false
    fi
    
    if ! backup_storage; then
        backup_success=false
    fi
    
    # Upload to S3 if enabled
    upload_to_s3
    
    # Cleanup old backups
    cleanup_old_backups
    
    # Generate report
    generate_backup_report
    
    if [ "$backup_success" = true ]; then
        log_message "=== Backup process completed successfully ==="
        send_notification "SUCCESS" "All backups completed successfully"
    else
        log_message "=== Backup process completed with errors ==="
        send_notification "PARTIAL" "Some backups failed - check logs"
        exit 1
    fi
}

# Handle command line arguments
case "${1:-}" in
    "database")
        log_message "Running database backup only..."
        create_directories
        backup_database
        ;;
    "files")
        log_message "Running files backup only..."
        create_directories
        backup_application_files
        ;;
    "storage")
        log_message "Running storage backup only..."
        create_directories
        backup_storage
        ;;
    "cleanup")
        log_message "Running cleanup only..."
        cleanup_old_backups
        ;;
    "report")
        generate_backup_report
        ;;
    *)
        main
        ;;
esac

exit 0