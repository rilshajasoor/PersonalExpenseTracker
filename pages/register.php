<?php
require_once __DIR__ . '/../includes/db.php'; require_once __DIR__ . '/../includes/auth.php';
if (is_logged_in()) { header('Location: dashboard.php'); exit; }
$error = ''; $name = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $password = $_POST['password'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) $error = 'Enter your name, a valid email and a password with at least 6 characters.';
    else {
        try { $stmt = db()->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)'); $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]); $_SESSION['user_id']=(int)db()->lastInsertId(); $_SESSION['user_name']=$name; header('Location: dashboard.php'); exit; }
        catch (PDOException $ex) { $error = $ex->getCode()==='23000' ? 'That email is already registered.' : 'Could not create your account. Check your database setup.'; }
    }
}
$page_title='Create account'; require '../includes/header.php';
?>
<section class="auth-card"><h1>Create your account</h1><p class="muted">Start tracking your income and expenses.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Full name<input name="name" value="<?= e($name) ?>" required></label>
<label>Email address<input type="email" name="email" value="<?= e($email) ?>" required></label>
<label>Password<input type="password" name="password" minlength="6" required></label>
<button class="btn full" type="submit">Register</button></form><p class="form-foot">Already have an account? <a href="login.php">Login</a></p></section>
<?php require '../includes/footer.php'; ?>
