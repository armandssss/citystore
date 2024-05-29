<?php
include 'db.php';

if (isset($_GET['cart_id']) && isset($_GET['quantity'])) {
    $cart_id = $_GET['cart_id'];
    $quantity = $_GET['quantity'];

    $update_query = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $quantity, $cart_id);

    if ($stmt->execute()) {
        $totalSum = updateCartTotal();
        echo "Quantity updated successfully. Total: EUR " . number_format($totalSum, 2);
    } else {
        echo "Error updating quantity: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid parameters";
}

function updateCartTotal() {
    global $conn;

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT COALESCE(SUM(products.price * cart.quantity), 0) as totalSum 
                FROM cart 
                LEFT JOIN products ON cart.product_id = products.product_id 
                WHERE cart.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result) {
                $row = $result->fetch_assoc();
                $totalSum = $row['totalSum'];

                $_SESSION['cart_total'] = $totalSum;
                $updateTotalQuery = "INSERT INTO cart_total (user_id, total) VALUES (?, ?)
                                    ON DUPLICATE KEY UPDATE total = VALUES(total)";
                $stmtUpdate = $conn->prepare($updateTotalQuery);
                $stmtUpdate->bind_param("id", $user_id, $totalSum);

                if ($stmtUpdate->execute()) {
                    return $totalSum;
                } else {
                    echo "Error updating cart total: " . $stmtUpdate->error;
                }

                $stmtUpdate->close();
            } else {
                echo "Error fetching cart total result: " . $stmt->error;
            }
        } else {
            echo "Error fetching cart total: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>