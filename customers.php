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
        SELECT * FROM customers
        WHERE account_number LIKE ?
        OR first_name LIKE ?
        OR last_name LIKE ?
        OR email LIKE ?
        ORDER BY customer_id DESC
    ");

    $like = "%".$search."%";

    $stmt->bind_param("ssss",$like,$like,$like,$like);
    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT *
        FROM customers
        ORDER BY customer_id DESC
    ");

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Customers | InvoiceVault</title>

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="dashboard-container">

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<div class="header">

<h1>Customers</h1>

<p>Manage customer information.</p>

</div>

<div class="table-section">

<form method="GET">

<input
type="text"
name="search"
placeholder="Search by account number, name or email"
value="<?php echo htmlspecialchars($search); ?>">

<button type="submit">Search</button>

</form>

<br>

<a href="add_customer.php">
<button type="button">Add Customer</button>
</a>

<table>

<tr>

<th>Account No.</th>

<th>Name</th>

<th>Email</th>

<th>Phone</th>

<th>Status</th>

<th>Actions</th>

</tr>
<?php

if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

?>

<tr>

<td><?php echo htmlspecialchars($row['account_number']); ?></td>

<td><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>

<td><?php echo htmlspecialchars($row['email']); ?></td>

<td><?php echo htmlspecialchars($row['phone']); ?></td>

<td><?php echo htmlspecialchars($row['account_status']); ?></td>

<td>

<a href="edit_customer.php?id=<?php echo $row['customer_id']; ?>">
<button type="button">Edit</button>
</a>

<a href="delete_customer.php?id=<?php echo $row['customer_id']; ?>"
onclick="return confirm('Are you sure you want to delete this customer?');">
<button type="button">Delete</button>
</a>

</td>

</tr>

<?php

    }

} else {

?>

<tr>

<td colspan="6">No customers found.</td>

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