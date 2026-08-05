<?php
session_start();
include 'config/db.php';

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Update last login
            $update = $conn->prepare("UPDATE users SET last_login = NOW(), failed_attempts = 0 WHERE user_id = ?");
            $update->bind_param("i", $user['user_id']);
            $update->execute();

            // Add to audit log
            $ip = $_SERVER['REMOTE_ADDR'];

            $log = $conn->prepare("INSERT INTO audit_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
            $action = "User logged in";
            $log->bind_param("iss", $user['user_id'], $action, $ip);
            $log->execute();

            header("Location: dashboard.php");
            exit();

        } else {

            // Increase failed attempts
            $failed = $conn->prepare("UPDATE users SET failed_attempts = failed_attempts + 1 WHERE user_id = ?");
            $failed->bind_param("i", $user['user_id']);
            $failed->execute();

            $error = "Incorrect password.";

        }

    } else {

        $error = "User not found.";

    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvoiceVault | Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-container">

    <h2>InvoiceVault</h2>
    <p>Secure Billing & Customer Management System</p>

    <?php
    if (isset($error)) {
        echo "<p style='color:red;'>$error</p>";
    }
    ?>

    <form method="POST">

        <input
            type="text"
            name="username"
            placeholder="Username"
            required>

        <input
            type="password"
            name="password"
            placeholder="Password"
            required>

        <button type="submit" name="login">Login</button>

    </form>

</div>

</body>
</html>