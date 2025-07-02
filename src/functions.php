<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// Load env vars
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[$name] = trim($value);
        }
    }
}

// Constants
define('EMAILS_FILE', __DIR__ . '/registered_emails.txt');
define('BASE_URL', 'http://localhost/github-email-digest'); // Change when deployed

// Generate 6-digit verification code
function generateVerificationCode(): string {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Send email using PHPMailer with environment credentials
function sendEmail(string $to, string $subject, string $body, string $fromName = "GitHub Digest"): bool {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = (int) $_ENV['SMTP_PORT'];

        $mail->setFrom($_ENV['SMTP_FROM'], $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        return false;
    }
}

function sendVerificationEmail(string $email, string $code): bool {
    $subject = "Your Verification Code";
    $message = "<p>Your verification code is: <strong>$code</strong></p>
                <p>If you did not request this, you can ignore it.</p>
                <p>To unsubscribe later, visit <a href=\"" . BASE_URL . "/src/unsubscribe.php\">Unsubscribe</a>.</p>";

    return sendEmail($email, $subject, $message);
}

function sendUnsubscribeCode(string $email, string $code): bool {
    $subject = "Confirm Unsubscription";
    $message = "<p>To confirm unsubscription, use this code: <strong>$code</strong></p>";

    return sendEmail($email, $subject, $message);
}

function registerEmail(string $email): bool {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

    $emails = file_exists(EMAILS_FILE)
        ? file(EMAILS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
        : [];

    if (!in_array($email, $emails)) {
        return file_put_contents(EMAILS_FILE, $email . PHP_EOL, FILE_APPEND) !== false;
    }

    return false;
}

function unsubscribeEmail(string $email): bool {
    if (!file_exists(EMAILS_FILE)) return false;

    $emails = file(EMAILS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $filtered = array_filter($emails, fn($line) => trim($line) !== $email);

    return file_put_contents(EMAILS_FILE, implode(PHP_EOL, $filtered) . PHP_EOL) !== false;
}

function fetchGitHubTimeline(): array {
    $url = 'https://api.github.com/events';
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: PHP\r\nAccept: application/vnd.github.v3+json\r\n"
        ]
    ];

    $context = stream_context_create($opts);
    $data = @file_get_contents($url, false, $context);

    if ($data === false) {
        error_log("GitHub API fetch failed");
        return [];
    }

    return json_decode($data, true);
}

function formatGitHubData(array $data): string {
    if (empty($data)) {
        return "<p style='font-family: Arial, sans-serif; color: #555;'>No recent GitHub activity found.</p>";
    }

    $html = "<div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                <h2 style='color: #0366d6;'>GitHub Timeline Updates</h2>
                <table style='width: 100%; border-collapse: collapse;'>
                    <thead>
                        <tr style='background-color: #f6f8fa;'>
                            <th style='padding: 10px; border: 1px solid #ddd;'>Event</th>
                            <th style='padding: 10px; border: 1px solid #ddd;'>User</th>
                            <th style='padding: 10px; border: 1px solid #ddd;'>Repo</th>
                            <th style='padding: 10px; border: 1px solid #ddd;'>When</th>
                        </tr>
                    </thead><tbody>";

    foreach ($data as $event) {
        $type = htmlspecialchars($event['type'] ?? 'Unknown');
        $user = htmlspecialchars($event['actor']['login'] ?? 'Anonymous');
        $repo = htmlspecialchars($event['repo']['name'] ?? 'Unknown');
        $createdAt = htmlspecialchars(date('Y-m-d H:i:s', strtotime($event['created_at'] ?? '')));

        $html .= "<tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'>$type</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>$user</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>$repo</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>$createdAt</td>
                  </tr>";
    }

    $html .= "</tbody></table></div>";
    return $html;
}

function sendGitHubUpdatesToSubscribers(): void {
    if (!file_exists(EMAILS_FILE)) return;

    $emails = file(EMAILS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = fetchGitHubTimeline();
    $formatted = formatGitHubData($data);

    foreach ($emails as $email) {
        $unsubscribeLink = BASE_URL . "/src/unsubscribe.php";
        $body = $formatted . "<p>To unsubscribe, <a href=\"$unsubscribeLink\">click here</a>.</p>";
        $subject = "Latest GitHub Updates";

        sendEmail($email, $subject, $body);
    }
}
