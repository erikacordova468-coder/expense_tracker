<?php
// DELETE an expense (POST only)
require 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: expenses.php'); exit; }

$id = (int)($_POST['id'] ?? 0);
$stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

$msg = $stmt->affected_rows > 0 ? 'Expense deleted.' : 'Expense not found.';
header('Location: expenses.php?msg=' . urlencode($msg));
exit;
