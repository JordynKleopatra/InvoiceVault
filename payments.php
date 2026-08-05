<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/db.php';

$search = "";

if(isset($_GET['search'])){

    $search = trim($_GET['search']);

    $stmt = $conn->prepare("
    SELECT
    payments.*,
    invoices.invoice_number
    FROM payments
    INNER JOIN invoices
    ON payments.invoice_id = invoices.invoice_id
    WHERE
    invoices.invoice_number LIKE ?
    OR payments.payment_method LIKE ?
    OR payments.reference_number LIKE ?
    ORDER BY payments.payment_date DESC
    ");

    $like = "%".$search."%";

    $stmt->bind_param("sss",$like,$like,$like);

    $stmt->execute();

    $result = $stmt->get_result();

}else{

    $result = $conn->query("
    SELECT
    payments.*,
    invoices.invoice_number
    FROM payments
    INNER JOIN invoices
    ON payments.invoice_id = invoices.invoice_id
    ORDER BY payments.payment_date DESC
    ");

}
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Payments | InvoiceVault</title>

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="dashboard-container">

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<div class="header">

<h1>Payments</h1>

<p>Manage customer payments.</p>

</div>

<div class="table-section">

<form method="GET">

<input
type="text"
name="search"
placeholder="Search invoice or payment..."
value="<?php echo htmlspecialchars($search); ?>">

<button type="submit">Search</button>

</form>

<br>

<a href="add_payment.php">

<button type="button">

Record Payment

</button>

</a>

<table>

<tr>

<th>Invoice</th>

<th>Payment Date</th>

<th>Amount</th>

<th>Method</th>

<th>Reference</th>

<th>Actions</th>

</tr>
<?php

if($result->num_rows > 0){

    while($row = $result->fetch_assoc()){

?>

<tr>

<td><?php echo htmlspecialchars($row['invoice_number']); ?></td>

<td><?php echo date("d M Y", strtotime($row['payment_date'])); ?></td>

<td>R <?php echo number_format($row['amount_paid'],2); ?></td>

<td><?php echo htmlspecialchars($row['payment_method']); ?></td>

<td>

<?php

if(!empty($row['reference_number'])){

    echo htmlspecialchars($row['reference_number']);

}else{

    echo "-";

}

?>

</td>

<td>

<a href="edit_payment.php?id=<?php echo $row['payment_id']; ?>">

<button type="button">

Edit

</button>

</a>

<a
href="delete_payment.php?id=<?php echo $row['payment_id']; ?>"
onclick="return confirm('Are you sure you want to delete this payment?');">

<button type="button">

Delete

</button>

</a>

</td>

</tr>

<?php

    }

}else{

?>

<tr>

<td colspan="6">

No payments found.

</td>

</tr>

<?php

}

?>

</table>

</div>

</div>

</div>

</body>

</html>