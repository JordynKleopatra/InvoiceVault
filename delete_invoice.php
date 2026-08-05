<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/db.php';

if (!isset($_GET['id'])) {
    header("Location: invoices.php");
    exit();
}

$invoice_id = intval($_GET['id']);

/* Get invoice details before deleting */
$getInvoice = $conn->prepare("
SELECT invoice_number
FROM invoices
WHERE invoice_id = ?
");

$getInvoice->bind_param("i", $invoice_id);
$getInvoice->execute();

$result = $getInvoice->get_result();

if ($result->num_rows == 0) {
    header("Location: invoices.php");
    exit();
}

$invoice = $result->fetch_assoc();

/* Delete invoice */
$deleteInvoice = $conn->prepare("
DELETE FROM invoices
WHERE invoice_id = ?
");

$deleteInvoice->bind_param("i", $invoice_id);

if ($deleteInvoice->execute()) {

    /* Record audit log */

    $user_id = $_SESSION['user_id'];
    $action = "Deleted invoice " . $invoice['invoice_number'];

    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip_address = "127.0.0.1";
    }

    $audit = $conn->prepare("
    INSERT INTO audit_logs
    (user_id, action, ip_address)
    VALUES
    (?, ?, ?)
    ");

    if ($audit) {

        $audit->bind_param(
            "iss",
            $user_id,
            $action,
            $ip_address
        );

        $audit->execute();
    }
}

header("Location: invoices.php");
exit();
?>