<?php
session_start();
include 'db.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Preventing rapid add-to-cart actions
    if (isset($_SESSION['last_added_time'][$product_id])) {
        if (time() - $_SESSION['last_added_time'][$product_id] < 5) {
            header("Location: product.php?id=" . $product_id . "&error=added_recently");
            exit;
        }
    }

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Check if the product is already in the user's cart
        $check_cart_query = "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?";
        $check_cart_stmt = $conn->prepare($check_cart_query);
        $check_cart_stmt->bind_param("ii", $user_id, $product_id);
        $check_cart_stmt->execute();
        $result = $check_cart_stmt->get_result();

        if ($result->num_rows > 0) {
            // Product is already in the cart, update the quantity
            $row = $result->fetch_assoc();
            $new_quantity = $row['quantity'] + 1;
            $update_cart_query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
            $update_cart_stmt = $conn->prepare($update_cart_query);
            $update_cart_stmt->bind_param("iii", $new_quantity, $user_id, $product_id);

            if ($update_cart_stmt->execute()) {
                $_SESSION['last_added_time'][$product_id] = time();
                updateCartTotal($user_id);
                header("Location: product.php?id=" . $product_id . "&success=added_to_cart");
                exit;
            } else {
                echo "Error updating item quantity in the cart: " . $update_cart_stmt->error;
            }
            $update_cart_stmt->close();
        } else {
            // Product is not in the cart, insert a new entry
            $insert_cart_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
            $insert_cart_stmt = $conn->prepare($insert_cart_query);
            $insert_cart_stmt->bind_param("ii", $user_id, $product_id);

            if ($insert_cart_stmt->execute()) {
                $_SESSION['last_added_time'][$product_id] = time();
                updateCartTotal($user_id);
                header("Location: product.php?id=" . $product_id . "&success=added_to_cart");
                exit;
            } else {
                echo "Error adding item to the cart: " . $insert_cart_stmt->error;
            }
            $insert_cart_stmt->close();
        }
        $check_cart_stmt->close();
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

            if (!$stmtUpdate->execute()) {
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