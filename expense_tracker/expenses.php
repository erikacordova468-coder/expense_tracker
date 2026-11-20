<?php
// SELECT: list expenses with search, category and month filters
require 'db_connect.php';
$pageTitle = 'Expenses';
$active = 'expenses';

$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['category'] ?? 0);
$month = $_GET['month'] ?? '';
if ($month !== '' && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) $month = '';

$sql = "SELECT e.*, c.name AS category, c.color FROM expenses e JOIN categories c ON c.id = e.category_id
        WHERE (e.title LIKE ? OR e.notes LIKE ?)";
$like = '%' . $q . '%';
$types = 'ss';
$params = [$like, $like];
if ($cat > 0) { $sql .= " AND e.category_id = ?"; $types .= 'i'; $params[] = $cat; }
if ($month !== '') {
    $sql .= " AND e.expense_date BETWEEN ? AND ?";
    $types .= 'ss';
    $params[] = $month . '-01';
    $params[] = date('Y-m-t', strtotime($month . '-01'));
}
$sql .= " ORDER BY e.expense_date DESC, e.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$filteredTotal = array_sum(array_column($rows, 'amount'));
$allCats = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>
<div class="page-head">
    <div><div class="kicker">Every peso, written down</div><h1>Expenses</h1></div>
    <a href="expense_form.php" class="btn btn-stamp"><i class="bi bi-plus-lg"></i> Log expense</a>
</div>

<form class="row g-2 mb-3" method="get">
    <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Search title or notes..." value="<?= e($q) ?>"></div>
    <div class="col-md-3">
        <select name="category" class="form-select">
            <option value="0">All categories</option>
            <?php foreach ($allCats as $c): ?><option value="<?= $c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><input type="month" name="month" class="form-control" value="<?= e($month) ?>"></div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-forest flex-fill"><i class="bi bi-funnel"></i> Filter</button>
        <a href="expenses.php" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<div class="sheet">
    <div class="table-responsive">
        <table class="table ledger align-middle">
            <thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Paid with</th><th class="text-end">Amount</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4">No expenses match.</td></tr><?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="mono small text-nowrap"><?= date('M d, Y', strtotime($r['expense_date'])) ?></td>
                    <td>
                        <div class="fw-semibold"><?= e($r['title']) ?></div>
                        <?php if ($r['notes']): ?><div class="small text-muted"><?= e($r['notes']) ?></div><?php endif; ?>
                    </td>
                    <td><span class="dot" style="background:<?= e($r['color']) ?>"></span><?= e($r['category']) ?></td>
                    <td><span class="chip"><?= e($r['payment_method']) ?></span></td>
                    <td class="text-end amount"><?= peso($r['amount']) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="expense_form.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-dark" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form action="expense_delete.php" method="post" class="d-inline" onsubmit="return confirm('Delete this expense?');">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <?php if ($rows): ?>
            <tfoot><tr><td colspan="4" class="text-end fw-semibold">Total (<?= count($rows) ?> entries)</td><td class="text-end amount"><?= peso($filteredTotal) ?></td><td></td></tr></tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
