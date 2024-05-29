<?php
session_start();
include 'db.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_count_query = "SELECT SUM(quantity) as count FROM cart WHERE user_id = $user_id";
    $cart_count_result = $conn->query($cart_count_query);

    if ($cart_count_result && $cart_count_result->num_rows > 0) {
        $row = $cart_count_result->fetch_assoc();
        echo $row['count'];
    } else {
        echo "0";
    }
} else {
    echo "0";
}
?>