#!/bin/bash
# Adds a CRON job to run cron.php every 5 minutes

# Get full path to cron.php
CRON_PATH="$(cd "$(dirname "$0")" && pwd)/cron.php"
CRON_JOB="*/5 * * * * php $CRON_PATH"

# Check if the CRON job already exists
(crontab -l 2>/dev/null | grep -F "$CRON_JOB") && echo "✅ CRON job already set." && exit 0

# Add the CRON job
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

echo "CRON job added: $CRON_JOB"

# USAGE INSTRUCTIONS:
# To enable automatic GitHub email updates every 5 minutes, run:
# 
#   chmod +x setup_cron.sh
#   ./setup_cron.sh
#
# This sets up a CRON job on a UNIX/Linux system.
