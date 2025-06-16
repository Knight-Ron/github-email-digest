# GitHub Email Digest

GitHub Email Digest is a lightweight PHP application that allows users to subscribe to receive regular updates on public GitHub events via email. It features email verification, unsubscribe confirmation, HTML-formatted emails, and a user-friendly interface with optional dark mode.

---

## 🌟 Features

* **Email Subscription with Verification**
  Users register with an email and verify it using a 6-digit code sent to their inbox.

* **GitHub Activity Updates via Email**
  Periodically fetches recent GitHub public events and sends an HTML summary to all subscribers.

* **Email-Based Unsubscribe Process**
  Users can initiate an unsubscribe request and confirm it using a secure email code.

* **HTML Emails**
  All emails are styled for readability, including GitHub event summaries and instructions.

* **User-Friendly Web UI**
  Simple UI with clear messages and Dark Mode toggle for better accessibility.

* **File-Based Storage**
  No database required—email data is stored in `registered_emails.txt`.

---

## 🚀 Getting Started

### Prerequisites

* PHP 7.x or later
* Local server (e.g., XAMPP, WAMP, or built-in PHP server)

### Setup Instructions

1. Clone this repository or download the ZIP.
2. Move the project into your local server directory (e.g., `htdocs` in XAMPP).
3. Navigate to the `/src` folder in your browser (e.g., `http://localhost/github-email-digest/src/`).

### Folder Structure

```
/github-email-digest
├── src
│   ├── index.php           # Landing page with subscribe/unsubscribe forms
│   ├── unsubscribe.php     # Handles email unsubscribe logic
│   ├── functions.php       # Contains all core logic
├── registered_emails.txt   # Email storage (auto-created)
```

---

## 🛠 How It Works

### Registration Flow

1. User enters email in form.
2. A 6-digit verification code is sent to their inbox.
3. User submits the code to complete registration.

### Email Digest

* The script `functions.php` fetches public GitHub events and emails a formatted summary to all registered users.

### Unsubscription

1. User clicks the unsubscribe link or enters email.
2. A confirmation code is sent.
3. On confirmation, the email is removed from the list.

---

## 🎨 Dark Mode

Toggle button is available on the UI. Preferences are saved using `localStorage`.

---

## 📬 Cron Job (Optional)

You can schedule the digest to be sent periodically by creating a cron job (Linux/macOS) or Task Scheduler (Windows):

```sh
# Example (Linux): Runs every 12 hours
0 */12 * * * php /path/to/your/project/src/send.php
```

*Note: Ensure your SMTP settings are correctly configured for mail delivery on your server.*

---

## 🙌 Credits

Built using PHP. Designed to be simple, functional, and easy to extend.

---

## 📄 License

This project is licensed under the MIT License.

## 🙋 Author

Ronald Jacob
https://www.linkedin.com/in/ronaldjacob/