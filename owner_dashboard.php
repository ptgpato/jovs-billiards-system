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
    <link rel="stylesheet" href="app.css">
</head>

<body>
    <div class="dashboard-container">
        <div class="header">
            <div>
                <div class="welcome">Owner dashboard</div>
                <p class="header-subtitle">A quick overview of your billiards business</p>
            </div>
            <form method="post" action="logout.php">
                <button type="submit" class="logout-button">Logout</button>
            </form>
        </div>

        <div class="dashboard-grid">
        <div class="section dashboard-card">
            <div class="card-icon">01</div>
            <h2>Inventory</h2>
            <div class="section-content">
                <p>Track products, stock levels, prices, and product images.</p>
                <a href="inventory.php">Open inventory</a>
            </div>
        </div>

        <div class="section dashboard-card">
            <div class="card-icon">02</div>
            <h2>Sales</h2>
            <div class="section-content">
                <p>Record customer purchases and review recent transactions.</p>
                <a href="sales.php">Open sales</a>
            </div>
        </div>

        <div class="section dashboard-card">
            <div class="card-icon">03</div>
            <h2>Team access</h2>
            <div class="section-content">
                <p>Create cashier accounts and keep staff access organized.</p>
                <a href="user_management.php">Manage users</a>
            </div>
        </div>
        </div>
    </div>
</body>

</html>