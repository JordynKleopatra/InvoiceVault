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

$outstandingRevenue = $conn->query("
    SELECT IFNULL(SUM(total), 0) AS total
    FROM invoices
    WHERE status <> 'Paid'
")->fetch_assoc()['total'];

$paidInvoices = $conn->query("
    SELECT COUNT(*) AS total
    FROM invoices
    WHERE status = 'Paid'
")->fetch_assoc()['total'];

$unpaidInvoices = $conn->query("
    SELECT COUNT(*) AS total
    FROM invoices
    WHERE status = 'Unpaid'
")->fetch_assoc()['total'];

$recentActivity = $conn->query("
    SELECT audit_logs.*, users.username
    FROM audit_logs
    INNER JOIN users
        ON audit_logs.user_id = users.user_id
    ORDER BY audit_logs.log_time DESC
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

    <title>InvoiceVault | Dashboard</title>

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

            <h1>Dashboard</h1>

            <p>
                Welcome back,
                <strong>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </strong>
            </p>

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
                <h3>Revenue Received</h3>
                <h1>
                    R <?php echo number_format($totalRevenue, 2); ?>
                </h1>
            </div>

            <div class="card">
                <h3>Outstanding Revenue</h3>
                <h1>
                    R <?php echo number_format($outstandingRevenue, 2); ?>
                </h1>
            </div>

            <div class="card">
                <h3>Paid Invoices</h3>
                <h1><?php echo $paidInvoices; ?></h1>
            </div>

            <div class="card">
                <h3>Unpaid Invoices</h3>
                <h1><?php echo $unpaidInvoices; ?></h1>
            </div>

        </div>

        <div class="table-section">

            <h2>Recent Security Activity</h2>

            <table>

                <thead>

                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>IP Address</th>
                        <th>Date and Time</th>
                    </tr>

                </thead>

                <tbody>

                <?php if ($recentActivity->num_rows > 0) { ?>

                    <?php while ($row = $recentActivity->fetch_assoc()) { ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($row['username']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['action']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['ip_address']); ?>
                            </td>

                            <td>
                                <?php echo date(
                                    "d M Y H:i",
                                    strtotime($row['log_time'])
                                ); ?>
                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="4">
                            No security activity recorded.
                        </td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>

</html>