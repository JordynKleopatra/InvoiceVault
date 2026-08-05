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
    $like = "%" . $search . "%";

    $stmt = $conn->prepare("
        SELECT
            audit_logs.log_id,
            audit_logs.action,
            audit_logs.ip_address,
            audit_logs.log_time,
            users.username,
            users.full_name
        FROM audit_logs
        INNER JOIN users
            ON audit_logs.user_id = users.user_id
        WHERE
            audit_logs.action LIKE ?
            OR users.username LIKE ?
            OR users.full_name LIKE ?
            OR audit_logs.ip_address LIKE ?
        ORDER BY audit_logs.log_time DESC
    ");

    $stmt->bind_param(
        "ssss",
        $like,
        $like,
        $like,
        $like
    );

    $stmt->execute();
    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT
            audit_logs.log_id,
            audit_logs.action,
            audit_logs.ip_address,
            audit_logs.log_time,
            users.username,
            users.full_name
        FROM audit_logs
        INNER JOIN users
            ON audit_logs.user_id = users.user_id
        ORDER BY audit_logs.log_time DESC
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

    <title>Audit Logs | InvoiceVault</title>

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
            <h1>Audit Logs</h1>
            <p>Review important user and system activity.</p>
        </div>

        <div class="table-section">

            <form method="GET">

                <input
                    type="text"
                    name="search"
                    placeholder="Search by user, action or IP address..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >

                <button
                    type="submit"
                    class="btn-warning"
                >
                    Search
                </button>

            </form>

            <table>

                <thead>

                    <tr>
                        <th>Log ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>IP Address</th>
                        <th>Date and Time</th>
                    </tr>

                </thead>

                <tbody>

                <?php if ($result && $result->num_rows > 0) { ?>

                    <?php while ($row = $result->fetch_assoc()) { ?>

                        <tr>

                            <td>
                                <?php echo $row['log_id']; ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row['full_name'] . " (" . $row['username'] . ")"
                                );
                                ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['action']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['ip_address']); ?>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "d M Y H:i",
                                    strtotime($row['log_time'])
                                );
                                ?>
                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="5">
                            No audit activity found.
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