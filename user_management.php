<?php
session_start();
include 'db_connect.php';

$error_message = "";
$success_message = "";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_cashier'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $cashier_name = $_POST['cashier_name'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    if (empty($username) || empty($password) || empty($confirm_password) || empty($cashier_name) || empty($contact_number) || empty($address)) {
        $error_message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM cashier WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Username already taken.";
        } else {
            $role = 'cashier';

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO cashier (username, password, role, cashier_name, cashier_number, cashier_address) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssss", $username, $hashed_password, $role, $cashier_name, $contact_number, $address);

            if ($insert_stmt->execute()) {
                $user_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $user_stmt->bind_param("sss", $username, $hashed_password, $role);
                $user_stmt->execute();
                $success_message = "Cashier account created successfully.";
            } else {
                $error_message = "Error creating cashier: " . $insert_stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="inventory1.css">
    <style>
        #toast {
            visibility: hidden;
            min-width: 250px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 5px;
            padding: 16px;
            position: fixed;
            left: 50%;
            bottom: 30px;
            transform: translateX(-50%);
            font-size: 16px;
            z-index: 1000;
        }
        #toast.show {
            visibility: visible;
            animation: fadein 0.5s, fadeout 0.5s 3s;
        }
        @keyframes fadein {
            from { bottom: 0; opacity: 0; }
            to { bottom: 30px; opacity: 1; }
        }
        @keyframes fadeout {
            from { bottom: 30px; opacity: 1; }
            to { bottom: 0; opacity: 0; }
        }
        .error-message, .success-message {
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-top: 8px;
            font-size: 14px;
        }
        .error-message {
            background: #dc3545;
        }
        .success-message {
            background: #28a745;
        }
    </style>
</head>

<body>
    <div class="inventory-container">
        <div class="header">
            <div class="welcome">User Management</div>
            <div class="action-buttons">
                <form method="post" action="owner_dashboard.php">
                    <button type="submit" class="action-button">Back</button>
                </form>
                <form method="post" action="logout.php">
                    <button type="submit" class="action-button">Logout</button>
                </form>
            </div>
        </div>

        <div class="section">
            <h2>Add Cashier Account</h2>
            <form method="POST" action="user_management.php">
                <input type="text" name="username" placeholder="Username" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
                
                <input type="text" name="cashier_name" placeholder="Cashier Name" required><br>
                <input type="text" name="contact_number" placeholder="Contact Number" required><br>
                <textarea name="address" placeholder="Address" required></textarea><br>
                
                <button type="submit" name="add_cashier" class="action-button">Create Account</button>

                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
                <?php elseif (!empty($success_message)): ?>
                    <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
                <?php endif; ?>
            </form>
        </div>

        <div class="section">
            <h2>Existing Cashiers</h2>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Cashier Name</th>
                            <th>Contact Number</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users = $conn->query("SELECT username, cashier_name, cashier_number, cashier_address FROM cashier");
                        while ($user = $users->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['cashier_name']) ?></td>
                                <td><?= htmlspecialchars($user['cashier_number']) ?></td>
                                <td><?= htmlspecialchars($user['cashier_address']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div id="toast"></div>

    <script>
        function showToast(message) {
            var toast = document.getElementById("toast");
            toast.innerText = message;
            toast.className = "show";
            setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3500);
        }

        <?php if (!empty($success_message)): ?>
            showToast("<?= htmlspecialchars($success_message) ?>");
        <?php endif; ?>
    </script>
</body>
</html>
