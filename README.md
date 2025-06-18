<h1 align="center">GitHub Email Digest</h1>

<p align="center">
A lightweight PHP project that lets users subscribe and receive GitHub public event updates via email—without any database or dependencies.
</p>

---

## 🚧 Project Overview

GitHub Email Digest is a PHP-based utility that enables email-based subscriptions to public GitHub event updates. It includes email verification, unsubscription with confirmation codes, and a clean dark/light themed UI. Emails are sent as formatted HTML summaries of recent GitHub activity.

---

## 🔍 Key Features

- **Verified Email Subscriptions**  
  Secure 6-digit code verification before registration.

- **HTML Email Digest**  
  Sends formatted summaries of public GitHub events using the GitHub Events API.

- **Email Unsubscribe with Confirmation**  
  Users confirm removal with a verification code.

- **Dark Mode Support**  
  Includes a dark mode toggle; remembers user preference using `localStorage`.

- **No Database Required**  
  Uses a simple text file (`registered_emails.txt`) to store email addresses.

---

## 🧰 Tech Stack

- PHP 7.x+
- GitHub REST API v3
- HTML5/CSS3 (No frameworks)
- File-based storage

---

## 🔧 Setup Instructions

1. **Clone or Download the Repository**

   ```bash
   git clone https://github.com/your-username/github-email-digest
   ```

2. **Place the Project in Your Server Directory**  
   For example, in `htdocs/` for XAMPP or `/var/www/html/` for Apache.

3. **Visit in Browser**

   ```
   http://localhost/github-email-digest/src/
   ```

4. **Configure Cron Job (Optional)**  
   Set up scheduled GitHub updates via:

   ```bash
   # Example: Send updates every 12 hours
   0 */12 * * * php /path/to/src/cron.php
   ```

---

## 🗂️ Folder Structure

```
github-email-digest/
├── src/
│   ├── index.php            # Subscription & unsubscription UI
│   ├── functions.php        # Core logic (mail, GitHub API, storage)
│   ├── unsubscribe.php      # Optional fallback unsubscribe endpoint
│   └── cron.php             # Script to send digest via cron
├── registered_emails.txt    # Email list (auto-created)
```

---

## 📬 Email Flow

- **Subscription**  
  1. User enters their email.  
  2. Receives a 6-digit code.  
  3. Verifies code to subscribe.

- **Unsubscribe**  
  1. User requests removal.  
  2. Receives confirmation code.  
  3. Confirms to be removed from mailing list.

- **Digest Delivery**  
  When `cron.php` runs, it fetches recent GitHub events and emails all verified subscribers.

---

## 📎 Notes

- Ensure your local server or hosting provider supports `mail()` function.
- For production, consider switching to SMTP with authentication for reliable delivery.
- Email list is stored in plaintext—do not deploy this as-is to public servers without securing it.

---

## 👤 Author

**Ronald Jacob**  
[LinkedIn](https://www.linkedin.com/in/ronaldjacob)  
[GitHub](https://github.com/Knight-Ron)

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

