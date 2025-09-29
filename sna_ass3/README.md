# XSS Assignment 

## Description
This submission demonstrates safe handling of user comments to prevent Cross-Site Scripting (XSS). The included PHP file (`xss_secure.php`) uses server-side validation, prepared statements (PDO/SQLite), and HTML output escaping to ensure user-supplied input is treated as data, not executable markup.

## Files included
- `xss_secure.php`  — Secure comment app (local demo using SQLite)
- `report.md`       — Descriptive report (concept, steps, mitigation)

## Local run instructions
1. Put `xss_secure.php` in an empty folder.
2. From that folder run:
   ```bash
   php -S localhost:8000
