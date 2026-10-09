<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <?php
    session_start();
    $error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
    unset($_SESSION['error_message']);
    ?>
    <div class="login-container">
        <h1>Jovs Billiards and Chill</h1>
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