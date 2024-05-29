<?php
// include "db.php";

// // Ensure that the status parameter exists and is set to 'success'
// $status = $_GET['status'] ?? '';
// if ($status !== 'success') {
//     // Redirect to an error page or handle the error scenario
//     header("Location: error.php");
//     exit;
// }

// require __DIR__ . "/vendor/autoload.php";

// $stripe_secret_key = "sk_test_51OP1WTFpFWLlek0v0V40c2j9IxfQIzeBgoEGw8iMLlgEuHz8PEwaYzUSay2lPo1GRULhgmpwQRy8dA5QFJVXSap100KtRGOuH8";

// \Stripe\Stripe::setApiKey($stripe_secret_key);

// session_start();
// if (!isset($_SESSION['user_id'])) {
//     header('Location: http://localhost/citystore/');
//     exit;
// }

// $user_id = $_SESSION['user_id'];

// try {
//     // Retrieve the Checkout Session ID from the URL
//     $checkout_session_id = $_GET['session_id'] ?? '';

//     // Retrieve the Checkout Session from Stripe
//     $checkout_session = \Stripe\Checkout\Session::retrieve($checkout_session_id);

//     // Extract the product details from the Checkout Session
//     $line_items = $checkout_session->line_items;
//     $product_id = $line_items->data[0]->price->product;

//     // Fetch the product price from the products table
//     $product_query = "SELECT product_id, price FROM products WHERE product_id = ?";
//     $product_stmt = $conn->prepare($product_query);
//     $product_stmt->bind_param("i", $product_id);
//     $product_stmt->execute();
//     $result = $product_stmt->get_result();
//     $product = $result->fetch_assoc();

//     if (!$product) {
//         throw new Exception("Product not found.");
//     }

//     $product_price = $product['price']; // Corrected column name

//     // Insert the purchased product into the orders table
//     $insert_order_query = "INSERT INTO orders (user_id, product_id, quantity, total, status)
//                            VALUES (?, ?, 1, ?, 'successful')";
//     $insert_order_stmt = $conn->prepare($insert_order_query);
//     $total = $product_price; // Set total to product price
//     $insert_order_stmt->bind_param("iid", $user_id, $product_id, $total);
//     if (!$insert_order_stmt->execute()) {
//         throw new Exception("Error inserting into orders table: " . $conn->error);
//     } else {
//         echo "Product inserted successfully into orders table.";
//     }

//     // Close the database connection
//     $conn->close();

// } catch (Exception $e) {
//     // Handle any errors that occur during the process
//     echo "Error: " . $e->getMessage();
//     exit;
// }

// // Redirect the user to the success page after successfully inserting into the database
// header("Location: http://localhost/citystore/buy-now-success.php");
// exit;
?>

<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout Success</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://js.stripe.com/v3/"></script>
    <meta http-equiv="Content-Security-Policy">
</head>
<body style="background-color:#2BAE66;justify-content: center;">
    <div class="payment-success">
        <h1>Payment Successful!</h1>
        <p>Your payment has been processed successfully. Thank you for your purchase!</p>
        <strong><p><a href="http://localhost/citystore/" style="color:white;">Go Back to Shop</a></p></strong>
    </div>
</body>
</html> -->
