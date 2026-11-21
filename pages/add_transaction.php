<?php
require_once __DIR__ . '/../includes/db.php'; require_once __DIR__ . '/../includes/auth.php'; require_login();
$uid=(int)$_SESSION['user_id']; $error='';
$categories=db()->query('SELECT id,name,type FROM categories ORDER BY name')->fetchAll();
if ($_SERVER['REQUEST_METHOD']==='POST') { verify_csrf(); $type=$_POST['type']??''; $amount=$_POST['amount']??''; $description=trim($_POST['description']??''); $date=$_POST['transaction_date']??date('Y-m-d'); $category=$_POST['category_id']!==''?(int)$_POST['category_id']:null;
    if (!in_array($type,['income','expense'],true) || !is_numeric($amount) || (float)$amount<=0 || $description==='' || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) $error='Please complete all fields with a valid amount and date.';
    else { $stmt=db()->prepare('INSERT INTO transactions (user_id,category_id,type,amount,description,transaction_date) VALUES (?,?,?,?,?,?)'); $stmt->execute([$uid,$category,$type,(float)$amount,$description,$date]); header('Location: transactions.php?added=1'); exit; }
}
$page_title='Add transaction'; require '../includes/header.php';
?>
<section class="form-card"><h1>Add a transaction</h1><p class="muted">Record money you received or spent.</p><?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Transaction type<select name="type" required><option value="expense">Expense</option><option value="income">Income</option></select></label>
<label>Amount (LKR)<input type="number" name="amount" min="0.01" step="0.01" placeholder="e.g. 1500.00" required></label>
<label>Description<input name="description" maxlength="150" placeholder="e.g. Bus fare" required></label>
<label>Category<select name="category_id"><option value="">Uncategorized</option><?php foreach($categories as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['type']) ?>)</option><?php endforeach; ?></select></label>
<label>Date<input type="date" name="transaction_date" value="<?= e(date('Y-m-d')) ?>" required></label>
<button class="btn" type="submit">Save transaction</button> <a class="btn btn-light" href="dashboard.php">Cancel</a></form></section>
<?php require '../includes/footer.php'; ?>
