
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$uid = (int)$_SESSION['user_id'];

// Delete transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verify_csrf();

    $stmt = db()->prepare(
        'DELETE FROM transactions WHERE id = ? AND user_id = ?'
    );
    $stmt->execute([(int)$_POST['delete_id'], $uid]);

    header('Location: transactions.php?deleted=1');
    exit;
}

// Get transactions
$stmt = db()->prepare(
    'SELECT t.*, c.name AS category_name
     FROM transactions t
     LEFT JOIN categories c ON c.id = t.category_id
     WHERE t.user_id = ?
     ORDER BY t.transaction_date DESC, t.id DESC'
);
$stmt->execute([$uid]);
$rows = $stmt->fetchAll();

$page_title = 'Transactions';
require '../includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">RECORDS</p>
        <h1>Transactions</h1>
        <p class="muted">All your recorded income and expenses.</p>
    </div>

    <a class="btn" href="add_transaction.php">
        + Add transaction
    </a>
</div>

<?php if (isset($_GET['added'])): ?>
    <div class="alert success">Transaction added successfully.</div>
<?php endif; ?>

<?php if (isset($_GET['updated'])): ?>
    <div class="alert success">Transaction updated successfully.</div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert success">Transaction deleted.</div>
<?php endif; ?>

<section class="panel">

    <div class="filter-row">
        <input id="tableSearch" type="search"
               placeholder="Search description or category...">

        <span class="muted">
            <?= count($rows) ?> record(s)
        </span>
    </div>

    <?php if (!$rows): ?>

        <p class="empty">
            No transactions found. Add your first transaction.
        </p>

    <?php else: ?>

        <div class="table-wrap">
            <table id="transactionsTable">

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th class="right">Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($rows as $t): ?>

                        <tr>
                            <td><?= e($t['transaction_date']) ?></td>

                            <td><?= e($t['description']) ?></td>

                            <td>
                                <?= e($t['category_name'] ?? 'Uncategorized') ?>
                            </td>

                            <td>
                                <span class="pill <?= e($t['type']) ?>">
                                    <?= e(ucfirst($t['type'])) ?>
                                </span>
                            </td>

                            <td class="right <?= $t['type'] === 'income' ? 'positive' : 'negative' ?>">
                                Rs. <?= number_format((float)$t['amount'], 2) ?>
                            </td>

                            <td>
                                <!-- Edit button -->
                                <a class="btn btn-light"
                                   href="edit_transaction.php?id=<?= (int)$t['id'] ?>">
                                    Edit
                                </a>

                                <!-- Delete button -->
                                <form method="post" class="inline-form"
                                      onsubmit="return confirm('Delete this transaction?')">

                                    <input type="hidden" name="csrf_token"
                                           value="<?= e(csrf_token()) ?>">

                                    <input type="hidden" name="delete_id"
                                           value="<?= (int)$t['id'] ?>">

                                    <button class="text-button danger-text"
                                            type="submit">
                                        Delete
                                    </button>

                                </form>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

    <?php endif; ?>

</section>

<?php require '../includes/footer.php'; ?>
