#!/bin/bash
# setup_cron.sh - Automatically sets a CRON job to run cron.php every 5 minutes

# Resolve absolute path to cron.php
CRON_PATH="$(cd "$(dirname "$0")" && pwd)/cron.php"
CRON_JOB="*/5 * * * * php $CRON_PATH"

# Check if cron.php is executable
if [ ! -f "$CRON_PATH" ]; then
    echo "❌ cron.php not found at: $CRON_PATH"
    exit 1
fi

# Check if the CRON job already exists
if crontab -l 2>/dev/null | grep -Fxq "$CRON_JOB"; then
    echo "✅ CRON job already exists and is active."
else
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "✅ CRON job added: $CRON_JOB"
fi

# Final note
echo ""
echo "ℹ️  This script sets up a background job to run every 5 minutes."
echo "🔧 To view or edit crontab manually, run: crontab -e"
