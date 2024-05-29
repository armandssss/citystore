<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';
$is_admin = false;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_count_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
    $cart_count_result = $conn->query($cart_count_query);

    if ($cart_count_result->num_rows > 0) {
        $row = $cart_count_result->fetch_assoc();
        $cart_count = $row['count'];
    }
    $user_query = "SELECT role FROM users WHERE id = $user_id";
    $user_result = $conn->query($user_query);

    if ($user_result && $user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $user_role = $user_data['role'];
        if ($user_role === 'admin') {
            $is_admin = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-<correct_hash_here>" crossorigin="anonymous">
    <title>Contact Us - CityStore</title>
</head>
<body>
<div class="wrapper">
<header>
<?php include 'header.php'; ?> 
</header>
<div class="container"> 
    <div class="main-page-wrapper">
                <div class="contact-info">
                    <h2>Contact Us</h2>
                    <p>If you have any questions or need assistance, please don't hesitate to contact us:</p>
                    <ul>
                        <li><strong>Email:</strong> citystore.help@gmail.com</li>
                        <li><strong>Phone:</strong> +371 23200344</li>
                        <li><strong>Address:</strong> Valmieras street 41, Cesis, Latvia</li>
                    </ul>
                    <p><strong>Our customer service team</strong> is available to help you from Monday to Friday, 9:00 AM - 5:00 PM.</p>
                </div>
            </div>
        </div>
        <?php include 'footer.php'; ?>
        </div>
    </div>
</body>
</html>