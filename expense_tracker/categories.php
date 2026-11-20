<?php
// SELECT: list categories with usage counts
require 'db_connect.php';
$pageTitle = 'Categories';
$active = 'categories';

$cats = $conn->query("SELECT c.*, COUNT(e.id) uses, COALESCE(SUM(e.amount),0) spent
                      FROM categories c LEFT JOIN expenses e ON e.category_id = c.id
                      GROUP BY c.id ORDER BY c.name")->fetch_all(MYSQLI_ASSOC);
include 'includes/header.php';
?>
<div class="page-head">
    <div><div class="kicker">Where the money goes</div><h1>Categories</h1></div>
    <a href="category_form.php" class="btn btn-stamp"><i class="bi bi-plus-lg"></i> New category</a>
</div>
<div class="sheet">
    <div class="table-responsive">
        <table class="table ledger align-middle">
            <thead><tr><th>Category</th><th class="text-end">Monthly budget</th><th class="text-end">Entries</th><th class="text-end">All-time spent</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php if (!$cats): ?><tr><td colspan="5" class="text-center text-muted py-4">No categories yet.</td></tr><?php endif; ?>
            <?php foreach ($cats as $c): ?>
                <tr>
                    <td class="fw-semibold"><span class="dot" style="background:<?= e($c['color']) ?>"></span><?= e($c['name']) ?></td>
                    <td class="text-end amount"><?= $c['monthly_budget'] > 0 ? peso($c['monthly_budget']) : '<span class="text-muted">none</span>' ?></td>
                    <td class="text-end mono"><?= (int)$c['uses'] ?></td>
                    <td class="text-end amount"><?= peso($c['spent']) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="category_form.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-dark" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form action="category_delete.php" method="post" class="d-inline" onsubmit="return confirm('Delete this category?');">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
