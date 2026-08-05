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

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: customers.php");
    exit();
}

$customer = $result->fetch_assoc();

$message = "";

if (isset($_POST['update'])) {

    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $postal_code = trim($_POST['postal_code']);
    $account_status = $_POST['account_status'];

    $update = $conn->prepare("
        UPDATE customers
        SET
        first_name=?,
        last_name=?,
        email=?,
        phone=?,
        address=?,
        city=?,
        province=?,
        postal_code=?,
        account_status=?
        WHERE customer_id=?
    ");

    $update->bind_param(
        "sssssssssi",
        $first_name,
        $last_name,
        $email,
        $phone,
        $address,
        $city,
        $province,
        $postal_code,
        $account_status,
        $id
    );

    if($update->execute()){

        $user = $_SESSION['user_id'];
        $action = "Updated customer ".$customer['account_number'];
        $ip = $_SERVER['REMOTE_ADDR'];

        $log = $conn->prepare("INSERT INTO audit_logs(user_id,action,ip_address) VALUES(?,?,?)");
        $log->bind_param("iss",$user,$action,$ip);
        $log->execute();

        header("Location: customers.php");
        exit();

    }else{

        $message="Unable to update customer.";

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Customer | InvoiceVault</title>

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="dashboard-container">

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<div class="header">

<h1>Edit Customer</h1>

</div>

<div class="table-section">

<?php
if($message!=""){
    echo "<p style='color:red;'>".$message."</p>";
}
?>

<form method="POST">

<input
type="text"
value="<?php echo htmlspecialchars($customer['account_number']); ?>"
disabled>

<input
type="text"
name="first_name"
value="<?php echo htmlspecialchars($customer['first_name']); ?>"
required>

<input
type="text"
name="last_name"
value="<?php echo htmlspecialchars($customer['last_name']); ?>"
required>

<input
type="email"
name="email"
value="<?php echo htmlspecialchars($customer['email']); ?>">

<input
type="text"
name="phone"
value="<?php echo htmlspecialchars($customer['phone']); ?>">

<input
type="text"
name="address"
value="<?php echo htmlspecialchars($customer['address']); ?>">

<input
type="text"
name="city"
value="<?php echo htmlspecialchars($customer['city']); ?>">

<input
type="text"
name="province"
value="<?php echo htmlspecialchars($customer['province']); ?>">

<input
type="text"
name="postal_code"
value="<?php echo htmlspecialchars($customer['postal_code']); ?>">

<select name="account_status">

<option value="Active" <?php if($customer['account_status']=="Active") echo "selected"; ?>>Active</option>

<option value="Inactive" <?php if($customer['account_status']=="Inactive") echo "selected"; ?>>Inactive</option>

</select>

<button type="submit" name="update">Update Customer</button>

<a href="customers.php">
<button type="button">Cancel</button>
</a>

</form>

</div>

</div>

</div>

</body>

</html>