<?php
/**
 * GitHub Timeline Email Digest - Cron Entry Point
 *
 * This script is meant to be scheduled (e.g., via cron) to send daily GitHub activity summaries
 * to all verified subscribers.
 */

require_once __DIR__ . '/functions.php';

try {
    sendGitHubUpdatesToSubscribers();
    echo "[" . date('Y-m-d H:i:s') . "] GitHub updates sent successfully.\n";
} catch (Exception $e) {
    // Basic error logging to stderr
    error_log("[" . date('Y-m-d H:i:s') . "] Error sending GitHub updates: " . $e->getMessage());
}
