<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password']) || hash_equals($user['password'], $password)) {
            if (!password_verify($password, $user['password'])) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $upgrade_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upgrade_stmt->bind_param("si", $hashed_password, $user['id']);
                $upgrade_stmt->execute();
            }
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'owner') {
                header("Location: owner_dashboard.php");
                exit();
            } elseif ($user['role'] === 'cashier') {
                header("Location: sales.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Invalid password.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "User not found.";
        header("Location: login.php");
        exit();
    }
}
?>