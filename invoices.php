<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/db.php';

$search = "";

if (isset($_GET['search'])) {

    $search = trim($_GET['search']);

    $stmt = $conn->prepare("
        SELECT
            invoices.*,
            customers.first_name,
            customers.last_name
        FROM invoices
        INNER JOIN customers
            ON invoices.customer_id = customers.customer_id
        WHERE
            invoices.invoice_number LIKE ?
            OR customers.first_name LIKE ?
            OR customers.last_name LIKE ?
        ORDER BY invoices.invoice_date DESC
    ");

    $like = "%" . $search . "%";

    $stmt->bind_param(
        "sss",
        $like,
        $like,
        $like
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT
            invoices.*,
            customers.first_name,
            customers.last_name
        FROM invoices
        INNER JOIN customers
            ON invoices.customer_id = customers.customer_id
        ORDER BY invoices.invoice_date DESC
    ");

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Invoices | InvoiceVault</title>

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

            <h1>Invoices</h1>

            <p>Manage customer invoices.</p>

        </div>

        <div class="table-section">

            <form method="GET">

                <input
                    type="text"
                    name="search"
                    placeholder="Search invoice number or customer..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >

                <button
                    type="submit"
                    class="btn-warning"
                >
                    Search
                </button>

            </form>

            <br>

            <a href="add_invoice.php">

                <button
                    type="button"
                    class="btn-success"
                >
                    Create Invoice
                </button>

            </a>

            <table>

                <thead>

                    <tr>
                        <th>Invoice No.</th>
                        <th>Customer</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody>

                <?php if ($result->num_rows > 0) { ?>

                    <?php while ($row = $result->fetch_assoc()) { ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($row['invoice_number']); ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row['first_name'] . " " . $row['last_name']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($row['invoice_date'])
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($row['due_date'])
                                );
                                ?>
                            </td>

                            <td>
                                R <?php echo number_format($row['total'], 2); ?>
                            </td>

                            <td>

                                <?php if ($row['status'] === 'Paid') { ?>

                                    <span class="status-paid">
                                        Paid
                                    </span>

                                <?php } elseif ($row['status'] === 'Partially Paid') { ?>

                                    <span class="status-partial">
                                        Partially Paid
                                    </span>

                                <?php } else { ?>

                                    <span class="status-unpaid">
                                        Unpaid
                                    </span>

                                <?php } ?>

                            </td>

                            <td>

                                <div class="action-group">

                                    <a
                                        href="edit_invoice.php?id=<?php echo $row['invoice_id']; ?>"
                                    >
                                        <button
                                            type="button"
                                            class="btn-primary"
                                        >
                                            Edit
                                        </button>
                                    </a>

                                    <a
                                        href="delete_invoice.php?id=<?php echo $row['invoice_id']; ?>"
                                        onclick="return confirm('Delete this invoice?');"
                                    >
                                        <button
                                            type="button"
                                            class="btn-danger"
                                        >
                                            Delete
                                        </button>
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="7">
                            No invoices found.
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