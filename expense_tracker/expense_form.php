<?php
// INSERT (no id) and UPDATE (?id=) for expenses
require 'db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;
$errors = [];
$exp = ['title' => '', 'category_id' => '', 'amount' => '', 'expense_date' => date('Y-m-d'), 'payment_method' => 'Cash', 'notes' => ''];

if ($editing) {
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    if (!$found) { header('Location: expenses.php?err=' . urlencode('Expense not found.')); exit; }
    $exp = $found;
}
$cats = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exp['title']          = trim($_POST['title'] ?? '');
    $exp['category_id']    = (int)($_POST['category_id'] ?? 0);
    $exp['amount']         = $_POST['amount'] ?? '';
    $exp['expense_date']   = $_POST['expense_date'] ?? '';
    $exp['payment_method'] = $_POST['payment_method'] ?? '';
    $exp['notes']          = trim($_POST['notes'] ?? '');

    if ($exp['title'] === '') $errors[] = 'Description is required.';
    if (!is_numeric($exp['amount']) || $exp['amount'] <= 0) $errors[] = 'Amount must be greater than zero.';
    $d = DateTime::createFromFormat('Y-m-d', $exp['expense_date']);
    if (!$d || $d->format('Y-m-d') !== $exp['expense_date']) $errors[] = 'Enter a valid date.';
    if (!in_array($exp['payment_method'], $PAYMENT_METHODS, true)) $errors[] = 'Choose a payment method.';
    $c = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    $c->bind_param('i', $exp['category_id']);
    $c->execute();
    if (!$c->get_result()->fetch_assoc()) $errors[] = 'Choose a category.';

    if (!$errors) {
        $amount = round((float)$exp['amount'], 2);
        $notes = $exp['notes'] === '' ? null : $exp['notes'];
        if ($editing) {
            $stmt = $conn->prepare("UPDATE expenses SET title=?, category_id=?, amount=?, expense_date=?, payment_method=?, notes=? WHERE id=?");
            $stmt->bind_param('sidsssi', $exp['title'], $exp['category_id'], $amount, $exp['expense_date'], $exp['payment_method'], $notes, $id);
            $msg = 'Expense updated.';
        } else {
            $stmt = $conn->prepare("INSERT INTO expenses (title, category_id, amount, expense_date, payment_method, notes) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('sidsss', $exp['title'], $exp['category_id'], $amount, $exp['expense_date'], $exp['payment_method'], $notes);
            $msg = 'Expense logged.';
        }
        if ($stmt->execute()) { header('Location: expenses.php?msg=' . urlencode($msg)); exit; }
        $errors[] = 'Database error: ' . $stmt->error;
    }
}

$pageTitle = $editing ? 'Edit expense' : 'Log expense';
$active = 'expenses';
include 'includes/header.php';
?>
<div class="page-head"><div><div class="kicker"><?= $editing ? 'Correct an entry' : 'New entry' ?></div><h1><?= $editing ? 'Edit expense' : 'Log expense' ?></h1></div></div>
<div class="row"><div class="col-xl-8">
<div class="sheet"><div class="p-4">
    <?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Description</label>
            <input type="text" name="title" class="form-control" maxlength="120" required value="<?= e($exp['title']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Amount (&#8369;)</label>
            <input type="number" name="amount" step="0.01" min="0.01" class="form-control mono" required value="<?= e($exp['amount']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">Choose...</option>
                <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= (int)$exp['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" name="expense_date" class="form-control" required value="<?= e($exp['expense_date']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Paid with</label>
            <select name="payment_method" class="form-select">
                <?php foreach ($PAYMENT_METHODS as $m): ?><option <?= $exp['payment_method'] === $m ? 'selected' : '' ?>><?= e($m) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Notes <span class="text-muted text-lowercase">(optional)</span></label>
            <input type="text" name="notes" class="form-control" maxlength="255" value="<?= e($exp['notes']) ?>">
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-stamp"><i class="bi bi-check-lg"></i> <?= $editing ? 'Save changes' : 'Save entry' ?></button>
            <a href="expenses.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div></div>
</div></div>
<?php include 'includes/footer.php'; ?>
