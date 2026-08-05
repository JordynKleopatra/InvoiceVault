<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/db.php';

if (!isset($_GET['id'])) {
    header("Location: customers.php");
    exit();
}

$id = intval($_GET['id']);

/* Get customer details for audit log */

$get = $conn->prepare("SELECT account_number FROM customers WHERE customer_id = ?");
$get->bind_param("i", $id);
$get->execute();
$result = $get->get_result();

if ($result->num_rows == 0) {
    header("Location: customers.php");
    exit();
}

$customer = $result->fetch_assoc();

/* Delete customer */

$delete = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
$delete->bind_param("i", $id);

if ($delete->execute()) {

    $user = $_SESSION['user_id'];
    $action = "Deleted customer " . $customer['account_number'];
    $ip = $_SERVER['REMOTE_ADDR'];

    $log = $conn->prepare("INSERT INTO audit_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
    $log->bind_param("iss", $user, $action, $ip);
    $log->execute();
}

header("Location: customers.php");
exit();
?>