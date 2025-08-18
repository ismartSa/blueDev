#!/bin/bash

# Laravel-Brive Backup Restoration Script
# Restores database, files, and storage from backups

set -e

# Configuration
APP_NAME="laravel-brive"
APP_PATH="/var/www/laravel-brive"
BACKUP_BASE_DIR="/var/backups/laravel-brive"
DB_BACKUP_DIR="$BACKUP_BASE_DIR/database"
FILE_BACKUP_DIR="$BACKUP_BASE_DIR/files"
STORAGE_BACKUP_DIR="$BACKUP_BASE_DIR/storage"
LOG_FILE="/var/log/laravel-brive-restore.log"
TEMP_DIR="/tmp/laravel-brive-restore"

# Database configuration (read from .env)
if [ -f "$APP_PATH/.env" ]; then
    DB_HOST=$(grep DB_HOST $APP_PATH/.env | cut -d '=' -f2)
    DB_PORT=$(grep DB_PORT $APP_PATH/.env | cut -d '=' -f2)
    DB_DATABASE=$(grep DB_DATABASE $APP_PATH/.env | cut -d '=' -f2)
    DB_USERNAME=$(grep DB_USERNAME $APP_PATH/.env | cut -d '=' -f2)
    DB_PASSWORD=$(grep DB_PASSWORD $APP_PATH/.env | cut -d '=' -f2)
else
    echo "ERROR - .env file not found at $APP_PATH/.env"
    exit 1
fi

# Functions
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S'): $1" | tee -a $LOG_FILE
}

show_usage() {
    cat << EOF
Usage: $0 [OPTIONS] COMMAND

Commands:
  list-backups          List available backups
  restore-database      Restore database from backup
  restore-files         Restore application files from backup
  restore-storage       Restore storage from backup
  restore-all           Restore everything from latest backups
  
Options:
  -d, --database FILE   Specific database backup file
  -f, --files FILE      Specific files backup file
  -s, --storage FILE    Specific storage backup file
  -t, --timestamp DATE  Restore from specific timestamp (YYYYMMDD_HHMMSS)
  --dry-run            Show what would be restored without doing it
  --force              Skip confirmation prompts
  -h, --help           Show this help message

Examples:
  $0 list-backups
  $0 restore-database -d laravel-brive_db_20240118_120000.sql.gz
  $0 restore-all -t 20240118_120000
  $0 restore-all --dry-run
EOF
}

list_backups() {
    log_message "Listing available backups..."
    
    echo "\n=== DATABASE BACKUPS ==="
    if ls $DB_BACKUP_DIR/*.gz >/dev/null 2>&1; then
        ls -lah $DB_BACKUP_DIR/*.gz | awk '{print $9, $5, $6, $7, $8}' | column -t
    else
        echo "No database backups found"
    fi
    
    echo "\n=== FILE BACKUPS ==="
    if ls $FILE_BACKUP_DIR/*.tar.gz >/dev/null 2>&1; then
        ls -lah $FILE_BACKUP_DIR/*.tar.gz | awk '{print $9, $5, $6, $7, $8}' | column -t
    else
        echo "No file backups found"
    fi
    
    echo "\n=== STORAGE BACKUPS ==="
    if ls $STORAGE_BACKUP_DIR/*.tar.gz >/dev/null 2>&1; then
        ls -lah $STORAGE_BACKUP_DIR/*.tar.gz | awk '{print $9, $5, $6, $7, $8}' | column -t
    else
        echo "No storage backups found"
    fi
    echo
}

find_backup_by_timestamp() {
    local timestamp=$1
    local type=$2
    
    case $type in
        "database")
            find $DB_BACKUP_DIR -name "*_${timestamp}.sql.gz" | head -1
            ;;
        "files")
            find $FILE_BACKUP_DIR -name "*_${timestamp}.tar.gz" | head -1
            ;;
        "storage")
            find $STORAGE_BACKUP_DIR -name "*_${timestamp}.tar.gz" | head -1
            ;;
    esac
}

get_latest_backup() {
    local type=$1
    
    case $type in
        "database")
            ls -t $DB_BACKUP_DIR/*.gz 2>/dev/null | head -1
            ;;
        "files")
            ls -t $FILE_BACKUP_DIR/*.tar.gz 2>/dev/null | head -1
            ;;
        "storage")
            ls -t $STORAGE_BACKUP_DIR/*.tar.gz 2>/dev/null | head -1
            ;;
    esac
}

confirm_action() {
    local message=$1
    
    if [ "$FORCE" = true ]; then
        return 0
    fi
    
    echo -n "$message (y/N): "
    read -r response
    case $response in
        [yY][eE][sS]|[yY])
            return 0
            ;;
        *)
            return 1
            ;;
    esac
}

create_temp_dir() {
    mkdir -p $TEMP_DIR
    chmod 750 $TEMP_DIR
}

cleanup_temp_dir() {
    if [ -d "$TEMP_DIR" ]; then
        rm -rf $TEMP_DIR
        log_message "Cleaned up temporary directory"
    fi
}

restore_database() {
    local backup_file=$1
    
    if [ -z "$backup_file" ]; then
        backup_file=$(get_latest_backup "database")
    fi
    
    if [ -z "$backup_file" ] || [ ! -f "$backup_file" ]; then
        log_message "ERROR - Database backup file not found: $backup_file"
        return 1
    fi
    
    log_message "Restoring database from: $(basename $backup_file)"
    
    if [ "$DRY_RUN" = true ]; then
        log_message "DRY RUN - Would restore database from $backup_file"
        return 0
    fi
    
    if ! confirm_action "This will overwrite the current database. Continue?"; then
        log_message "Database restore cancelled by user"
        return 1
    fi
    
    # Create temporary directory
    create_temp_dir
    
    # Decompress backup
    local temp_sql="$TEMP_DIR/restore.sql"
    if gunzip -c "$backup_file" > "$temp_sql"; then
        log_message "Backup decompressed successfully"
    else
        log_message "ERROR - Failed to decompress backup"
        cleanup_temp_dir
        return 1
    fi
    
    # Create database backup before restore
    local pre_restore_backup="$TEMP_DIR/pre_restore_$(date +%Y%m%d_%H%M%S).sql"
    log_message "Creating pre-restore backup..."
    mysqldump -h$DB_HOST -P$DB_PORT -u$DB_USERNAME -p$DB_PASSWORD \
        --single-transaction $DB_DATABASE > "$pre_restore_backup" || true
    
    # Restore database
    log_message "Restoring database..."
    if mysql -h$DB_HOST -P$DB_PORT -u$DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < "$temp_sql"; then
        log_message "Database restored successfully"
        
        # Clear Laravel caches
        cd $APP_PATH
        php artisan config:clear
        php artisan cache:clear
        php artisan route:clear
        php artisan view:clear
        
        log_message "Laravel caches cleared"
        cleanup_temp_dir
        return 0
    else
        log_message "ERROR - Database restore failed"
        
        # Attempt to restore pre-restore backup
        if [ -f "$pre_restore_backup" ]; then
            log_message "Attempting to restore pre-restore backup..."
            mysql -h$DB_HOST -P$DB_PORT -u$DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < "$pre_restore_backup"
        fi
        
        cleanup_temp_dir
        return 1
    fi
}

restore_files() {
    local backup_file=$1
    
    if [ -z "$backup_file" ]; then
        backup_file=$(get_latest_backup "files")
    fi
    
    if [ -z "$backup_file" ] || [ ! -f "$backup_file" ]; then
        log_message "ERROR - Files backup file not found: $backup_file"
        return 1
    fi
    
    log_message "Restoring files from: $(basename $backup_file)"
    
    if [ "$DRY_RUN" = true ]; then
        log_message "DRY RUN - Would restore files from $backup_file"
        tar -tzf "$backup_file" | head -20
        return 0
    fi
    
    if ! confirm_action "This will overwrite application files. Continue?"; then
        log_message "Files restore cancelled by user"
        return 1
    fi
    
    # Create backup of current files
    local current_backup="$TEMP_DIR/current_files_$(date +%Y%m%d_%H%M%S).tar.gz"
    log_message "Creating backup of current files..."
    tar -czf "$current_backup" -C $(dirname $APP_PATH) $(basename $APP_PATH) || true
    
    # Restore files
    log_message "Restoring application files..."
    if tar -xzf "$backup_file" -C $(dirname $APP_PATH); then
        log_message "Files restored successfully"
        
        # Set proper permissions
        chown -R www-data:www-data $APP_PATH
        chmod -R 755 $APP_PATH
        chmod -R 775 $APP_PATH/storage
        chmod -R 775 $APP_PATH/bootstrap/cache
        
        log_message "File permissions updated"
        return 0
    else
        log_message "ERROR - Files restore failed"
        
        # Attempt to restore current backup
        if [ -f "$current_backup" ]; then
            log_message "Attempting to restore current files backup..."
            tar -xzf "$current_backup" -C $(dirname $APP_PATH)
        fi
        
        return 1
    fi
}

restore_storage() {
    local backup_file=$1
    
    if [ -z "$backup_file" ]; then
        backup_file=$(get_latest_backup "storage")
    fi
    
    if [ -z "$backup_file" ] || [ ! -f "$backup_file" ]; then
        log_message "ERROR - Storage backup file not found: $backup_file"
        return 1
    fi
    
    log_message "Restoring storage from: $(basename $backup_file)"
    
    if [ "$DRY_RUN" = true ]; then
        log_message "DRY RUN - Would restore storage from $backup_file"
        tar -tzf "$backup_file" | head -20
        return 0
    fi
    
    if ! confirm_action "This will overwrite storage files. Continue?"; then
        log_message "Storage restore cancelled by user"
        return 1
    fi
    
    # Create backup of current storage
    local current_storage="$TEMP_DIR/current_storage_$(date +%Y%m%d_%H%M%S).tar.gz"
    log_message "Creating backup of current storage..."
    tar -czf "$current_storage" -C $APP_PATH storage || true
    
    # Restore storage
    log_message "Restoring storage..."
    if tar -xzf "$backup_file" -C $APP_PATH; then
        log_message "Storage restored successfully"
        
        # Set proper permissions
        chown -R www-data:www-data $APP_PATH/storage
        chmod -R 775 $APP_PATH/storage
        
        log_message "Storage permissions updated"
        return 0
    else
        log_message "ERROR - Storage restore failed"
        
        # Attempt to restore current storage
        if [ -f "$current_storage" ]; then
            log_message "Attempting to restore current storage backup..."
            tar -xzf "$current_storage" -C $APP_PATH
        fi
        
        return 1
    fi
}

restore_all() {
    local timestamp=$1
    local db_file=$2
    local files_file=$3
    local storage_file=$4
    
    log_message "Starting full restore process..."
    
    # Find backup files
    if [ -n "$timestamp" ]; then
        db_file=$(find_backup_by_timestamp "$timestamp" "database")
        files_file=$(find_backup_by_timestamp "$timestamp" "files")
        storage_file=$(find_backup_by_timestamp "$timestamp" "storage")
    else
        db_file=${db_file:-$(get_latest_backup "database")}
        files_file=${files_file:-$(get_latest_backup "files")}
        storage_file=${storage_file:-$(get_latest_backup "storage")}
    fi
    
    log_message "Restore plan:"
    log_message "  Database: $(basename "${db_file:-Not found}")"
    log_message "  Files: $(basename "${files_file:-Not found}")"
    log_message "  Storage: $(basename "${storage_file:-Not found}")"
    
    if [ "$DRY_RUN" = true ]; then
        log_message "DRY RUN - Full restore plan shown above"
        return 0
    fi
    
    if ! confirm_action "This will restore the entire application. Continue?"; then
        log_message "Full restore cancelled by user"
        return 1
    fi
    
    create_temp_dir
    
    local success=true
    
    # Restore in order: files, storage, database
    if [ -n "$files_file" ]; then
        if ! restore_files "$files_file"; then
            success=false
        fi
    fi
    
    if [ -n "$storage_file" ]; then
        if ! restore_storage "$storage_file"; then
            success=false
        fi
    fi
    
    if [ -n "$db_file" ]; then
        if ! restore_database "$db_file"; then
            success=false
        fi
    fi
    
    cleanup_temp_dir
    
    if [ "$success" = true ]; then
        log_message "Full restore completed successfully"
        return 0
    else
        log_message "Full restore completed with errors"
        return 1
    fi
}

# Parse command line arguments
DRY_RUN=false
FORCE=false
TIMESTAMP=""
DB_FILE=""
FILES_FILE=""
STORAGE_FILE=""

while [[ $# -gt 0 ]]; do
    case $1 in
        -d|--database)
            DB_FILE="$2"
            shift 2
            ;;
        -f|--files)
            FILES_FILE="$2"
            shift 2
            ;;
        -s|--storage)
            STORAGE_FILE="$2"
            shift 2
            ;;
        -t|--timestamp)
            TIMESTAMP="$2"
            shift 2
            ;;
        --dry-run)
            DRY_RUN=true
            shift
            ;;
        --force)
            FORCE=true
            shift
            ;;
        -h|--help)
            show_usage
            exit 0
            ;;
        list-backups|restore-database|restore-files|restore-storage|restore-all)
            COMMAND="$1"
            shift
            ;;
        *)
            echo "Unknown option: $1"
            show_usage
            exit 1
            ;;
    esac
done

# Check if running as root or www-data
if [[ $EUID -ne 0 ]] && [[ $(whoami) != "www-data" ]]; then
    echo "ERROR - This script must be run as root or www-data user"
    exit 1
fi

# Execute command
case "${COMMAND:-}" in
    "list-backups")
        list_backups
        ;;
    "restore-database")
        restore_database "$DB_FILE"
        ;;
    "restore-files")
        restore_files "$FILES_FILE"
        ;;
    "restore-storage")
        restore_storage "$STORAGE_FILE"
        ;;
    "restore-all")
        restore_all "$TIMESTAMP" "$DB_FILE" "$FILES_FILE" "$STORAGE_FILE"
        ;;
    *)
        echo "No command specified"
        show_usage
        exit 1
        ;;
esac

exit 0