<?php
// NOTE: This script is meant to be run periodically (e.g., via cron)
// It sends the latest GitHub timeline updates to all verified subscribers.

require_once __DIR__ . '/functions.php';

sendGitHubUpdatesToSubscribers();
