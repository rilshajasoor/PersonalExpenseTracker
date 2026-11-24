<?php
require_once __DIR__ . '/../includes/db.php'; require_once __DIR__ . '/../includes/auth.php'; require_login();
$uid=(int)$_SESSION['user_id'];
$stmt=db()->prepare("SELECT COALESCE(SUM(CASE WHEN type='income' THEN amount ELSE 0 END),0) income, COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END),0) expenses FROM transactions WHERE user_id=?"); $stmt->execute([$uid]); $totals=$stmt->fetch();
$balance=(float)$totals['income']-(float)$totals['expenses'];
$stmt=db()->prepare('SELECT t.*, c.name category_name FROM transactions t LEFT JOIN categories c ON c.id=t.category_id WHERE t.user_id=? ORDER BY t.transaction_date DESC,t.id DESC LIMIT 6'); $stmt->execute([$uid]); $recent=$stmt->fetchAll();

// Prepare monthly chart data for the last 6 months

$firstMonth = new DateTimeImmutable('first day of this month');
$firstMonth = $firstMonth->modify('-5 months');

$startDate = $firstMonth->format('Y-m-d');
$endDate = (new DateTimeImmutable('first day of next month'))
    ->format('Y-m-d');

$stmt = db()->prepare(
    "SELECT
        DATE_FORMAT(transaction_date, '%Y-%m') AS month_key,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expenses
     FROM transactions
     WHERE user_id = ?
       AND transaction_date >= ?
       AND transaction_date < ?
     GROUP BY month_key
     ORDER BY month_key"
);

$stmt->execute([$uid, $startDate, $endDate]);
$monthlyRows = $stmt->fetchAll();

$monthlyData = [];

foreach ($monthlyRows as $row) {
    $monthlyData[$row['month_key']] = $row;
}

$chartLabels = [];
$chartIncome = [];
$chartExpenses = [];

for ($i = 0; $i < 6; $i++) {
    $month = $firstMonth->modify("+$i months");
    $key = $month->format('Y-m');

    $chartLabels[] = $month->format('M Y');

    $chartIncome[] = (float)($monthlyData[$key]['income'] ?? 0);
    $chartExpenses[] = (float)($monthlyData[$key]['expenses'] ?? 0);
}

$page_title='Dashboard'; require '../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">OVERVIEW</p>
        <h1>Hello, <?= e($_SESSION['name'] ?? 'User') ?> 👋</h1>
        <p class="muted">Here is a summary of your finances.</p>
    </div>

    <a class="btn" href="add_transaction.php">+ Add transaction</a>
</div>

<section class="summary-grid">
<div class="summary-card"><span>Total income</span><strong class="positive">Rs. <?= number_format((float)$totals['income'],2) ?></strong></div>
<div class="summary-card"><span>Total expenses</span><strong class="negative">Rs. <?= number_format((float)$totals['expenses'],2) ?></strong></div>
<div class="summary-card"><span>Current balance</span><strong>Rs. <?= number_format($balance,2) ?></strong></div>
</section>

<section class="panel">
    <div class="panel-heading">
        <h2>Monthly Income vs Expenses</h2>
        <span class="muted">Last 6 months</span>
    </div>

    <div style="position: relative; height: 320px;">
        <canvas id="monthlyChart"></canvas>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const chartLabels = <?= json_encode($chartLabels) ?>;
const chartIncome = <?= json_encode($chartIncome) ?>;
const chartExpenses = <?= json_encode($chartExpenses) ?>;

const chartElement = document.getElementById('monthlyChart');

new Chart(chartElement, {
    type: 'bar',

    data: {
        labels: chartLabels,

        datasets: [
            {
                label: 'Income',
                data: chartIncome,
                backgroundColor: '#22c55e',
                borderRadius: 5
            },
            {
                label: 'Expenses',
                data: chartExpenses,
                backgroundColor: '#ef4444',
                borderRadius: 5
            }
        ]
    },

    options: {
        responsive: true,
        maintainAspectRatio: false,

        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Amount (LKR)'
                }
            }
        }
    }
});
</script>

<section class="panel"><div class="panel-heading"><h2>Recent transactions</h2><a href="transactions.php">View all</a></div>
<?php if (!$recent): ?><p class="empty">No transactions yet. Add your first income or expense.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Type</th><th class="right">Amount</th></tr></thead><tbody>
<?php foreach($recent as $t): ?><tr><td><?= e($t['transaction_date']) ?></td><td><?= e($t['description']) ?></td><td><?= e($t['category_name']??'Uncategorized') ?></td><td><span class="pill <?= e($t['type']) ?>"><?= e(ucfirst($t['type'])) ?></span></td><td class="right <?= $t['type']==='income'?'positive':'negative' ?>"> <?= $t['type']==='income'?'+':'−' ?> Rs. <?= number_format((float)$t['amount'],2) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require '../includes/footer.php'; ?>
