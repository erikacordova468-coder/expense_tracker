<?php
// Overview: monthly total, category budgets, recent entries
require 'db_connect.php';
$pageTitle = 'Overview';
$active = 'home';

$month = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) $month = date('Y-m');
$start = $month . '-01';
$end = date('Y-m-t', strtotime($start));
$isCurrent = ($month === date('Y-m'));
$daysCounted = $isCurrent ? (int)date('j') : (int)date('t', strtotime($start));

$stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) total, COUNT(*) cnt FROM expenses WHERE expense_date BETWEEN ? AND ?");
$stmt->bind_param('ss', $start, $end);
$stmt->execute();
$sum = $stmt->get_result()->fetch_assoc();
$total = (float)$sum['total'];
$count = (int)$sum['cnt'];
$perDay = $daysCounted > 0 ? $total / $daysCounted : 0;

$stmt = $conn->prepare("SELECT c.id, c.name, c.color, c.monthly_budget, COALESCE(SUM(e.amount),0) spent
                        FROM categories c LEFT JOIN expenses e ON e.category_id = c.id AND e.expense_date BETWEEN ? AND ?
                        GROUP BY c.id, c.name, c.color, c.monthly_budget ORDER BY spent DESC, c.name");
$stmt->bind_param('ss', $start, $end);
$stmt->execute();
$cats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$topCat = ($cats && $cats[0]['spent'] > 0) ? $cats[0]['name'] : 'None yet';
$totalBudget = array_sum(array_column($cats, 'monthly_budget'));

$recent = $conn->query("SELECT e.*, c.name AS category, c.color FROM expenses e JOIN categories c ON c.id = e.category_id ORDER BY e.expense_date DESC, e.id DESC LIMIT 6");

include 'includes/header.php';
?>
<div class="page-head">
    <div>
        <div class="kicker"><?= date('F Y', strtotime($start)) ?></div>
        <h1>Overview</h1>
    </div>
    <form method="get" class="d-flex gap-2">
        <input type="month" name="month" value="<?= e($month) ?>" class="form-control" onchange="this.form.submit()">
        <noscript><button class="btn btn-forest">Go</button></noscript>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3"><div class="sheet stat hero h-100"><div class="stat-label">Spent this month</div><div class="stat-value"><?= peso($total) ?></div></div></div>
    <div class="col-md-6 col-lg-3"><div class="sheet stat h-100"><div class="stat-label">Entries</div><div class="stat-value"><?= $count ?></div></div></div>
    <div class="col-md-6 col-lg-3"><div class="sheet stat h-100"><div class="stat-label">Average per day</div><div class="stat-value"><?= peso($perDay) ?></div></div></div>
    <div class="col-md-6 col-lg-3"><div class="sheet stat h-100"><div class="stat-label">Biggest category</div><div class="stat-value" style="font-size:1.15rem"><?= e($topCat) ?></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="sheet h-100">
            <div class="sheet-head">Budget by category</div>
            <div class="p-3">
                <?php if (!$cats): ?><p class="text-muted mb-0">No categories yet.</p><?php endif; ?>
                <?php foreach ($cats as $c):
                    $budget = (float)$c['monthly_budget'];
                    $spent = (float)$c['spent'];
                    $pct = $budget > 0 ? min(100, round($spent / $budget * 100)) : ($spent > 0 ? 100 : 0);
                    $isOver = $budget > 0 && $spent > $budget;
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span><span class="dot" style="background:<?= e($c['color']) ?>"></span><?= e($c['name']) ?></span>
                        <span class="mono <?= $isOver ? 'over' : '' ?>"><?= peso($spent) ?><?= $budget > 0 ? ' / ' . peso($budget) : '' ?></span>
                    </div>
                    <div class="bar-track"><div class="bar-fill" style="width:<?= $pct ?>%;background:<?= $isOver ? 'var(--stamp)' : e($c['color']) ?>"></div></div>
                    <?php if ($isOver): ?><div class="small over mt-1">Over budget by <?= peso($spent - $budget) ?></div><?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if ($totalBudget > 0): ?>
                <div class="small text-muted border-top pt-2 mono">Total budget: <?= peso($totalBudget) ?> &middot; Remaining: <?= peso($totalBudget - $total) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="sheet h-100">
            <div class="sheet-head d-flex justify-content-between"><span>Latest entries</span><a href="expenses.php" class="small">View all</a></div>
            <div class="table-responsive">
                <table class="table ledger align-middle">
                    <tbody>
                    <?php if ($recent->num_rows === 0): ?><tr><td class="text-center text-muted py-4">Nothing logged yet.</td></tr><?php endif; ?>
                    <?php while ($r = $recent->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($r['title']) ?></div>
                                <div class="small text-muted"><span class="dot" style="background:<?= e($r['color']) ?>"></span><?= e($r['category']) ?> &middot; <?= date('M d', strtotime($r['expense_date'])) ?></div>
                            </td>
                            <td class="text-end amount"><?= peso($r['amount']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
