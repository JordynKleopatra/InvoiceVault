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

$id = intval($_GET['id']);

$customers = $conn->query("
SELECT customer_id,
account_number,
first_name,
last_name
FROM customers
WHERE account_status='Active'
ORDER BY first_name
");

$stmt = $conn->prepare("
SELECT *
FROM invoices
WHERE invoice_id=?
");

$stmt->bind_param("i",$id);
$stmt->execute();

$result=$stmt->get_result();

if($result->num_rows==0){
    header("Location: invoices.php");
    exit();
}

$invoice=$result->fetch_assoc();

$message="";

if(isset($_POST['update'])){

    $customer_id=$_POST['customer_id'];
    $invoice_date=$_POST['invoice_date'];
    $due_date=$_POST['due_date'];
    $subtotal=$_POST['subtotal'];

    $vat=$subtotal*0.15;
    $total=$subtotal+$vat;

    $update=$conn->prepare("
    UPDATE invoices
    SET
    customer_id=?,
    invoice_date=?,
    due_date=?,
    subtotal=?,
    vat=?,
    total=?
    WHERE invoice_id=?
    ");

    $update->bind_param(
    "issdddi",
    $customer_id,
    $invoice_date,
    $due_date,
    $subtotal,
    $vat,
    $total,
    $id
    );

    if($update->execute()){

        $user=$_SESSION['user_id'];
        $action="Updated invoice ".$invoice['invoice_number'];
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

        $message="Unable to update invoice.";

    }

}
?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Edit Invoice</title>

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="dashboard-container">

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<div class="header">

<h1>Edit Invoice</h1>

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

<?php while($customer = $customers->fetch_assoc()){ ?>

<option
value="<?php echo $customer['customer_id']; ?>"
<?php if($customer['customer_id']==$invoice['customer_id']) echo "selected"; ?>>

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
value="<?php echo $invoice['invoice_date']; ?>"
required>

<label>Due Date</label>

<input
type="date"
name="due_date"
value="<?php echo $invoice['due_date']; ?>"
required>

<label>Subtotal (R)</label>

<input
type="number"
name="subtotal"
step="0.01"
min="0"
value="<?php echo $invoice['subtotal']; ?>"
required>

<button type="submit" name="update">
Update Invoice
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