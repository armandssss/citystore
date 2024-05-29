<?php
include "db.php";
require __DIR__ . "/vendor/autoload.php";

$stripe_secret_key = "sk_test_51OP1WTFpFWLlek0v0V40c2j9IxfQIzeBgoEGw8iMLlgEuHz8PEwaYzUSay2lPo1GRULhgmpwQRy8dA5QFJVXSap100KtRGOuH8";

\Stripe\Stripe::setApiKey($stripe_secret_key);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

if (isset($_GET['id']) && isset($_GET['price']) && isset($_GET['name'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_GET['id'];
    $product_price = $_GET['price'];
    $product_name = urldecode($_GET['name']);
} else {
    echo "Error: Product details are missing.";
    exit;
}

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        "payment_method_types" => ["card"],
        "line_items" => [[
            "price_data" => [
                "currency" => "eur",
                "unit_amount" => $product_price * 100,
                "product_data" => [
                    "name" => $product_name
                ]
            ],
            "quantity" => 1,
        ]],
        "mode" => "payment",
        "success_url" => "/checkout-success.php?status=success&id=$product_id&price=$product_price&name=" . urlencode($product_name),
        "cancel_url" => "/",
        "locale" => "auto",
    ]);

    // Redirect to the checkout session URL
    header("Location: " . $checkout_session->url);
    exit;
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Handle Stripe API errors
    echo "Error: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    // Handle other exceptions
    echo "Error: " . $e->getMessage();
    exit;
}
?>