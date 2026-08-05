<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config/db.php';

$user_id = $_SESSION['user_id'];
$message = "";
$messageType = "";

$getUser = $conn->prepare("
    SELECT
        full_name,
        username,
        email,
        role,
        account_status,
        last_login,
        created_at
    FROM users
    WHERE user_id = ?
");

$getUser->bind_param("i", $user_id);
$getUser->execute();

$result = $getUser->get_result();

if ($result->num_rows === 0) {
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

if (isset($_POST['change_password'])) {

    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    $passwordQuery = $conn->prepare("
        SELECT password
        FROM users
        WHERE user_id = ?
    ");

    $passwordQuery->bind_param("i", $user_id);
    $passwordQuery->execute();

    $passwordResult = $passwordQuery->get_result();
    $passwordData = $passwordResult->fetch_assoc();

    if (!password_verify($currentPassword, $passwordData['password'])) {

        $message = "The current password is incorrect.";
        $messageType = "error";

    } elseif (strlen($newPassword) < 8) {

        $message = "The new password must contain at least 8 characters.";
        $messageType = "error";

    } elseif (
        !preg_match('/[A-Z]/', $newPassword) ||
        !preg_match('/[a-z]/', $newPassword) ||
        !preg_match('/[0-9]/', $newPassword) ||
        !preg_match('/[^A-Za-z0-9]/', $newPassword)
    ) {

        $message = "The new password must include uppercase, lowercase, a number and a special character.";
        $messageType = "error";

    } elseif ($newPassword !== $confirmPassword) {

        $message = "The new passwords do not match.";
        $messageType = "error";

    } else {

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $updatePassword = $conn->prepare("
            UPDATE users
            SET password = ?
            WHERE user_id = ?
        ");

        $updatePassword->bind_param(
            "si",
            $newHash,
            $user_id
        );

        if ($updatePassword->execute()) {

            $action = "Changed account password";
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? "127.0.0.1";

            $audit = $conn->prepare("
                INSERT INTO audit_logs
                    (user_id, action, ip_address)
                VALUES
                    (?, ?, ?)
            ");

            $audit->bind_param(
                "iss",
                $user_id,
                $action,
                $ipAddress
            );

            $audit->execute();

            $message = "Password changed successfully.";
            $messageType = "success";

        } else {

            $message = "Unable to change the password.";
            $messageType = "error";

        }

    }

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

    <title>My Profile | InvoiceVault</title>

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
            <h1>My Profile</h1>
            <p>View your account information and update your password.</p>
        </div>

        <div class="table-section">

            <h2>Account Information</h2>

            <table>

                <tbody>

                    <tr>
                        <th>Full Name</th>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    </tr>

                    <tr>
                        <th>Username</th>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                    </tr>

                    <tr>
                        <th>Email</th>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                    </tr>

                    <tr>
                        <th>Role</th>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                    </tr>

                    <tr>
                        <th>Account Status</th>
                        <td><?php echo htmlspecialchars($user['account_status']); ?></td>
                    </tr>

                    <tr>
                        <th>Last Login</th>
                        <td>
                            <?php
                            echo !empty($user['last_login'])
                                ? date("d M Y H:i", strtotime($user['last_login']))
                                : "No login recorded";
                            ?>
                        </td>
                    </tr>

                    <tr>
                        <th>Account Created</th>
                        <td>
                            <?php echo date(
                                "d M Y",
                                strtotime($user['created_at'])
                            ); ?>
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

        <div class="table-section">

            <h2>Change Password</h2>

            <?php if ($message !== "") { ?>

                <p
                    style="
                        margin:15px 0;
                        font-weight:bold;
                        color:<?php echo $messageType === 'success' ? '#198754' : '#dc3545'; ?>;
                    "
                >
                    <?php echo htmlspecialchars($message); ?>
                </p>

            <?php } ?>

            <form method="POST">

                <label>Current Password</label>

                <input
                    type="password"
                    name="current_password"
                    required
                >

                <label>New Password</label>

                <input
                    type="password"
                    name="new_password"
                    required
                >

                <label>Confirm New Password</label>

                <input
                    type="password"
                    name="confirm_password"
                    required
                >

                <button
                    type="submit"
                    name="change_password"
                >
                    Change Password
                </button>

            </form>

        </div>

    </div>

</div>

</body>

</html>