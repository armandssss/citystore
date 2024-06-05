<?php
include "db.php";

if (isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
    // Modify the SQL query to fetch the user's username and order date
    $sql = "SELECT u.username, o.order_id, o.status, o.created_at, oi.product_id, oi.quantity, p.name, p.price, p.image_url
            FROM users u
            JOIN orders o ON u.id = o.user_id
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE o.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        if ($result->num_rows > 0) {
            $orders = [];
            $username = '';

            while ($row = $result->fetch_assoc()) {
                $orders[$row['order_id']]['status'] = $row['status'];
                $orders[$row['order_id']]['created_at'] = $row['created_at'];
                $orders[$row['order_id']]['items'][] = $row;
                if (empty($username)) {
                    $username = $row['username'];
                }
            }

            echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;">
                    <h2>Orders for ' . htmlspecialchars($username) . '</h2>';
            
            foreach ($orders as $orderId => $order) {
                echo '<div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        <p><strong>Order ID:</strong> ' . $orderId . '</p>
                        <p><strong>Status:</strong> ' . $order['status'] . '</p>
                        <p><strong>Order Date:</strong> ' . $order['created_at'] . '</p>';

                foreach ($order['items'] as $item) {
                    echo '<div style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">
                            <p><strong>Product:</strong> ' . $item['name'] . '</p>
                            <p><strong>Quantity:</strong> ' . $item['quantity'] . '</p>
                            <p><strong>Price:</strong> ' . $item['price'] . '</p>
                            <img src="' . $item['image_url'] . '" alt="Product Image" style="max-width: 100px;">
                        </div>';
                }

                echo '</div>';
            }

            echo '</div>';
        } else {
            echo "No orders found for this user.";
        }
    } else {
        echo "Error executing query: " . mysqli_error($conn);
    }

    $stmt->close(); // Close the statement
    $conn->close(); // Close the connection
}
?>
