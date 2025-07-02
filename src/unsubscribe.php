<?php
session_start(); // Initialize session to store unsubscription code and email

require_once 'functions.php'; // Load utility functions for email handling

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Step 1: User submits their email to request unsubscription
    if (isset($_POST['unsubscribe_email'])) {
        $email = trim($_POST['unsubscribe_email']);

        // Check if email format is valid
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $file = __DIR__ . '/registered_emails.txt';
            $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

            // Check if the email is already registered
            if (!in_array($email, $emails)) {
                $message = "This email is not subscribed.";
            } else {
                // Generate and send unsubscription code
                $code = generateVerificationCode();
                $_SESSION['unsubscribe_code'] = $code;
                $_SESSION['unsubscribe_email'] = $email;

                if (sendUnsubscribeCode($email, $code)) {
                    $message = "A confirmation code was sent to $email.";
                } else {
                    $message = "Failed to send confirmation code. Please try again.";
                }
            }
        } else {
            $message = "Invalid email format.";
        }
    }

    // Step 2: User submits the unsubscription verification code
    if (isset($_POST['unsubscribe_verification_code'])) {
        $code = trim($_POST['unsubscribe_verification_code']);
        $email = $_SESSION['unsubscribe_email'] ?? '';

        // Verify code match
        if ($code === ($_SESSION['unsubscribe_code'] ?? '')) {
            // Attempt to remove the email from subscribers list
            if (unsubscribeEmail($email)) {
                $message = "You have been unsubscribed.";
            } else {
                $message = "Email not found or already unsubscribed.";
            }
            // Clear session data after use
            unset($_SESSION['unsubscribe_code'], $_SESSION['unsubscribe_email']);
        } else {
            $message = "Incorrect unsubscription code.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unsubscribe | GitHub Email Digest</title>
    <style>
        :root {
            --bg-color: #f0f4f8;
            --text-color: #333;
            --card-bg: white;
            --input-bg: white;
            --btn-bg: rgb(214, 3, 3);
            --btn-hover: rgba(156, 2, 2, 0.56);
            --alert-success-bg: #e6f4ea;
            --alert-error-bg: #fdecea;
            --alert-success-color: #256029;
            --alert-error-color: #d93025;
        }

        body.dark {
            --bg-color: #121212;
            --text-color: #eee;
            --card-bg: #1e1e1e;
            --input-bg: #2c2c2c;
            --btn-bg: rgb(255, 10, 10);
            --btn-hover: rgba(156, 2, 2, 0.56);
            --alert-success-bg: #1f3b2e;
            --alert-error-bg: #3e1c1a;
            --alert-success-color: #b9fbc0;
            --alert-error-color: #ffb4ab;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: var(--btn-bg);
            margin-bottom: 10px;
        }

        input, button {
            padding: 10px;
            width: 100%;
            font-size: 1rem;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            background: var(--input-bg);
            color: var(--text-color);
        }

        button {
            background-color: var(--btn-bg);
            color: white;
            border: none;
        }

        button:hover {
            background-color: var(--btn-hover);
        }

        .alert {
            padding: 12px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .success {
            background-color: var(--alert-success-bg);
            color: var(--alert-success-color);
            border-left: 4px solid #34c759;
        }

        .error {
            background-color: var(--alert-error-bg);
            color: var(--alert-error-color);
            border-left: 4px solid #ff3b30;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9rem;
            color: var(--text-color);
        }

        .toggle-theme {
            text-align: right;
            margin-bottom: 15px;
        }

        .toggle-theme button {
            background: none;
            border: 1px solid var(--text-color);
            padding: 5px 10px;
            border-radius: 6px;
            cursor: pointer;
            color: var(--text-color);
        }

        .toggle-theme button:hover {
            background: var(--btn-bg);
            color: white;
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Dark/light mode toggle -->
    <div class="toggle-theme">
        <button onclick="toggleDarkMode()" id="theme-toggle-btn">
            <span id="theme-icon">🌙</span> <span id="theme-label">Toggle Dark Mode</span>
        </button>
    </div>

    <h1>Unsubscribe from Updates</h1>

    <!-- Step 1: Request unsubscription code -->
    <form method="POST">
        <input type="email" name="unsubscribe_email" required placeholder="Enter your email">
        <button type="submit">Send Unsubscribe Code</button>
    </form>

    <!-- Step 2: Submit verification code to confirm unsubscription -->
    <form method="POST">
        <input type="text" name="unsubscribe_verification_code" maxlength="6" required placeholder="Enter code to confirm unsubscription">
        <button type="submit">Confirm Unsubscribe</button>
    </form>

    <!-- Show result messages -->
    <?php if (!empty($message)): ?>
        <div class="alert <?= strpos($message, 'unsubscribed') || strpos($message, 'sent') ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
</div>

<footer>
    Made by <a href="https://github.com/Knight-Ron" target="_blank">Knight-Ron</a>
</footer>

<script>
    function toggleDarkMode() {
        const isDark = document.body.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateThemeToggle(isDark);
    }

    function updateThemeToggle(isDark) {
        document.getElementById('theme-icon').textContent = isDark ? '🌞' : '🌙';
        document.getElementById('theme-label').textContent = isDark ? 'Toggle Light Mode' : 'Toggle Dark Mode';
    }

    window.onload = () => {
        const isDark = localStorage.getItem('theme') === 'dark';
        if (isDark) {
            document.body.classList.add('dark');
        }
        updateThemeToggle(isDark);
    };
</script>
</body>
</html>
