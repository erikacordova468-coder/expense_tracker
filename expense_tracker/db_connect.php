<?php
/**
 * Pocket Ledger: Expense Tracker
 * Author: Christy Ramos
 *
 * Database connection (MySQLi).
 * LOCAL (XAMPP): host 127.0.0.1, port 3307 (change to 3306 if your XAMPP uses the default).
 * INFINITYFREE: use the host, user, password and DB name from the control panel, port 3306.
 */
$DB_HOST = '127.0.0.1';
$DB_PORT = 3307;
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'expense_tracker';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($conn->connect_error) {
    die('Database connection failed: ' . htmlspecialchars($conn->connect_error));
}
$conn->set_charset('utf8mb4');

/** Escape output safely in HTML. */
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Format a peso amount. */
function peso($n) {
    return '&#8369;' . number_format((float)$n, 2);
}

$PAYMENT_METHODS = ['Cash', 'GCash', 'Maya', 'Card', 'Bank Transfer'];
