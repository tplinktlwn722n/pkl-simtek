#!/bin/bash

# Database Backup Script for Admin Web
# This script creates a backup of the MySQL database

# Configuration
DB_HOST="localhost"
DB_PORT="3306"
DB_NAME="myapp_db"
DB_USER="root"
DB_PASSWORD=""

# Backup directory
BACKUP_DIR="./storage/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/backup_${DB_NAME}_${TIMESTAMP}.sql"

# Create backup directory if it doesn't exist
mkdir -p ${BACKUP_DIR}

# Create backup
echo "Starting database backup..."
mysqldump -h ${DB_HOST} -P ${DB_PORT} -u ${DB_USER} ${DB_NAME} > ${BACKUP_FILE}

if [ $? -eq 0 ]; then
    echo "Backup completed successfully!"
    echo "Backup file: ${BACKUP_FILE}"
    
    # Compress backup
    gzip ${BACKUP_FILE}
    echo "Backup compressed: ${BACKUP_FILE}.gz"
    
    # Remove backups older than 30 days
    find ${BACKUP_DIR} -name "backup_*.sql.gz" -type f -mtime +30 -delete
    echo "Old backups removed (older than 30 days)"
else
    echo "Backup failed!"
    exit 1
fi

echo "Backup process completed."
