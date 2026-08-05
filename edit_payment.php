<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/db.php';

if (!isset($_GET['id'])) {
    header("Location: payments.php");
    exit();
}

$payment_id = intval($_GET['id']);

$message = "";

$payment = $conn->prepare("
SELECT *
FROM payments
WHERE payment_id=?
");

$payment->bind_param("i",$payment_id);
$payment->execute();

$result = $payment->get_result();

if($result->num_rows==0){
    header("Location: payments.php");
    exit();
}

$data = $result->fetch_assoc();

if(isset($_POST['update'])){

    $payment_date = $_POST['payment_date'];
    $amount_paid = $_POST['amount_paid'];
    $payment_method = $_POST['payment_method'];
    $reference_number = trim($_POST['reference_number']);

    $update = $conn->prepare("
    UPDATE payments
    SET
    payment_date=?,
    amount_paid=?,
    payment_method=?,
    reference_number=?
    WHERE payment_id=?
    ");

    $update->bind_param(
        "sdssi",
        $payment_date,
        $amount_paid,
        $payment_method,
        $reference_number,
        $payment_id
    );

    if($update->execute()){

        $user = $_SESSION['user_id'];
        $action = "Updated payment ID ".$payment_id;
        $ip = $_SERVER['REMOTE_ADDR'];

        $log = $conn->prepare("
        INSERT INTO audit_logs(user_id,action,ip_address)
        VALUES(?,?,?)
        ");

        $log->bind_param("iss",$user,$action,$ip);
        $log->execute();

        header("Location: payments.php");
        exit();

    }else{

        $message = "Unable to update payment.";

    }

}
?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Edit Payment</title>

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="dashboard-container">

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<div class="header">

<h1>Edit Payment</h1>

</div>

<div class="table-section">

<?php

if($message!=""){

echo "<p style='color:red;'>".$message."</p>";

}

?>

<form method="POST">

<label>Payment Date</label>

<input
type="date"
name="payment_date"
value="<?php echo $data['payment_date']; ?>"
required>

<label>Amount Paid (R)</label>

<input
type="number"
name="amount_paid"
step="0.01"
value="<?php echo $data['amount_paid']; ?>"
required>

<label>Payment Method</label>

<select name="payment_method">

<option value="Cash" <?php if($data['payment_method']=="Cash") echo "selected"; ?>>Cash</option>

<option value="Card" <?php if($data['payment_method']=="Card") echo "selected"; ?>>Card</option>

<option value="EFT" <?php if($data['payment_method']=="EFT") echo "selected"; ?>>EFT</option>

</select>

<label>Reference Number</label>

<input
type="text"
name="reference_number"
value="<?php echo htmlspecialchars($data['reference_number']); ?>">

<button
type="submit"
name="update">

Update Payment

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