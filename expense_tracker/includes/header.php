<?php
// Shared layout: sidebar + content. Set $pageTitle and $active before including.
$pageTitle = $pageTitle ?? 'Pocket Ledger';
$active = $active ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Erika Cordova">
    <title><?= e($pageTitle) ?> | Pocket Ledger by Erika Cordova</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,800&family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="shell">
    <aside class="side">
        <a href="index.php" class="brand">
            <span class="brand-mark">PL</span>
            <span><span class="brand-name">Pocket Ledger</span><span class="brand-sub">expense tracker</span></span>
        </a>
        <nav class="side-nav">
            <a href="index.php" class="<?= $active==='home'?'on':'' ?>"><i class="bi bi-journal-bookmark"></i> Overview</a>
            <a href="expenses.php" class="<?= $active==='expenses'?'on':'' ?>"><i class="bi bi-receipt-cutoff"></i> Expenses</a>
            <a href="categories.php" class="<?= $active==='categories'?'on':'' ?>"><i class="bi bi-tags"></i> Categories</a>
        </nav>
        <a href="expense_form.php" class="btn btn-stamp w-100 mt-3"><i class="bi bi-plus-lg"></i> Log expense</a>
        <div class="side-credit">Kept by <strong>Erika Cordova</strong><br>Web Systems Technologies</div>
    </aside>
    <main class="content">
<?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-ledger alert-dismissible fade show" role="alert"><?= e($_GET['msg']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($_GET['err'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><?= e($_GET['err']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
