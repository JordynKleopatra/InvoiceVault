<?php
$currentPage = basename($_SERVER['PHP_SELF']);

function activePage($pageName, $currentPage)
{
    return $pageName === $currentPage ? 'active-link' : '';
}
?>

<div class="sidebar">

    <div class="sidebar-brand">
        <h2>InvoiceVault</h2>
        <p>Secure Billing System</p>
    </div>

    <ul>

        <li>
            <a
                class="<?php echo activePage('dashboard.php', $currentPage); ?>"
                href="dashboard.php"
            >
                Dashboard
            </a>
        </li>

        <li>
            <a
                class="<?php echo activePage('customers.php', $currentPage); ?>"
                href="customers.php"
            >
                Customers
            </a>
        </li>

        <li>
            <a
                class="<?php echo activePage('invoices.php', $currentPage); ?>"
                href="invoices.php"
            >
                Invoices
            </a>
        </li>

        <li>
            <a
                class="<?php echo activePage('payments.php', $currentPage); ?>"
                href="payments.php"
            >
                Payments
            </a>
        </li>

        <li>
            <a
                class="<?php echo activePage('reports.php', $currentPage); ?>"
                href="reports.php"
            >
                Reports
            </a>
        </li>

        <li>
            <a
                class="<?php echo activePage('audit_logs.php', $currentPage); ?>"
                href="audit_logs.php"
            >
                Audit Logs
            </a>
        </li>

        <li>
            <a
                class="<?php echo activePage('profile.php', $currentPage); ?>"
                href="profile.php"
            >
                My Profile
            </a>
        </li>

        <li class="logout-item">
            <a href="logout.php">
                Logout
            </a>
        </li>

    </ul>

</div>