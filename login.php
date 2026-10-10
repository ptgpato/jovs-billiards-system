<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="app.css">
</head>

<body>
    <?php
    session_start();
    $error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
    unset($_SESSION['error_message']);
    ?>
    <div class="login-container">
        <p class="login-kicker">JOVS BILLIARDS &amp; CHILL</p>
        <h1>Welcome back</h1>
        <p class="login-intro">Sign in to manage your tables, inventory, and sales.</p>
        <?php if (!empty($error_message)): ?>
            <div class="error-message" style="color: red; font-weight: bold;"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form action="index.php" method="post">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="login-button">Login</button>
        </form>
    </div>
</body>

</html>