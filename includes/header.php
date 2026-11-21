<?php
require_once __DIR__ . '/auth.php';
$page_title = $page_title ?? 'Personal Expense Tracker';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title) ?> | Personal Expense Tracker</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=2">
  <script defer src="../assets/js/app.js"></script>
</head>
<body>
<header class="topbar"><a class="brand" href="<?= is_logged_in() ? 'dashboard.php' : 'login.php' ?>">💰 Personal Expense Tracker</a>

<?php if (is_logged_in()): ?>
<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="transactions.php">Transactions</a>
    <a href="add_transaction.php">Add transaction</a>
    <a href="reports.php">Reports</a>
    <a href="categories.php">Categories</a>
    <a href="logout.php">Logout</a>
</nav>
<?php endif; ?>
</header><main class="container">
