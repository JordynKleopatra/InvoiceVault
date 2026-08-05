<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/db.php';

$message = "";

$invoices = $conn->query("
SELECT
invoice_id,
invoice_number,
total,
status
FROM invoices
WHERE status <> 'Paid'
ORDER BY invoice_date DESC
");

if(isset($_POST['save'])){

    $invoice_id = intval($_POST['invoice_id']);
    $payment_date = $_POST['payment_date'];
    $amount_paid = floatval($_POST['amount_paid']);
    $payment_method = $_POST['payment_method'];
    $reference_number = trim($_POST['reference_number']);

    /* Get invoice */

    $invoiceQuery = $conn->prepare("
    SELECT
    invoice_number,
    total
    FROM invoices
    WHERE invoice_id=?
    ");

    $invoiceQuery->bind_param("i",$invoice_id);
    $invoiceQuery->execute();

    $invoice = $invoiceQuery->get_result()->fetch_assoc();

    $invoice_total = $invoice['total'];

    /* Total already paid */

    $paidQuery = $conn->prepare("
    SELECT
    IFNULL(SUM(amount_paid),0) AS total_paid
    FROM payments
    WHERE invoice_id=?
    ");

    $paidQuery->bind_param("i",$invoice_id);
    $paidQuery->execute();

    $paid = $paidQuery->get_result()->fetch_assoc();

    $already_paid = $paid['total_paid'];

    $remaining = $invoice_total - $already_paid;

    if($amount_paid > $remaining){

        $message = "Payment amount exceeds the outstanding balance.";

    }else{

        $insert = $conn->prepare("
        INSERT INTO payments
        (
        invoice_id,
        payment_date,
        amount_paid,
        payment_method,
        reference_number
        )
        VALUES
        (?,?,?,?,?)
        ");

        $insert->bind_param(
        "isdss",
        $invoice_id,
        $payment_date,
        $amount_paid,
        $payment_method,
        $reference_number
        );

        if($insert->execute()){

            /* Recalculate payment total */

            $paidQuery = $conn->prepare("
            SELECT
            IFNULL(SUM(amount_paid),0) AS total_paid
            FROM payments
            WHERE invoice_id=?
            ");

            $paidQuery->bind_param("i",$invoice_id);
            $paidQuery->execute();

            $paid = $paidQuery->get_result()->fetch_assoc();

            $total_paid = $paid['total_paid'];

            if($total_paid >= $invoice_total){

                $status = "Paid";

            }elseif($total_paid > 0){

                $status = "Partially Paid";

            }else{

                $status = "Unpaid";

            }

            $update = $conn->prepare("
            UPDATE invoices
            SET status=?
            WHERE invoice_id=?
            ");

            $update->bind_param(
            "si",
            $status,
            $invoice_id
            );

            $update->execute();

            $user = $_SESSION['user_id'];

            $action = "Recorded payment for ".$invoice['invoice_number'];

            $ip = $_SERVER['REMOTE_ADDR'];

            $audit = $conn->prepare("
            INSERT INTO audit_logs
            (
            user_id,
            action,
            ip_address
            )
            VALUES
            (?,?,?)
            ");

            $audit->bind_param(
            "iss",
            $user,
            $action,
            $ip
            );

            $audit->execute();

            header("Location: payments.php");
            exit();

        }else{

            $message = "Unable to record payment.";

        }

    }

}
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Record Payment</title>

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="dashboard-container">

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<div class="header">

<h1>Record Payment</h1>

<p>Record a customer payment.</p>

</div>

<div class="table-section">

<?php

if($message!=""){

echo "<p style='color:red;font-weight:bold;'>".$message."</p>";

}

?>

<form method="POST">
    <label>Invoice</label>

<select name="invoice_id" required>
    <option value="">Select Invoice</option>

    <?php while ($row = $invoices->fetch_assoc()) { ?>

        <option value="<?php echo $row['invoice_id']; ?>">
            <?php
            echo htmlspecialchars(
                $row['invoice_number'] .
                " | Total: R" .
                number_format($row['total'], 2) .
                " | " .
                $row['status']
            );
            ?>
        </option>

    <?php } ?>
</select>

<label>Payment Date</label>

<input
    type="date"
    name="payment_date"
    value="<?php echo date('Y-m-d'); ?>"
    required
>

<label>Amount Paid (R)</label>

<input
    type="number"
    name="amount_paid"
    step="0.01"
    min="0.01"
    placeholder="0.00"
    required
>

<label>Payment Method</label>

<select name="payment_method" required>
    <option value="">Select Payment Method</option>
    <option value="Cash">Cash</option>
    <option value="Card">Card</option>
    <option value="EFT">EFT</option>
</select>

<label>Reference Number</label>

<input
    type="text"
    name="reference_number"
    placeholder="Optional"
>

<button type="submit" name="save">
    Save Payment
</button>

<a href="payments.php">
    <button type="button">
        Cancel
    </button>
</a>

</form>

</div>

</div>

</div>

</body>

</html>