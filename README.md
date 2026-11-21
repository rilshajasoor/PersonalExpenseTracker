# Personal Expense Tracker

A beginner-friendly web application built with HTML, CSS, JavaScript, PHP and MySQL. It supports account registration/login, a financial dashboard, adding income and expenses, viewing/searching transactions, and deleting transactions.

## Requirements
- XAMPP (Apache and MySQL)
- A modern web browser

## Installation (XAMPP)
1. Extract the `PersonalExpenseTracker` folder into `C:\xampp\htdocs\`.
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. Visit `http://localhost/phpmyadmin` in your browser.
4. Select **Import**, choose the `database.sql` file from this folder, then click **Import** or **Go**.
5. Check `config.php`. The default XAMPP settings are host `localhost`, database `personal_expense_tracker`, user `root`, and an empty password.
6. Open `http://localhost/PersonalExpenseTracker/`.
7. Register a new account and start adding transactions.

## Project structure
- `index.php` – redirects to login or dashboard
- `config.php` – database connection settings
- `database.sql` – creates the database and tables
- `includes/` – database, authentication, header and footer helpers
- `pages/` – login, registration, dashboard, transactions and logout pages
- `assets/css/style.css` – application styling
- `assets/js/app.js` – transaction table search

## Notes
- Passwords are stored using PHP `password_hash()` and checked using `password_verify()`.
- Database queries use PDO prepared statements.
- Each user can only view and delete their own transactions.
- This is a learning project starter. Before public deployment, add HTTPS, stronger production configuration, backups, and additional validation/security review.
