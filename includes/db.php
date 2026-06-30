<?php
/**
 * db.php — shared MySQL connection for the Mealtime site.
 *
 * XAMPP/WAMP defaults: host=localhost, user=root, password="" (empty).
 * Change these if your setup is different.
 *
 * Every other PHP page does: require_once 'includes/db.php';
 * and then uses the $conn variable (a mysqli connection).
 */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'mealtime_shop';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

/**
 * Start a session on every page that includes this file,
 * so we can track logged-in customers via $_SESSION['customer_id'].
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
