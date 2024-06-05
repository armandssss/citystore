<?php
include "db.php";

if (isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
    $sql = "SELECT o.order_id as order_id, o.status, oi.product_id, oi.quantity, p.name, p.price, p.image_url
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE o.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;">
                <h2>User Orders</h2>';
        
        while ($row = $result->fetch_assoc()) {
            echo '<div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                    <p><strong>Order ID:</strong> ' . $row['order_id'] . '</p>
                    <p><strong>Status:</strong> ' . $row['status'] . '</p>
                    <p><strong>Product:</strong> ' . $row['name'] . '</p>
                    <p><strong>Quantity:</strong> ' . $row['quantity'] . '</p>
                    <p><strong>Price:</strong> ' . $row['price'] . '</p>
                    <img src="' . $row['image_url'] . '" alt="Product Image" style="max-width: 100px;">
                </div>';
        }

        echo '</div>';
    } else {
        echo "No orders found for this user.";
    }
}
?>
