# Incident Report System v2.0

A modern, secure web-based incident reporting and management system built with PHP, MySQL, Bootstrap 5, and jQuery.

## Features

### Security
- ✅ CSRF protection on all forms
- ✅ Bcrypt password hashing (cost factor 12)
- ✅ PDO prepared statements (SQL injection prevention)
- ✅ Brute force protection (lockout after 5 failed attempts)
- ✅ Password strength validation (uppercase, lowercase, number, symbol)
- ✅ Secure session management (strict mode, session rotation, httponly cookies)
- ✅ XSS prevention (output escaping via `Security::e()`)
- ✅ Secure HTTP headers (CSP, X-Frame-Options, X-XSS-Protection, etc.)
- ✅ `.htaccess` blocks direct access to sensitive directories
- ✅ Role-based access control (user / manager / admin)
- ✅ Session timeout (1 hour inactivity)

### Core Features
- 📋 Report incidents with title, description, priority, category, location
- 👥 User registration and authentication
- 🎯 Assign incidents to users
- 💬 Comment on incidents
- 🔄 Status workflow: Open → In Progress → Resolved → Closed
- 🕒 Activity log / timeline per incident
- 🔔 Notifications system
- 📊 Dashboard with charts (7-day trend, status donut)
- 🔍 Search and filter incidents
- 📄 Pagination

### UI / UX
- 🎨 Modern Bootstrap 5 UI with custom CSS
- 📱 Fully responsive (mobile-friendly)
- 🌙 Clean sidebar-free layout
- ⚡ Live search with debounce
- 💪 Password strength meter
- 🔔 Auto-polling notifications (every 60s)
- ⏱️ Relative timestamps (e.g. "2 hours ago")
- 🏷️ Priority and status badges
- 🖱️ Confirmation dialogs for destructive actions
- 🔢 Character counter on forms
- ⬆️ Auto-resize textareas

## Tech Stack

| Layer      | Technology              |
|------------|-------------------------|
| Backend    | PHP 8.1+                |
| Database   | MySQL 8.0+              |
| Frontend   | Bootstrap 5.3, Bootstrap Icons |
| Charts     | Chart.js 4              |
| ORM/DB     | PDO with prepared statements |

## Installation

1. Clone or download the repository
2. Create a MySQL database: `incident_report_db`
3. Copy `config.php` and update DB credentials
4. Edit `setup.php` and change the `SETUP_KEY` constant
5. Visit `http://yourdomain/IncidentReportSystem/setup.php?key=YOUR_KEY`
6. Log in with `admin@example.com` / `Admin@1234`
7. **Delete `setup.php` immediately after setup!**

## Directory Structure

```
IncidentReportSystem/
├── index.php             # Login
├── register.php          # Registration
├── dashboard.php         # Main dashboard
├── logout.php
├── profile.php
├── change-password.php
├── setup.php             # One-time setup (delete after use)
├── schema.sql            # Database schema
├── config.php            # Configuration
├── .htaccess             # Apache security rules
├── includes/
│   ├── db.php            # PDO database singleton
│   ├── auth.php          # Authentication class
│   ├── security.php      # Security class (CSRF, hashing, sanitization)
│   ├── functions.php     # Helper functions
│   ├── header.php      # Shared header/navbar
│   └── footer.php       # Shared footer
├── incidents/
│   ├── list.php          # Incident list with search/filter
│   ├── create.php        # Report new incident
│   ├── view.php          # Incident detail + comments
│   ├── edit.php          # Edit incident
│   └── delete.php        # Delete (admin only)
├── admin/
│   └── users.php         # User management
├── api/
│   └── notifications.php # Notification API
└── assets/
    ├── css/style.css
    └── js/app.js
```

## Credits

- Bootstrap 5
- Bootstrap Icons
- Chart.js
- PHP / MySQL
