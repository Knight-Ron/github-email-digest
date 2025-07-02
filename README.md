# GitHub Email Digest

A lightweight PHP project that lets users subscribe and receive GitHub public event updates via email—without needing a database.

---

## What This Project Does

GitHub Email Digest is a beginner-friendly, file-based PHP tool that:

* Lets users **subscribe** to GitHub updates using their email.
* Sends a **verification code** to confirm email validity.
* Allows users to **unsubscribe** securely with a code.
* Uses a **simple `.txt` file** to store subscribers (no MySQL needed).
* Sends **HTML-formatted GitHub activity summaries** via email using a cron job.

---

## Requirements

* PHP 7.4 or higher (with CLI support)
* Composer (for PHPMailer)
* Access to a Gmail account + [App Password](https://support.google.com/accounts/answer/185833?hl=en)
* Local server (like XAMPP or WAMP)

---

## Features

* **Verified Email Subscription** (via code)
* **Secure Unsubscription**
* **Dark/Light Mode UI** (saved with `localStorage`)
* **No database** — uses `registered_emails.txt`
* **HTML Emails** summarizing recent GitHub public events

---

## Tech Stack

* PHP 7.4+
* GitHub REST API v3
* PHPMailer
* HTML5 / CSS3 (vanilla)

---

## Setup Guide (Step-by-Step)

### 1. Clone the Repository

```bash
git clone https://github.com/Knight-Ron/github-email-digest.git
cd github-email-digest
```

### 2. Install PHPMailer via Composer

```bash
composer require phpmailer/phpmailer
```

### 3. Configure Gmail for SMTP

1. Visit [Google App Passwords](https://myaccount.google.com/apppasswords)
2. Generate a 16-digit password for "Mail > Windows Computer"
3. Copy it for later use

### 4. Create a `.env` File

Inside your project root:

```dotenv
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=yourgmail@gmail.com
SMTP_PASS=your_app_password
SMTP_FROM=yourgmail@gmail.com
```

> Make sure `.env` is in your `.gitignore` file

### 5. Start Your Server

Place the project in your server directory:

* For XAMPP: `C:/xampp/htdocs/github-email-digest/`

Then visit in browser:

```
http://localhost/github-email-digest/src/
```

---

## Project Structure

```
github-email-digest/
├── src/
│   ├── index.php          # Main UI (subscribe / unsubscribe)
│   ├── unsubscribe.php    # Separate unsubscribe page
│   ├── functions.php      # Core logic (email, GitHub fetch, etc)
│   └── cron.php           # Script to send digest emails
├── .env                   # Your Gmail credentials (private)
├── registered_emails.txt  # Auto-created email list
├── .gitignore             # Ignores .env, email list, logs
```

---

## Email Flow

### Subscribing

1. User submits their email
2. Receives a 6-digit verification code
3. Enters code to confirm
4. Email is added to list

### Unsubscribing

1. User enters their email to unsubscribe
2. Receives a confirmation code
3. Submits code to be removed

### Receiving GitHub Updates

1. You run `cron.php` manually or via CRON job
2. GitHub events are fetched and emailed as HTML

---

## Set Up a CRON Job (Optional)

To automate email sending:

**Linux example (every 12 hrs):**

```bash
0 */12 * * * php /path/to/github-email-digest/src/cron.php
```

**Windows Task Scheduler:**

* Use `php.exe path/to/src/cron.php`
* Trigger daily or hourly as needed

---

## Tips & Notes

* Test email by running:

```bash
php src/cron.php
```

* Check your spam folder during first tests
* Secure the `registered_emails.txt` if deployed publicly
* Gmail has sending limits for free accounts

---

## Author

**Ronald Jacob**
GitHub: [@Knight-Ron](https://github.com/Knight-Ron)
LinkedIn: [linkedin.com/in/ronaldjacob](https://www.linkedin.com/in/ronaldjacob)
