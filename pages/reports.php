
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$uid = (int)$_SESSION['user_id'];

// Get selected month
$selectedMonth = $_GET['month'] ?? date('Y-m');

if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
    $selectedMonth = date('Y-m');
}

// Calculate month date range
$startDate = $selectedMonth . '-01';
$endDate = (new DateTimeImmutable($startDate))
    ->modify('+1 month')
    ->format('Y-m-d');

// Get income and expense totals
$stmt = db()->prepare(
    "SELECT type, SUM(amount) AS total
     FROM transactions
     WHERE user_id = ?
     AND transaction_date >= ?
     AND transaction_date < ?
     GROUP BY type"
);

$stmt->execute([$uid, $startDate, $endDate]);
$totals = $stmt->fetchAll();

$income = 0;
$expense = 0;

foreach ($totals as $row) {
    if ($row['type'] === 'income') {
        $income = (float)$row['total'];
    } else {
        $expense = (float)$row['total'];
    }
}

$balance = $income - $expense;

// Get category-wise report
$stmt = db()->prepare(
    "SELECT
        t.type,
        COALESCE(c.name, 'Uncategorized') AS category_name,
        SUM(t.amount) AS total
     FROM transactions t
     LEFT JOIN categories c ON c.id = t.category_id
     WHERE t.user_id = ?
     AND t.transaction_date >= ?
     AND t.transaction_date < ?
     GROUP BY t.type, c.name
     ORDER BY t.type, category_name"
);

$stmt->execute([$uid, $startDate, $endDate]);
$categoryReports = $stmt->fetchAll();

$page_title = 'Monthly Reports';
require '../includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">REPORTS</p>
        <h1>Monthly Reports</h1>
        <p class="muted">View your monthly income and expenses.</p>
    </div>
</div>

<section class="panel">

    <h2>Select Month</h2>

    <form method="get">
        <label for="month">Choose a month</label>

        <input
            type="month"
            id="month"
            name="month"
            value="<?= e($selectedMonth) ?>"
            required
        >

        <button type="submit" class="btn">
            View Report
        </button>
    </form>

</section>

<section class="panel">

    <h2>Monthly Summary</h2>

    <p>
        <strong>Selected Month:</strong>
        <?= e(date('F Y', strtotime($startDate))) ?>
    </p>

    <p>
        <strong>Total Income:</strong>
        <span class="positive">
            Rs. <?= number_format($income, 2) ?>
        </span>
    </p>

    <p>
        <strong>Total Expenses:</strong>
        <span class="negative">
            Rs. <?= number_format($expense, 2) ?>
        </span>
    </p>

    <p>
        <strong>Remaining Balance:</strong>
        Rs. <?= number_format($balance, 2) ?>
    </p>

</section>

<section class="panel">

    <h2>Category-wise Report</h2>

    <?php if (!$categoryReports): ?>

        <p class="empty">
            No transactions found for this month.
        </p>

    <?php else: ?>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Type</th>
                        <th class="right">Amount</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($categoryReports as $report): ?>

                        <tr>
                            <td>
                                <?= e($report['category_name']) ?>
                            </td>

                            <td>
                                <?= e(ucfirst($report['type'])) ?>
                            </td>

                            <td class="right">
                                Rs. <?= number_format(
                                    (float)$report['total'],
                                    2
                                ) ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

    <?php endif; ?>

</section>

<?php require '../includes/footer.php'; ?>
