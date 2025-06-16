// NOTE: This file is intended to be run periodically (e.g., via cron)
// to send GitHub timeline updates to all verified subscribers.

// This script sends the latest GitHub activity email to all verified subscribers.
// Should be triggered by a scheduled cron job or manual execution.

<?php
require_once 'functions.php';
sendGitHubUpdatesToSubscribers();
