<?php
session_start(); // Used to store temporary verification/unsubscribe codes

require_once 'functions.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 📩 Registration flow
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);

        // Validate email format before proceeding
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();

            // Store code and email in session for verification step
            $_SESSION['verification_code'] = $code;
            $_SESSION['email'] = $email;

            // Send the verification code to the user's email
            if (sendVerificationEmail($email, $code)) {
                $message = "A verification code was sent to $email.";
            } else {
                $message = "Failed to send verification code. Please try again.";
            }
        } else {
            $message = "Invalid email format.";
        }
    }

    // Handle verification step
    if (isset($_POST['verification_code'])) {
        $userCode = trim($_POST['verification_code']);

        // Match user input with stored session code
        if ($userCode === ($_SESSION['verification_code'] ?? '')) {
            if (registerEmail($_SESSION['email'])) {
                $message = "Email registered successfully!";
            } else {
                $message = "Email is already registered.";
            }
            // Clear session data after use
            unset($_SESSION['verification_code'], $_SESSION['email']);
        } else {
            $message = "Incorrect verification code.";
        }
    }

    // Unsubscribe flow: step 1 — request
    if (isset($_POST['unsubscribe_email'])) {
        $email = trim($_POST['unsubscribe_email']);

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $file = __DIR__ . '/registered_emails.txt';
            $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

            if (!in_array($email, $emails)) {
                $message = "This email is not subscribed.";
            } else {
                $code = generateVerificationCode();

                // Store unsubscribe code/email for confirmation step
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

    // Unsubscribe flow: step 2 — verify code
    if (isset($_POST['unsubscribe_verification_code'])) {
        $code = trim($_POST['unsubscribe_verification_code']);
        $email = $_SESSION['unsubscribe_email'] ?? '';

        if ($code === ($_SESSION['unsubscribe_code'] ?? '')) {
            if (unsubscribeEmail($email)) {
                $message = "You have been unsubscribed.";
            } else {
                $message = "Email not found or already unsubscribed.";
            }
            // Clear session data
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
    <title>Email Subscription System</title>
    <style>
        :root {
            --bg-color: #f0f4f8;
            --text-color: #333;
            --card-bg: white;
            --input-bg: white;
            --btn-bg: #0366d6;
            --btn-hover: #024a9c;
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
            --btn-bg: #0a84ff;
            --btn-hover: #0060c0;
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

        .tabs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .tab-btn {
            flex: 1;
            text-align: center;
            padding: 10px;
            background-color: #e0e0e0;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        .tab-btn.active {
            background-color: var(--btn-bg);
            color: white;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
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
            color: #777;
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
    <div class="toggle-theme">
    <button onclick="toggleDarkMode()" id="theme-toggle-btn">
        <span id="theme-icon">🌙</span> <span id="theme-label">Toggle Dark Mode</span>
    </button>
</div>

    <h1>📬 GitHub Email Updates</h1>

    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('subscribe')">Subscribe</button>
        <button class="tab-btn" onclick="switchTab('unsubscribe')">Unsubscribe</button>
    </div>

    <div id="subscribe" class="form-section active">
        <form method="POST">
            <input type="email" name="email" required placeholder="Enter your email">
            <button type="submit">Send Verification Code</button>
        </form>

        <form method="POST">
            <input type="text" name="verification_code" maxlength="6" required placeholder="Enter verification code">
            <button type="submit">Verify & Subscribe</button>
        </form>
    </div>

    <div id="unsubscribe" class="form-section">
        <form method="POST">
            <input type="email" name="unsubscribe_email" required placeholder="Enter your email">
            <button type="submit">Send Unsubscribe Code</button>
        </form>

        <form method="POST">
            <input type="text" name="unsubscribe_verification_code" maxlength="6" required placeholder="Enter code to confirm unsubscription">
            <button type="submit">Confirm Unsubscribe</button>
        </form>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert <?= strpos($message, 'successfully') || strpos($message, 'sent') ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
</div>

<footer>
    Made with by <a href="https://github.com/Knight-Ron" target="_blank">Knight-Ron</a>
</footer>

<script>
    // Toggles dark mode class and stores user preference in localStorage
    function toggleDarkMode() {
        const isDark = document.body.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateThemeToggle(isDark);
    }

    // Updates icon and label based on current theme
    function updateThemeToggle(isDark) {
        document.getElementById('theme-icon').textContent = isDark ? '🌞' : '🌙';
        document.getElementById('theme-label').textContent = isDark ? 'Toggle Light Mode' : 'Toggle Dark Mode';
    }

    // Apply saved theme on page load
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
