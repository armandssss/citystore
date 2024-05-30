<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';
require __DIR__ . "/vendor/autoload.php";
$uploadsFolder = "uploads/";

$stripe_secret_key = "sk_test_51OP1WTFpFWLlek0v0V40c2j9IxfQIzeBgoEGw8iMLlgEuHz8PEwaYzUSay2lPo1GRULhgmpwQRy8dA5QFJVXSap100KtRGOuH8";

\Stripe\Stripe::setApiKey($stripe_secret_key);

if (!isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT cart.*, products.name, products.price, products.image_url, cart.quantity
        FROM cart 
        INNER JOIN products ON cart.product_id = products.product_id
        WHERE cart.user_id = $user_id";

$result = $conn->query($sql);
if ($result === false) {
    die("Error executing the query: " . $conn->error);
}
$cartItems = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $itemName = $row['name'];
        $itemPrice = $row['price'];
        $itemQuantity = $row['quantity'];
        $itemImage = $row['image_url'];
        $cartItems[] = [
            'name' => $itemName,
            'price' => $itemPrice,
            'quantity' => $itemQuantity,
            'image_url' => $itemImage
        ];
        $cartId = $row['cart_id'];
        $update_status_query = "UPDATE cart SET status = 'pending' WHERE cart_id = $cartId";
        $conn->query($update_status_query);
    }
}

$lineItems = [];
foreach ($cartItems as $item) {
    $lineItems[] = [
        "quantity" => $item['quantity'],
        "price_data" => [
            "currency" => "eur",
            "unit_amount" => $item['price'] * 100,
            "product_data" => [
                "name" => $item['name'],
                "images" => [$_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $uploadsFolder . '/' . $item['image_url']],
            ],
        ],
    ];
}

$env = parse_ini_file('.env');

$checkout_session = \Stripe\Checkout\Session::create([
    "mode" => "payment",
    "success_url" => $env["STRIPE_SUCCESS"],
    "cancel_url" => $env["STRIPE_CANCEL"],
    "locale" => "auto",
    "line_items" => $lineItems,
]);

header("Location: " . $checkout_session->url);
exit;
?>