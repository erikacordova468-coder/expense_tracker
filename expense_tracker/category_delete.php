<?php
// DELETE a category (POST only). Blocked while expenses still use it.
require 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: categories.php'); exit; }

$id = (int)($_POST['id'] ?? 0);
$chk = $conn->prepare("SELECT COUNT(*) c FROM expenses WHERE category_id = ?");
$chk->bind_param('i', $id);
$chk->execute();
if ((int)$chk->get_result()->fetch_assoc()['c'] > 0) {
    header('Location: categories.php?err=' . urlencode('Cannot delete: expenses still use this category. Edit or delete them first.'));
    exit;
}

$stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

$msg = $stmt->affected_rows > 0 ? 'Category deleted.' : 'Category not found.';
header('Location: categories.php?msg=' . urlencode($msg));
exit;
