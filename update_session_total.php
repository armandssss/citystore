<?php
session_start();

if (isset($_GET['total_sum'])) {
    $_SESSION['cart_total'] = floatval($_GET['total_sum']);
    echo "Session total updated successfully";
} else {
    echo "Error: Total sum not provided";
}
?>