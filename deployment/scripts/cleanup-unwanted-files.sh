#!/bin/bash

# Cleanup Unwanted Files Script
# This script removes files that are not part of the core Laravel project

set -e

echo "================================"
echo "Starting cleanup of unwanted files"
echo "================================"

# Define the project root
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$PROJECT_ROOT"

echo "Working in: $PROJECT_ROOT"

# List of unwanted files to remove
UNWANTED_FILES=(
    "Untitled-1"
    "quizzz"
    "report-viewer.html"
    "unified-viewer.html"
)

# List of unwanted patterns to add to .gitignore
UNWANTED_PATTERNS=(
    "# Unwanted files"
    "Untitled-*"
    "quizzz*"
    "*-viewer.html"
    "*.tmp"
    "*.temp"
    "*.bak"
    "*.old"
    "test-*"
    "demo-*"
)

echo "Checking for unwanted files..."

# Remove unwanted files
for file in "${UNWANTED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "Removing file: $file"
        rm -f "$file"
        
        # Remove from git if tracked
        if git ls-files --error-unmatch "$file" > /dev/null 2>&1; then
            echo "Removing $file from git tracking"
            git rm --cached "$file" 2>/dev/null || true
        fi
    else
        echo "File not found: $file (already clean)"
    fi
done

echo "Updating .gitignore..."

# Check if .gitignore exists
if [ ! -f ".gitignore" ]; then
    echo "Creating .gitignore file"
    touch .gitignore
fi

# Add unwanted patterns to .gitignore if not already present
for pattern in "${UNWANTED_PATTERNS[@]}"; do
    if ! grep -Fxq "$pattern" .gitignore; then
        echo "Adding to .gitignore: $pattern"
        echo "$pattern" >> .gitignore
    fi
done

echo "Checking for large files in storage..."

# Clean up large log files (keep only recent ones)
if [ -d "storage/logs" ]; then
    find storage/logs -name "*.log" -size +10M -mtime +7 -exec rm -f {} \; 2>/dev/null || true
    echo "Cleaned up large log files older than 7 days"
fi

# Clean up cache files
if [ -d "storage/framework/cache" ]; then
    find storage/framework/cache -name "*" -type f -mtime +30 -exec rm -f {} \; 2>/dev/null || true
    echo "Cleaned up old cache files"
fi

# Clean up session files
if [ -d "storage/framework/sessions" ]; then
    find storage/framework/sessions -name "*" -type f -mtime +7 -exec rm -f {} \; 2>/dev/null || true
    echo "Cleaned up old session files"
fi

echo "Checking for files with suspicious extensions..."

# Find and list suspicious files (but don't auto-delete)
SUSPICIOUS_EXTENSIONS=("*.exe" "*.bat" "*.cmd" "*.scr" "*.com" "*.pif")
for ext in "${SUSPICIOUS_EXTENSIONS[@]}"; do
    if find . -name "$ext" -type f | grep -q .; then
        echo "WARNING: Found suspicious files with extension $ext:"
        find . -name "$ext" -type f
        echo "Please review these files manually"
    fi
done

echo "Generating cleanup report..."

# Generate a report of what was cleaned
REPORT_FILE="deployment/logs/cleanup-report-$(date +%Y%m%d-%H%M%S).txt"
mkdir -p "$(dirname "$REPORT_FILE")"

cat > "$REPORT_FILE" << EOF
Cleanup Report - $(date)
================================

Project: Laravel Brive
Cleaned by: cleanup-unwanted-files.sh

Files Removed:
EOF

for file in "${UNWANTED_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo "- $file" >> "$REPORT_FILE"
    fi
done

cat >> "$REPORT_FILE" << EOF

Patterns Added to .gitignore:
EOF

for pattern in "${UNWANTED_PATTERNS[@]}"; do
    echo "- $pattern" >> "$REPORT_FILE"
done

echo "Storage Cleanup:"
echo "- Large log files (>10MB, >7 days old): Removed"
echo "- Old cache files (>30 days): Removed"
echo "- Old session files (>7 days): Removed"

echo "Report saved to: $REPORT_FILE"

echo "================================"
echo "Cleanup completed successfully!"
echo "================================"

# Commit changes if in git repository
if git rev-parse --git-dir > /dev/null 2>&1; then
    echo "Committing cleanup changes..."
    git add .gitignore 2>/dev/null || true
    
    if git diff --staged --quiet; then
        echo "No changes to commit"
    else
        git commit -m "Clean up unwanted files and update gitignore" || echo "Commit failed or no changes"
    fi
fi

echo "Cleanup process finished!"