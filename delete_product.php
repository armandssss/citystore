<!DOCTYPE html>
<html lang="en">
<?php
include 'db.php';

if (isset($_POST['delete'])) {
    $product_id = $_POST['product_id'];

    $delete_cart_query = "DELETE FROM cart WHERE product_id = $product_id";
    $conn->query($delete_cart_query);

    $delete_product_query = "DELETE FROM products WHERE product_id = $product_id";
    if ($conn->query($delete_product_query) === TRUE) {
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Error deleting product: " . $conn->error;
    }
}

$conn->close();
?>