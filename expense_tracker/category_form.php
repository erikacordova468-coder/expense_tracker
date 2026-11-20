<?php
// INSERT (no id) and UPDATE (?id=) for categories
require 'db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;
$errors = [];
$cat = ['name' => '', 'color' => '#2f5d46', 'monthly_budget' => '0'];

if ($editing) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    if (!$found) { header('Location: categories.php?err=' . urlencode('Category not found.')); exit; }
    $cat = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat['name']           = trim($_POST['name'] ?? '');
    $cat['color']          = $_POST['color'] ?? '';
    $cat['monthly_budget'] = $_POST['monthly_budget'] ?? '0';

    if ($cat['name'] === '') $errors[] = 'Category name is required.';
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $cat['color'])) $errors[] = 'Pick a valid color.';
    if (!is_numeric($cat['monthly_budget']) || $cat['monthly_budget'] < 0) $errors[] = 'Budget must be zero or more.';

    if (!$errors) {
        $budget = round((float)$cat['monthly_budget'], 2);
        if ($editing) {
            $stmt = $conn->prepare("UPDATE categories SET name=?, color=?, monthly_budget=? WHERE id=?");
            $stmt->bind_param('ssdi', $cat['name'], $cat['color'], $budget, $id);
            $msg = 'Category updated.';
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, color, monthly_budget) VALUES (?,?,?)");
            $stmt->bind_param('ssd', $cat['name'], $cat['color'], $budget);
            $msg = 'Category added.';
        }
        if ($stmt->execute()) { header('Location: categories.php?msg=' . urlencode($msg)); exit; }
        $errors[] = $stmt->errno === 1062 ? 'That category name already exists.' : 'Database error: ' . $stmt->error;
    }
}

$pageTitle = $editing ? 'Edit category' : 'New category';
$active = 'categories';
include 'includes/header.php';
?>
<div class="page-head"><div><div class="kicker"><?= $editing ? 'Adjust' : 'Create' ?></div><h1><?= $editing ? 'Edit category' : 'New category' ?></h1></div></div>
<div class="row"><div class="col-xl-6">
<div class="sheet"><div class="p-4">
    <?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" class="row g-3">
        <div class="col-12">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" maxlength="60" required value="<?= e($cat['name']) ?>">
        </div>
        <div class="col-md-8">
            <label class="form-label">Monthly budget (&#8369;)</label>
            <input type="number" name="monthly_budget" step="0.01" min="0" class="form-control mono" value="<?= e($cat['monthly_budget']) ?>">
            <div class="form-text">Use 0 for no budget limit.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Color</label>
            <input type="color" name="color" class="form-control form-control-color w-100" value="<?= e($cat['color']) ?>">
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-stamp"><i class="bi bi-check-lg"></i> <?= $editing ? 'Save changes' : 'Add category' ?></button>
            <a href="categories.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div></div>
</div></div>
<?php include 'includes/footer.php'; ?>
