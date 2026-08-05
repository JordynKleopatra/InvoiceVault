<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/db.php';

$message = "";

if (isset($_POST['save'])) {

    $account_number = trim($_POST['account_number']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $postal_code = trim($_POST['postal_code']);
    $account_status = $_POST['account_status'];

    $check = $conn->prepare("SELECT customer_id FROM customers WHERE account_number = ?");
    $check->bind_param("s", $account_number);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {

        $message = "Account number already exists.";

    } else {

        $stmt = $conn->prepare("INSERT INTO customers
        (account_number, first_name, last_name, email, phone, address, city, province, postal_code, account_status)
        VALUES (?,?,?,?,?,?,?,?,?,?)");

        $stmt->bind_param(
            "ssssssssss",
            $account_number,
            $first_name,
            $last_name,
            $email,
            $phone,
            $address,
            $city,
            $province,
            $postal_code,
            $account_status
        );

        if ($stmt->execute()) {

            $user_id = $_SESSION['user_id'];
            $action = "Added customer: " . $account_number;
            $ip = $_SERVER['REMOTE_ADDR'];

            $log = $conn->prepare("INSERT INTO audit_logs (user_id, action, ip_address) VALUES (?,?,?)");
            $log->bind_param("iss", $user_id, $action, $ip);
            $log->execute();

            header("Location: customers.php");
            exit();

        } else {

            $message = "Unable to save customer.";

        }

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Customer | InvoiceVault</title>

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="dashboard-container">

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<div class="header">

<h1>Add Customer</h1>

<p>Create a new customer account.</p>

</div>

<div class="table-section">

<?php
if($message != ""){
    echo "<p style='color:red;'>".$message."</p>";
}
?>

<form method="POST">

<input
type="text"
name="account_number"
placeholder="Account Number"
required>

<input
type="text"
name="first_name"
placeholder="First Name"
required>

<input
type="text"
name="last_name"
placeholder="Last Name"
required>

<input
type="email"
name="email"
placeholder="Email Address">

<input
type="text"
name="phone"
placeholder="Phone Number">

<input
type="text"
name="address"
placeholder="Street Address">

<input
type="text"
name="city"
placeholder="City">

<input
type="text"
name="province"
placeholder="Province">

<input
type="text"
name="postal_code"
placeholder="Postal Code">

<select name="account_status" required>

<option value="Active">Active</option>

<option value="Inactive">Inactive</option>

</select>

<br><br>

<button type="submit" name="save">Save Customer</button>

<a href="customers.php">
<button type="button">Cancel</button>
</a>

</form>

</div>

</div>

</div>

</body>

</html>