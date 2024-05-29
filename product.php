<?php
session_start();
include 'db.php';

function isAdmin($conn, $user_id) {
    $check_admin_query = "SELECT role FROM users WHERE id = ?";
    $check_admin_stmt = $conn->prepare($check_admin_query);
    $check_admin_stmt->bind_param("i", $user_id);
    $check_admin_stmt->execute();
    $check_admin_result = $check_admin_stmt->get_result();

    if ($check_admin_result->num_rows > 0) {
        $row = $check_admin_result->fetch_assoc();
        return $row['role'] === 'admin';
    }

    return false;
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_count_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
    $cart_count_stmt = $conn->prepare($cart_count_query);
    $cart_count_stmt->bind_param("i", $user_id);
    $cart_count_stmt->execute();
    $cart_count_result = $cart_count_stmt->get_result();

    if ($cart_count_result->num_rows > 0) {
        $row = $cart_count_result->fetch_assoc();
        $cart_count = $row['count'];
    }
}

$is_admin = isset($user_id) ? isAdmin($conn, $user_id) : false;

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
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
    <title>Product Details - CityStore</title>
</head>
<body>
<div class="wrapper">
    <header>
        <?php include "header.php" ?>
    </header>
    <div class="container">
        <div class="main-page-wrapper">
            <div class="card">
                <div class="card__title">
                    <div class="icon">
                        <a href="javascript:history.back()"><i class="fas fa-arrow-left"></i></a>
                    </div>
                </div>
                <div class="card__body">
                    <div class="half">
                        <div class="image">
                            <div class="slideshow-container">
                                <div class="mySlides fade">
                                    <img class="product-card-img" src='<?php echo $product['image_url']; ?>' alt='<?php echo $product['name']; ?>'>
                                </div>
                                <div class="mySlides fade">
                                    <img class="product-card-img" src='<?php echo $product['image_url_2']; ?>' alt='<?php echo $product['name']; ?>'>
                                </div>
                                <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
                                <a class="next" onclick="plusSlides(1)">&#10095;</a>
                            </div>
                            <div style="text-align:center">
                                <span class="dot" onclick="currentSlide(1)"></span>
                                <span class="dot" onclick="currentSlide(2)"></span>
                            </div>
                        </div>
                    </div>
                    <div class="half">
                        <div class="featured_text">
                            <h1><?php echo $product['name']; ?></h1>
                            <p class="price"><b>EUR</b> <?php echo $product['price']; ?></p>
                        </div>
                        <div class="description">
                            <p class="p-company"><?php echo $product['description']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="card__footer">
                    <div class="action">
                        <a href='add_to_cart.php?id=<?php echo $product['product_id']; ?>' class='add-to-cart-button' onclick="addToCart(event)">Add to Cart</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <?php include "footer.php" ?>
    </footer>
</div>
</body>
<script>
    function addToCart(event) {
        var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        if (!isLoggedIn) {
            alert('Please login to add items to your cart.');
            openLoginModal();
            event.preventDefault();
        }
    }

    function buyButtonClicked(event) {
        var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        if (!isLoggedIn) {
            alert('Please login to buy this item.');
            openLoginModal();
            event.preventDefault();
        }
    }

    var slideIndex = 1;
    showSlides(slideIndex);

    function plusSlides(n) {
        showSlides(slideIndex += n);
    }

    function currentSlide(n) {
        showSlides(slideIndex = n);
    }

    function showSlides(n) {
        var i;
        var slides = document.getElementsByClassName("mySlides");
        if (slides && slides.length > 0) {
            if (n > slides.length) {
                slideIndex = 1;
            }
            if (n < 1) {
                slideIndex = slides.length;
            }
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slides[slideIndex - 1].style.display = "block";
        }
    }
</script>
</html>
<?php
    } else {
        echo "Product not found";
    }
} else {
    echo "Product ID not provided";
}

$conn->close();
?>