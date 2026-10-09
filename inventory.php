<?php
session_start();
include 'db_connect.php';
include 's3_storage.php';

$error_message = ""; // For validation feedback

// Only allow owners
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

// Handle adding a new item
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_item'])) {
    $item_name = trim($_POST['item_name']);
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $acquired_date = $_POST['acquired_date'];
    $category = $_POST['category'];
    $status = 'Available';

    if (!is_numeric($quantity) || $quantity <= 0 || !is_numeric($price) || $price <= 0) {
        $error_message = "Please enter valid positive integers for quantity and price.";
    } else {
        try {
            $image_url = upload_product_image($_FILES['product_image'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
            $cashierID = 1;
            $conn->begin_transaction();
            $stmt = $conn->prepare("INSERT INTO product (cashierID, product_name, product_quantity, product_price, product_date, product_status, product_category, product_image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isidssss", $cashierID, $item_name, $quantity, $price, $acquired_date, $status, $category, $image_url);
            $stmt->execute();
            $productID = $stmt->insert_id;

            // Insert into inventory
            $ownerID = 1;
            $change_type = 'Added';
            $status_level = 'Normal';
            $inv_stmt = $conn->prepare("INSERT INTO inventory (productID, ownerID, inventory_change_type, inventory_quantity, inventory_change_date, inventory_status_level) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, ?)");
            $inv_stmt->bind_param("iisis", $productID, $ownerID, $change_type, $quantity, $status_level);
            $inv_stmt->execute();
            $conn->commit();
            echo '<script>showToast("Product and inventory entry added successfully.");</script>';
        } catch (Throwable $error) {
            $conn->rollback();
            $error_message = "Unable to add the product.";
        }
    }
}

// Handle updating quantity
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_quantity'])) {
    $productID = $_POST['productID'];
    $quantity_change = $_POST['quantity_change'];
    $update_type = $_POST['update_type'];

    if (!is_numeric($quantity_change) || $quantity_change <= 0) {
        $error_message = "Value must be greater than or equal to 1.";
    } else {
        $conn->begin_transaction();
        $stmt = $conn->prepare("SELECT product_quantity FROM product WHERE productID = ? FOR UPDATE");
        $stmt->bind_param("i", $productID);
        $stmt->execute();
        $result = $stmt->get_result();
        $current = $result->fetch_assoc();
        if (!$current) {
            $conn->rollback();
            $error_message = "Product not found.";
        } else {
        $current_quantity = (int) $current['product_quantity'];

        if ($update_type === 'add') {
            $new_quantity = $current_quantity + $quantity_change;
        } elseif ($update_type === 'reduce') {
            $new_quantity = max(0, $current_quantity - $quantity_change);
        }

        $update_stmt = $conn->prepare("UPDATE product SET product_quantity = ? WHERE productID = ?");
        $update_stmt->bind_param("ii", $new_quantity, $productID);
        $update_stmt->execute();

        $ownerID = 1;
        $inventory_change_type = ($update_type === 'add') ? 'Added' : 'Reduced';
        $inventory_status_level = 'Normal';

        $inv_stmt = $conn->prepare("INSERT INTO inventory (productID, ownerID, inventory_change_type, inventory_quantity, inventory_change_date, inventory_status_level) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, ?)");
        $inv_stmt->bind_param("iisis", $productID, $ownerID, $inventory_change_type, $quantity_change, $inventory_status_level);
        $inv_stmt->execute();
        $conn->commit();
        echo '<script>showToast("Inventory updated successfully.");</script>';
        }
    }
}

// Handle updating price
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_price'])) {
    $productID = $_POST['productID'];
    $new_price = $_POST['new_price'];

    if (!is_numeric($new_price) || $new_price <= 0) {
        $error_message = "Please enter a valid positive price.";
    } else {
        $update_stmt = $conn->prepare("UPDATE product SET product_price = ? WHERE productID = ?");
        $update_stmt->bind_param("di", $new_price, $productID);

        if ($update_stmt->execute()) {
            echo '<script>showToast("Price updated successfully.");</script>';
        } else {
            echo '<script>showToast("Error updating price.");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="inventory1.css">
    <style>
        /* Toast Notification */
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
        .error-message {
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-top: 8px;
            font-size: 14px;
        }
        .form-inline {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="inventory-container">
        <div class="header">
            <div class="welcome">Inventory Management</div>
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
            <h2>Add New Item</h2>
            <form method="POST" action="inventory.php" enctype="multipart/form-data">
                <input type="text" name="item_name" placeholder="Item Name" required><br>
                <input type="number" name="quantity" placeholder="Quantity" required><br>
                <input type="number" step="0.01" name="price" placeholder="Price" required><br>
                <input type="date" name="acquired_date" required><br>
                <input type="file" name="product_image" accept="image/jpeg,image/png,image/webp"><br>
                <select name="category" required>
                    <option value="Drinks">Drinks</option>
                    <option value="Snacks">Snacks</option>
                </select><br>
                <button type="submit" name="add_item" class="action-button">Add Item</button>
                <?php if (!empty($error_message) && isset($_POST['add_item'])): ?>
                    <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>
            </form>
        </div>

        <div class="section">
            <h2>Inventory Items</h2>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Date Acquired</th>
                            <th>Category</th>
                            <th>Image</th>
                            <th>Last Update</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "
                            SELECT p.productID, p.product_name, p.product_quantity, p.product_price, p.product_date, p.product_category, p.product_image_url,
                                   MAX(i.inventory_change_date) AS last_update
                            FROM product p
                            LEFT JOIN inventory i ON p.productID = i.productID
                            GROUP BY p.productID
                            ORDER BY last_update DESC";
                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><?= $row['product_quantity'] ?></td>
                                <td><?= $row['product_price'] ?></td>
                                <td><?= $row['product_date'] ?></td>
                                <td><?= $row['product_category'] ?></td>
                                <td>
                                    <?php if (!empty($row['product_image_url'])): ?>
                                        <img src="<?= htmlspecialchars($row['product_image_url']) ?>" alt="Product image" width="60">
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?= $row['last_update'] ? date("Y-m-d H:i:s", strtotime($row['last_update'])) : 'N/A' ?></td>
                                <td>
                                    <form method="POST" action="inventory.php" class="form-inline">
                                        <input type="hidden" name="productID" value="<?= $row['productID'] ?>">
                                        <input type="number" name="quantity_change" placeholder="Quantity" required>
                                        <select name="update_type" required>
                                            <option value="add">Add</option>
                                            <option value="reduce">Reduce</option>
                                        </select>
                                        <button type="submit" name="update_quantity" class="action-button">Update Quantity</button>
                                    </form>
                                    <form method="POST" action="inventory.php" class="form-inline">
                                        <input type="hidden" name="productID" value="<?= $row['productID'] ?>">
                                        <input type="number" step="0.01" name="new_price" placeholder="New Price" required>
                                        <button type="submit" name="update_price" class="action-button">Update Price</button>
                                    </form>
                                    <?php if (!empty($error_message) && isset($_POST['productID']) && $_POST['productID'] == $row['productID']): ?>
                                        <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
                                    <?php endif; ?>
                                </td>
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
    </script>
</body>
</html>
