<?php
include "db.php";

if (isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);

    // Fetch the username first
    $usernameSql = "SELECT username FROM users WHERE id = ?";
    $usernameStmt = $conn->prepare($usernameSql);
    $usernameStmt->bind_param("i", $userId);
    $usernameStmt->execute();
    $usernameResult = $usernameStmt->get_result();

    if ($usernameResult && $usernameResult->num_rows > 0) {
        $usernameRow = $usernameResult->fetch_assoc();
        $username = htmlspecialchars($usernameRow['username']);

        // Fetch the user's orders
        $sql = "SELECT o.order_id, o.status, o.created_at, oi.product_id, oi.quantity, p.name, p.price, p.image_url
                FROM orders o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE o.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $orders = [];

            while ($row = $result->fetch_assoc()) {
                $orders[$row['order_id']]['status'] = $row['status'];
                $orders[$row['order_id']]['created_at'] = $row['created_at'];
                $orders[$row['order_id']]['items'][] = $row;
            }

            echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 10px;">
                    <h2>Orders for ' . $username . '</h2>';
            
            foreach ($orders as $orderId => $order) {
                echo '<div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        <p><strong>Order ID:</strong> ' . $orderId . '</p>
                        <p><strong>Status:</strong> ' . $order['status'] . '</p>
                        <p><strong>Order Date:</strong> ' . $order['created_at'] . '</p>';

                foreach ($order['items'] as $item) {
                    echo '<div style="border-top: 1px solid #ddd; padding-top: 5px; margin-top: 5px; display: flex; align-items: center;">
                            <img src="' . htmlspecialchars($item['image_url']) . '" alt="Product Image" style="max-width: 50px; margin-right: 10px;">
                            <div>
                                <p style="margin: 0;"><strong>Product:</strong> ' . htmlspecialchars($item['name']) . '</p>
                                <p style="margin: 0;"><strong>Quantity:</strong> ' . htmlspecialchars($item['quantity']) . '</p>
                                <p style="margin: 0;"><strong>Price:</strong> ' . htmlspecialchars($item['price']) . '</p>
                            </div>
                        </div>';
                }

                echo '</div>';
            }

            echo '</div>';
        } else {
            echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 10px;">
                    <h2>No orders found for ' . $username . '</h2>
                  </div>';
        }
    } else {
        echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 10px;">
                <h2>User not found.</h2>
              </div>';
    }

    $usernameStmt->close(); // Close the statement
    $stmt->close(); // Close the statement
    $conn->close(); // Close the connection
}
?>
