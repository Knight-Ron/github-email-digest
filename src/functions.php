<?php

// Generates a 6-digit numeric verification code.
function generateVerificationCode(): string {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Sends an HTML verification email with the code and unsubscribe link.
function sendVerificationEmail(string $email, string $code): bool {
    $subject = "Your Verification Code";
    $message = "<p>Your verification code is: <strong>$code</strong></p>";
    $message .= "<p>If you did not request this, please ignore.</p>";
    $message .= "<p>To unsubscribe, please visit the <a href=\"http://localhost/github-email-digest/src/unsubscribe.php\">unsubscribe page</a> and follow the steps.</p>";
    $headers = "From: ronaldjwork@gmail.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($email, $subject, $message, $headers);
}

// Registers an email if not already in the file.
function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file_exists($file)
        ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
        : [];

    if (!in_array($email, $emails)) {
        return file_put_contents($file, $email . PHP_EOL, FILE_APPEND) !== false;
    }

    return false;
}

// Removes an email from the subscription list.
function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';

    if (!file_exists($file)) {
        return false;
    }

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $emails = array_filter($emails, fn($line) => trim($line) !== $email);

    return file_put_contents($file, implode(PHP_EOL, $emails) . PHP_EOL) !== false;
}

// Sends a code to confirm unsubscription.
function sendUnsubscribeCode($email, $code) {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

    if (!in_array($email, $emails)) {
        return false;
    }

    $subject = "Confirm Unsubscription";
    $message = "<p>To confirm unsubscription, use this code: <strong>$code</strong></p>";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@example.com\r\n";

    return mail($email, $subject, $message, $headers); 
}

// Fetches recent public GitHub events using the Events API.
function fetchGitHubTimeline(): array {
    $url = 'https://api.github.com/events';
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: PHP\r\nAccept: application/vnd.github.v3+json\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $data = file_get_contents($url, false, $context);
    return $data ? json_decode($data, true) : [];
}

// Converts GitHub API data into an HTML-formatted email block.
function formatGitHubData(array $data): string {
    if (empty($data)) {
        return "<p style='font-family: Arial, sans-serif; color: #555;'>No recent GitHub activity found.</p>";
    }

    $html = "
    <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
        <h2 style='color: #0366d6;'>GitHub Timeline Updates</h2>
        <table style='width: 100%; border-collapse: collapse;'>
            <thead>
                <tr style='background-color: #f6f8fa;'>
                    <th style='padding: 10px; border: 1px solid #ddd;'>Event</th>
                    <th style='padding: 10px; border: 1px solid #ddd;'>User</th>
                    <th style='padding: 10px; border: 1px solid #ddd;'>Repo</th>
                    <th style='padding: 10px; border: 1px solid #ddd;'>When</th>
                </tr>
            </thead>
            <tbody>
    ";

    foreach ($data as $event) {
        $type = htmlspecialchars($event['type'] ?? 'Unknown');
        $user = htmlspecialchars($event['actor']['login'] ?? 'Anonymous');
        $repo = htmlspecialchars($event['repo']['name'] ?? 'Unknown');
        $createdAt = htmlspecialchars(date('Y-m-d H:i:s', strtotime($event['created_at'] ?? '')));

        $html .= "
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd;'>$type</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>$user</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>$repo</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>$createdAt</td>
            </tr>
        ";
    }

    $html .= "</tbody></table></div>";
    return $html;
}

// Sends formatted GitHub update emails to all registered subscribers.
function sendGitHubUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';

    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = fetchGitHubTimeline();
    $formatted = formatGitHubData($data);

    foreach ($emails as $email) {
        $unsubscribeLink = "http://localhost/github-email-digest/src/unsubscribe.php";
        $body = $formatted . "<p>To unsubscribe, please visit the <a href=\"$unsubscribeLink\">unsubscribe page</a>.</p>";

        $subject = "Latest GitHub Updates";
        $headers = "From: no-reply@example.com\r\n";
        $headers .= "Content-Type: text/html\r\n";

        mail($email, $subject, $body, $headers);
    }
}
