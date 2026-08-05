<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/db.php';

$totalCustomers = $conn->query("
    SELECT COUNT(*) AS total
    FROM customers
")->fetch_assoc()['total'];

$totalInvoices = $conn->query("
    SELECT COUNT(*) AS total
    FROM invoices
")->fetch_assoc()['total'];

$totalRevenue = $conn->query("
    SELECT IFNULL(SUM(amount_paid), 0) AS total
    FROM payments
")->fetch_assoc()['total'];

$totalInvoiced = $conn->query("
    SELECT IFNULL(SUM(total), 0) AS total
    FROM invoices
")->fetch_assoc()['total'];

$outstandingRevenue = $totalInvoiced - $totalRevenue;

$paidInvoices = $conn->query("
    SELECT COUNT(*) AS total
    FROM invoices
    WHERE status = 'Paid'
")->fetch_assoc()['total'];

$partiallyPaidInvoices = $conn->query("
    SELECT COUNT(*) AS total
    FROM invoices
    WHERE status = 'Partially Paid'
")->fetch_assoc()['total'];

$unpaidInvoices = $conn->query("
    SELECT COUNT(*) AS total
    FROM invoices
    WHERE status = 'Unpaid'
")->fetch_assoc()['total'];

$recentPayments = $conn->query("
    SELECT
        payments.payment_date,
        payments.amount_paid,
        payments.payment_method,
        payments.reference_number,
        invoices.invoice_number
    FROM payments
    INNER JOIN invoices
        ON payments.invoice_id = invoices.invoice_id
    ORDER BY payments.payment_date DESC, payments.payment_id DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reports | InvoiceVault</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >
</head>

<body>

<div class="dashboard-container">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">

        <div class="header">
            <h1>Reports</h1>
            <p>View billing and payment summaries.</p>
        </div>

        <div class="cards">

            <div class="card">
                <h3>Total Customers</h3>
                <h1><?php echo $totalCustomers; ?></h1>
            </div>

            <div class="card">
                <h3>Total Invoices</h3>
                <h1><?php echo $totalInvoices; ?></h1>
            </div>

            <div class="card">
                <h3>Total Invoiced</h3>
                <h1>R <?php echo number_format($totalInvoiced, 2); ?></h1>
            </div>

            <div class="card">
                <h3>Revenue Received</h3>
                <h1>R <?php echo number_format($totalRevenue, 2); ?></h1>
            </div>

            <div class="card">
                <h3>Outstanding Revenue</h3>
                <h1>R <?php echo number_format($outstandingRevenue, 2); ?></h1>
            </div>

            <div class="card">
                <h3>Paid Invoices</h3>
                <h1><?php echo $paidInvoices; ?></h1>
            </div>

            <div class="card">
                <h3>Partially Paid</h3>
                <h1><?php echo $partiallyPaidInvoices; ?></h1>
            </div>

            <div class="card">
                <h3>Unpaid Invoices</h3>
                <h1><?php echo $unpaidInvoices; ?></h1>
            </div>

        </div>

        <div class="table-section">

            <h2>Recent Payments</h2>

            <table>

                <thead>

                    <tr>
                        <th>Invoice</th>
                        <th>Payment Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                    </tr>

                </thead>

                <tbody>

                <?php if ($recentPayments->num_rows > 0) { ?>

                    <?php while ($row = $recentPayments->fetch_assoc()) { ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($row['invoice_number']); ?>
                            </td>

                            <td>
                                <?php echo date(
                                    "d M Y",
                                    strtotime($row['payment_date'])
                                ); ?>
                            </td>

                            <td>
                                R <?php echo number_format($row['amount_paid'], 2); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['payment_method']); ?>
                            </td>

                            <td>
                                <?php
                                echo !empty($row['reference_number'])
                                    ? htmlspecialchars($row['reference_number'])
                                    : "-";
                                ?>
                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="5">No payments recorded.</td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>

</html>