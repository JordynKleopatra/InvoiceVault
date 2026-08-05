<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/db.php';

if (!isset($_GET['id'])) {
    header("Location: payments.php");
    exit();
}

$payment_id = intval($_GET['id']);

/* Get payment information before deleting */

$get = $conn->prepare("
SELECT
payments.invoice_id,
invoices.invoice_number
FROM payments
INNER JOIN invoices
ON payments.invoice_id = invoices.invoice_id
WHERE payment_id = ?
");

$get->bind_param("i", $payment_id);
$get->execute();

$result = $get->get_result();

if ($result->num_rows == 0) {
    header("Location: payments.php");
    exit();
}

$payment = $result->fetch_assoc();

$invoice_id = $payment['invoice_id'];
$invoice_number = $payment['invoice_number'];

/* Delete payment */

$delete = $conn->prepare("
DELETE FROM payments
WHERE payment_id = ?
");

$delete->bind_param("i", $payment_id);

if ($delete->execute()) {

    /* Recalculate invoice status */

    $paid = $conn->query("
    SELECT IFNULL(SUM(amount_paid),0) AS total_paid
    FROM payments
    WHERE invoice_id = $invoice_id
    ")->fetch_assoc();

    $total_paid = $paid['total_paid'];

    $invoice = $conn->query("
    SELECT total
    FROM invoices
    WHERE invoice_id = $invoice_id
    ")->fetch_assoc();

    $invoice_total = $invoice['total'];

    if ($total_paid <= 0) {
        $status = "Unpaid";
    } elseif ($total_paid < $invoice_total) {
        $status = "Partially Paid";
    } else {
        $status = "Paid";
    }

    $update = $conn->prepare("
    UPDATE invoices
    SET status = ?
    WHERE invoice_id = ?
    ");

    $update->bind_param("si", $status, $invoice_id);
    $update->execute();

    /* Audit Log */

    $user = $_SESSION['user_id'];
    $action = "Deleted payment for " . $invoice_number;
    $ip = $_SERVER['REMOTE_ADDR'];

    $log = $conn->prepare("
    INSERT INTO audit_logs (user_id, action, ip_address)
    VALUES (?, ?, ?)
    ");

    $log->bind_param("iss", $user, $action, $ip);
    $log->execute();
}

header("Location: payments.php");
exit();
?>