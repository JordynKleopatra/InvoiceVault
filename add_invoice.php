<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/db.php';

$message = "";

/* Load customers */

$customers = $conn->query("
SELECT customer_id,
account_number,
first_name,
last_name
FROM customers
WHERE account_status='Active'
ORDER BY first_name ASC
");

if(isset($_POST['save'])){

    $customer_id = $_POST['customer_id'];
    $invoice_date = $_POST['invoice_date'];
    $due_date = $_POST['due_date'];
    $subtotal = $_POST['subtotal'];

    $vat = $subtotal * 0.15;
    $total = $subtotal + $vat;

    $invoice_number = "INV-".date("Y")."-".str_pad(rand(1,999999),6,"0",STR_PAD_LEFT);

    $stmt = $conn->prepare("
    INSERT INTO invoices
    (
    invoice_number,
    customer_id,
    invoice_date,
    due_date,
    subtotal,
    vat,
    total
    )
    VALUES
    (?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
    "sissddd",
    $invoice_number,
    $customer_id,
    $invoice_date,
    $due_date,
    $subtotal,
    $vat,
    $total
    );

    if($stmt->execute()){

        $user=$_SESSION['user_id'];
        $action="Created invoice ".$invoice_number;
        $ip=$_SERVER['REMOTE_ADDR'];

        $log=$conn->prepare("
        INSERT INTO audit_logs(user_id,action,ip_address)
        VALUES(?,?,?)
        ");

        $log->bind_param("iss",$user,$action,$ip);
        $log->execute();

        header("Location: invoices.php");
        exit();

    }else{

        $message="Unable to create invoice.";

    }

}
?>

<!DOCTYPE html>

<html>

<head>

<title>Create Invoice</title>

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="dashboard-container">

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<div class="header">

<h1>Create Invoice</h1>

</div>

<div class="table-section">

<?php

if($message!=""){

echo "<p style='color:red;'>".$message."</p>";

}

?>

<form method="POST">
    <label>Customer</label>

<select name="customer_id" required>

<option value="">Select Customer</option>

<?php while($customer = $customers->fetch_assoc()){ ?>

<option value="<?php echo $customer['customer_id']; ?>">

<?php
echo htmlspecialchars(
$customer['account_number']." - ".
$customer['first_name']." ".
$customer['last_name']
);
?>

</option>

<?php } ?>

</select>

<label>Invoice Date</label>

<input
type="date"
name="invoice_date"
required>

<label>Due Date</label>

<input
type="date"
name="due_date"
required>

<label>Subtotal (R)</label>

<input
type="number"
name="subtotal"
step="0.01"
min="0"
placeholder="0.00"
required>

<button type="submit" name="save">
Create Invoice
</button>

<a href="invoices.php">
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