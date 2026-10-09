<?php
session_start();


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="owner_dashboard.css">
</head>

<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="welcome">Welcome, Mr. Dave</div>
            <form method="post" action="logout.php">
                <button type="submit" class="logout-button">Logout</button>
            </form>
        </div>

        <div class="section">
            <h2>Inventory Management</h2>
            <div class="section-content">
                <p>Track, edit, and manage inventory items here.</p>
                <a href="inventory.php">Go to Inventory Management</a>
            </div>
        </div>

        <div class="section">
            <h2>Sales Data</h2>
            <div class="section-content">
                <p>View daily sales reports and track recent transactions.</p>
                <a href="sales.php">Go to Sales Data</a>
            </div>
        </div>

        <div class="section">
            <h2>User Management</h2>
            <div class="section-content">
                <p>Add or manage system users.</p>
                <a href="user_management.php">Go to User Management</a>
            </div>
        </div>
    </div>
</body>

</html>