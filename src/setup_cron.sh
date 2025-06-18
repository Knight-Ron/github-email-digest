#!/bin/bash
# setup_cron.sh - Automatically sets a CRON job to run cron.php every 5 minutes

# Get absolute path to cron.php
CRON_PATH="$(cd "$(dirname "$0")" && pwd)/cron.php"
CRON_JOB="*/5 * * * * php $CRON_PATH"

# Check if the CRON job already exists
if crontab -l 2>/dev/null | grep -Fxq "$CRON_JOB"; then
    echo "✅ CRON job already set."
    exit 0
fi

# Add the CRON job
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
echo "✅ CRON job added: $CRON_JOB"

# Instructions
echo ""
echo "ℹ️  To remove the CRON job later, run: crontab -e"
