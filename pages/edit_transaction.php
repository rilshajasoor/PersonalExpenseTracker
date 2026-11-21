
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$uid = (int)$_SESSION['user_id'];
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$error = '';

$stmt = db()->prepare(
    'SELECT * FROM transactions WHERE id = ? AND user_id = ?'
);
$stmt->execute([$id, $uid]);
$transaction = $stmt->fetch();

if (!$transaction) {
    http_response_code(404);
    exit('Transaction not found.');
}

$categories = db()->query(
    'SELECT id, name, type FROM categories ORDER BY name'
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $type = $_POST['type'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['transaction_date'] ?? '';
    $category = ($_POST['category_id'] ?? '') !== ''
        ? (int)$_POST['category_id']
        : null;

    if (
        !in_array($type, ['income', 'expense'], true) ||
        !is_numeric($amount) ||
        (float)$amount <= 0 ||
        $description === '' ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
    ) {
        $error = 'Please enter valid transaction details.';
    } else {
        $stmt = db()->prepare(
            'UPDATE transactions
             SET category_id = ?, type = ?, amount = ?,
                 description = ?, transaction_date = ?
             WHERE id = ? AND user_id = ?'
        );

        $stmt->execute([
            $category,
            $type,
            (float)$amount,
            $description,
            $date,
            $id,
            $uid
        ]);

        header('Location: transactions.php?updated=1');
        exit;
    }
}

$page_title = 'Edit transaction';
require '../includes/header.php';
?>

<section class="form-card">
    <h1>Edit transaction</h1>
    <p class="muted">Update your income or expense details.</p>

    <?php if ($error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token"
               value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">

        <label>Transaction type
            <select name="type" required>
                <option value="expense"
                    <?= $transaction['type'] === 'expense' ? 'selected' : '' ?>>
                    Expense
                </option>
                <option value="income"
                    <?= $transaction['type'] === 'income' ? 'selected' : '' ?>>
                    Income
                </option>
            </select>
        </label>

        <label>Amount (LKR)
            <input type="number" name="amount" min="0.01"
                step="0.01" required
                value="<?= e($transaction['amount']) ?>">
        </label>

        <label>Description
            <input name="description" maxlength="150" required
                value="<?= e($transaction['description']) ?>">
        </label>

        <label>Category
            <select name="category_id">
                <option value="">Uncategorized</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"
                        <?= (int)$transaction['category_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?> (<?= e($c['type']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Date
            <input type="date" name="transaction_date"
                value="<?= e($transaction['transaction_date']) ?>"
                required>
        </label>

        <button class="btn" type="submit">Update transaction</button>
        <a class="btn btn-light" href="transactions.php">Cancel</a>
    </form>
</section>

<?php require '../includes/footer.php'; ?>
