<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');
    $customer_number = trim($_POST['customer_number'] ?? '');
    $customer_type = $_POST['customer_type'] ?? '';
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $payment_method = $_POST['payment_method'] ?? '';
    $status = $_POST['status'] ?? '';

    if ($customer_name === '' || $product_id === false || $product_id === null || $quantity === false || $quantity === null || $quantity < 1) {
        $_SESSION['error_message'] = "Complete the customer, product, and quantity fields with valid values.";
        header("Location: sales.php");
        exit();
    }

    $cashier_stmt = $conn->prepare("SELECT cashierID FROM cashier WHERE username = ?");
    $cashier_stmt->bind_param("s", $_SESSION['username']);
    $cashier_stmt->execute();
    $cashier = $cashier_stmt->get_result()->fetch_assoc();
    $cashier_id = $cashier ? (int) $cashier['cashierID'] : 1;

    $conn->begin_transaction();
    $product_stmt = $conn->prepare("SELECT product_price, product_quantity FROM product WHERE productID = ? FOR UPDATE");
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product = $product_stmt->get_result()->fetch_assoc();
    if (!$product || $quantity > (int) $product['product_quantity']) {
        $_SESSION['error_message'] = "The selected product does not have enough stock.";
        $conn->rollback();
        header("Location: sales.php");
        exit();
    }

    $subtotal = (float) $product['product_price'] * $quantity;
    try {
        $customer_stmt = $conn->prepare("INSERT INTO customer (customer_name, customer_number, customer_address, customer_type) VALUES (?, ?, ?, ?)");
        $customer_stmt->bind_param("ssss", $customer_name, $customer_number, $customer_address, $customer_type);
        $customer_stmt->execute();
        $customer_id = $customer_stmt->insert_id;

        $transaction_stmt = $conn->prepare("INSERT INTO sales_transaction (cashier_ID, customerID, productID, salestrans_payment_method, salestrans_status, salestrans_subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        $transaction_stmt->bind_param("iiissd", $cashier_id, $customer_id, $product_id, $payment_method, $status, $subtotal);
        $transaction_stmt->execute();

        $new_quantity = (int) $product['product_quantity'] - $quantity;
        $update_stmt = $conn->prepare("UPDATE product SET product_quantity = ? WHERE productID = ?");
        $update_stmt->bind_param("ii", $new_quantity, $product_id);
        $update_stmt->execute();
        $conn->commit();
        $_SESSION['success_message'] = "Sales transaction recorded successfully.";
    } catch (Throwable $error) {
        $conn->rollback();
        $_SESSION['error_message'] = "Unable to record the transaction.";
    }
    header("Location: sales.php");
    exit();
}

$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['error_message'], $_SESSION['success_message']);
$products = $conn->query("SELECT productID, product_name, product_price, product_quantity FROM product WHERE product_quantity > 0 ORDER BY product_name");
$transactions = $conn->query(
    "SELECT st.sales_trans_ID, st.customerID, c.customer_name, st.salestrans_payment_method,
            st.salestrans_status, st.salestrans_subtotal, p.product_name
     FROM sales_transaction st
     JOIN customer c ON st.customerID = c.customerID
     JOIN product p ON st.productID = p.productID
     ORDER BY st.sales_trans_ID DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Data</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sales.css">
</head>
<body>
    <div class="sales-container">
        <div class="header">
            <div class="welcome">Sales Data</div>
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
            <h2>Add Transaction</h2>
            <form method="POST" action="sales.php">
                <label>Customer Name</label><br>
                <input type="text" name="customer_name" required><br>
                <label>Customer Address</label><br>
                <input type="text" name="customer_address"><br>
                <label>Customer Contact</label><br>
                <input type="text" name="customer_number"><br>
                <label>Customer Type</label><br>
                <select name="customer_type" required>
                    <option value="Regular">Regular</option>
                    <option value="VIP">VIP</option>
                </select><br>
                <label>Item</label><br>
                <select name="product_id" required>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <option value="<?= (int) $product['productID'] ?>">
                            <?= htmlspecialchars($product['product_name']) ?> - ₱<?= number_format((float) $product['product_price'], 2) ?>
                        </option>
                    <?php endwhile; ?>
                </select><br>
                <label>Quantity</label><br>
                <input type="number" name="quantity" min="1" required><br>
                <label>Payment Method</label><br>
                <select name="payment_method" required>
                    <option value="Cash">Cash</option>
                    <option value="Gcash">Gcash</option>
                </select><br>
                <label>Transaction Status</label><br>
                <select name="status" required>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                </select><br>
                <button type="submit" class="action-button">Add Transaction</button>
            </form>
            <?php if ($error_message !== ''): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php elseif ($success_message !== ''): ?>
                <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Transaction Records</h2>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Customer Name</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Product</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($transaction = $transactions->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int) $transaction['sales_trans_ID'] ?></td>
                                <td><?= htmlspecialchars($transaction['customer_name']) ?></td>
                                <td>₱<?= number_format((float) $transaction['salestrans_subtotal'], 2) ?></td>
                                <td><?= htmlspecialchars($transaction['salestrans_payment_method']) ?></td>
                                <td><?= htmlspecialchars($transaction['salestrans_status']) ?></td>
                                <td><?= htmlspecialchars($transaction['product_name']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
