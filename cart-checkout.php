<?php
// Sāk sesiju, ja tā vēl nav sākta
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Iekļauj datubāzes savienojuma failu
include 'db.php';

// Iekļauj Composer autoload failu
require __DIR__ . "/vendor/autoload.php";

// Iestata augšupielāžu mapi
$uploadsFolder = "/uploads";

// Stripe slepenās atslēgas iestatīšana
$stripe_secret_key = "sk_test_51OP1WTFpFWLlek0v0V40c2j9IxfQIzeBgoEGw8iMLlgEuHz8PEwaYzUSay2lPo1GRULhgmpwQRy8dA5QFJVXSap100KtRGOuH8";

// Stripe API atslēgas iestatīšana
\Stripe\Stripe::setApiKey($stripe_secret_key);

// Pārbauda, vai sesijā ir lietotāja ID
if (!isset($_SESSION['user_id'])) {
    // Ja nav, pāradresē uz sākumlapu un pārtrauc izpildi
    header('Location: /');
    exit;
}

// Iegūst lietotāja ID no sesijas
$user_id = $_SESSION['user_id'];

// SQL vaicājums, lai iegūtu lietotāja groza saturu ar produkta informāciju
$sql = "SELECT cart.*, products.name, products.price, products.image_url, cart.quantity
        FROM cart 
        INNER JOIN products ON cart.product_id = products.product_id
        WHERE cart.user_id = $user_id";

// Izpilda vaicājumu un pārbauda, vai nav kļūdu
$result = $conn->query($sql);
if ($result === false) {
    die("Error executing the query: " . $conn->error);
}

// Inicializē groza preču masīvu
$cartItems = [];

// Pārbauda, vai vaicājuma rezultāts satur rindas
if ($result->num_rows > 0) {
    // Iterē caur rezultāta rindām
    while ($row = $result->fetch_assoc()) {
        // Iegūst produkta informāciju no rindas
        $itemName = $row['name'];
        $itemPrice = $row['price'];
        $itemQuantity = $row['quantity'];
        $itemImage = $row['image_url'];
        
        // Pievieno preci groza preču masīvam
        $cartItems[] = [
            'name' => $itemName,
            'price' => $itemPrice,
            'quantity' => $itemQuantity,
            'image_url' => $itemImage
        ];
        
        // Iegūst groza ID un atjaunina statusu uz "pending"
        $cartId = $row['cart_id'];
        $update_status_query = "UPDATE cart SET status = 'pending' WHERE cart_id = $cartId";
        $conn->query($update_status_query);
    }
}

// Inicializē Stripe line_items masīvu
$lineItems = [];
foreach ($cartItems as $item) {
    // Pievieno katru preci line_items masīvam
    $lineItems[] = [
        "quantity" => $item['quantity'],
        "price_data" => [
            "currency" => "eur",
            "unit_amount" => $item['price'] * 100, // Stripe prasa cenu centos
            "product_data" => [
                "name" => $item['name'],
            ],
        ],
    ];
}

// Nolasīt .env failu, lai iegūtu URL norādes
$env = parse_ini_file('.env');

// Izveido Stripe Checkout sesiju
$checkout_session = \Stripe\Checkout\Session::create([
    "mode" => "payment",
    "success_url" => $env["STRIPE_SUCCESS"], // Veiksmīgas maksājuma norādes URL
    "cancel_url" => $env["STRIPE_CANCEL"], // Atceltās maksājuma norādes URL
    "locale" => "auto",
    "line_items" => $lineItems,
]);

// Pāradresē lietotāju uz Stripe Checkout sesiju
header("Location: " . $checkout_session->url);
exit;
?>