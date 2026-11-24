<?php
require_once __DIR__ . '/../includes/db.php'; require_once __DIR__ . '/../includes/auth.php';
if (is_logged_in()) { header('Location: dashboard.php'); exit; }
$error=''; $email='';
if ($_SERVER['REQUEST_METHOD']==='POST') { verify_csrf(); $email=trim($_POST['email']??''); $password=$_POST['password']??'';
    $stmt=db()->prepare('SELECT id,name,password FROM users WHERE email=?'); $stmt->execute([$email]); $user=$stmt->fetch();
    if ($user && password_verify($password,$user['password'])) { session_regenerate_id(true); $_SESSION['user_id']=(int)$user['id']; $_SESSION['name']=$user['name']; header('Location: dashboard.php'); exit; }
    $error='Incorrect email or password.';
}
$page_title='Login'; require '../includes/header.php';
?>
<section class="auth-card"><h1>Welcome back</h1><p class="muted">Login to manage your finances.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Email address<input type="email" name="email" value="<?= e($email) ?>" required></label>
<label>Password<input type="password" name="password" required></label>
<button class="btn full" type="submit">Login</button></form><p class="form-foot">New user? <a href="register.php">Create an account</a></p></section>
<?php require '../includes/footer.php'; ?>
