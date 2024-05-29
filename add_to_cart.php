<?php
session_start();
include 'db.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    if (isset($_SESSION['last_added_time'][$product_id])) {
        if (time() - $_SESSION['last_added_time'][$product_id] < 5) {
            header("Location: product.php?id=" . $product_id . "&error=added_recently");
            exit;
        }
    }
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $product_id);

        if ($stmt->execute()) {
            $_SESSION['last_added_time'][$product_id] = time();
            updateCartTotal($user_id);

            header("Location: product.php?id=" . $product_id . "&success=added_to_cart");
            exit;
        } else {
            echo "Error adding item to the cart: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Please log in to add items to the cart.";
    }
}

function updateCartTotal($user_id) {
    global $conn;

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
?>