<?php
include "update_last_seen.php";
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection file
include 'db.php';

// Function to update last seen to 'Online'
function updateLastSeenToOnline($conn, $user_id) {
    $update_sql = "UPDATE users SET last_seen = 'Online' WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

$is_admin = false; // Initialize variable to check if the user is an admin

// Check if the user ID is set in the session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Update last seen to 'Online'
    updateLastSeenToOnline($conn, $user_id);

    // Query to check if the user is an admin
    $check_admin_query = "SELECT role FROM users WHERE id = ?";
    $check_admin_stmt = $conn->prepare($check_admin_query);
    $check_admin_stmt->bind_param("i", $user_id);
    $check_admin_stmt->execute();
    $check_admin_result = $check_admin_stmt->get_result();

    // Check if the result contains rows
    if ($check_admin_result->num_rows > 0) {
        $row = $check_admin_result->fetch_assoc();
        $is_admin = ($row['role'] === 'admin'); // Set $is_admin to true if the user is an admin
    }

    // Check if the user ID is set in the session (redundant check)
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        // Query to get the cart item count
        $cart_count_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
        $cart_count_stmt = $conn->prepare($cart_count_query);
        $cart_count_stmt->bind_param("i", $user_id);
        $cart_count_stmt->execute();
        $cart_count_result = $cart_count_stmt->get_result();

        // Check if the result contains rows
        if ($cart_count_result->num_rows > 0) {
            $row = $cart_count_result->fetch_assoc();
            $cart_count = $row['count']; // Get the number of items in the cart
        }
    }
}

// Query to get all categories
$category_query = "SELECT * FROM categories";
$category_result = $conn->query($category_query);

// Check if the category query was successful
if ($category_result) {
    $categories = $category_result->fetch_all(MYSQLI_ASSOC); // Get all categories
} else {
    $categories = array(); // Initialize an empty array if the query failed
}

// Check if there is a search query in the GET request
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search_query'];
    // Query to search for products by name
    $sql = "SELECT product_id, name, description, price, image_url, company FROM products WHERE name LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchParam = "%" . $searchQuery . "%";
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif (isset($_GET['category'])) {
    $category_id = $_GET['category'];
    // Query to get products by category
    $sql = "SELECT p.product_id, p.name, p.description, p.price, p.image_url, p.company
            FROM products p
            INNER JOIN product_categories pc ON p.product_id = pc.product_id
            WHERE pc.category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Query to get all products
    $sql = "SELECT product_id, name, description, price, image_url, company FROM products";
    $result = $conn->query($sql);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if a product is being deleted and if the user is an admin
    if (isset($_POST['delete_product_id']) && $is_admin) {
        $delete_product_id = $_POST['delete_product_id'];
        // Query to delete the product
        $delete_sql = "DELETE FROM products WHERE product_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $delete_product_id);
        $delete_stmt->execute();
        header("Location: /");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <!-- Iekļauj Google fontus -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <!-- Iekļauj CSS failu -->
    <link rel="stylesheet" href="styles.css">
    <!-- Iekļauj FontAwesome ikonfontus -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-<correct_hash_here>" crossorigin="anonymous">
    <title>CityStore - Ērts un lietotājam draudzīgs testa interneta veikals.</title>
    <!-- SEO meta tagi -->
    <meta name="description" content="Testa interneta veikals Citystore – ļoti ērti lietojams un lietotājam draudzīgs interneta veikals.">
    <meta name="keywords" content="CityStore, Citystore, citystore, kvd, lv, iPhone 15 Pro, AirPods Pro, AirPods Max, Apple Watch, MacBook Pro, iPhone SE, iPad Air Pro, Apple Watch SE, AirPods 3, Mac Mini, Apple TV 4K, MacBook Air, Apple">
    <!-- Open Graph meta tagi -->
    <meta property="og:title" content="CityStore - citystore.kvd.lv">
    <meta property="og:url" content="https://citystore.kvd.lv/">
    <meta property="og:image" content="https://citystore.kvd.lv/uploads/citystore.png">
    <meta property="og:description" content="Ērts un lietotājam draudzīgs testa interneta veikals.">
</head>
<body>
    <div class="wrapper">
        <!-- Iekļauj lapas galveni -->
        <?php include 'header.php'; ?>
        <div class="container">
            <?php
                // Parāda ziņojumu, ja produkts veiksmīgi atjaunināts
                if (isset($_GET['updateSuccess']) && $_GET['updateSuccess'] == 'true') {
                    echo "<p class='success-message'>Product successfully updated.</p>";
                }
            ?>
            <div class="main-page-wrapper">
                <div class="search-categories">
                    <div class="category-dropdown">
                        <select id="category-dropdown" onchange="window.location.href=this.value">
                            <!-- Noklusējuma opcija, lai parādītu visus produktus -->
                            <option value="/" <?php if (!isset($_GET['category'])) echo 'selected'; ?>>All Products</option>
                            <!-- Dinamiski izveido kategoriju opcijas -->
                            <?php foreach ($categories as $category) : ?>
                                <option value="?category=<?php echo $category['category_id']; ?>"
                                    <?php if (isset($_GET['category']) && $_GET['category'] == $category['category_id']) echo 'selected'; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endforeach; ?>
                            <div class="dropdown-icon">
                            </div>
                        </select>

                        <i class="fa-solid fa-caret-down"></i> <!-- Ikona dropdown izvēlnei -->
                    </div>
                    <div class="search-container">
                        <!-- Meklēšanas forma -->
                        <form method="GET" action="/" class="search-form">
                            <div class="search-wrapper">
                                <input name="search_query" placeholder="Search for products..." class="search-input"> <!-- Meklēšanas ievades lauks -->
                                <button name="search" class="search-btn"><i class="fas fa-search"></i></button> <!-- Meklēšanas poga ar ikonu -->
                            </div>
                        </form>
                    </div>
                </div>
                <div class="products">
                <?php
                    // Pārbauda, vai ir atrasti produkti
                    if ($result->num_rows > 0) {
                        while ($product = $result->fetch_assoc()) {
                            echo "<div class='product-card'>";
                            // Pārbauda, vai lietotājs ir administrātors
                            if ($is_admin) {
                                echo "<div class='admin-buttons'>";
                                echo "<form id='delete_form' method='POST' action='' style='display:inline-block;'>";
                                echo "<input type='hidden' id='delete_product_id' name='delete_product_id' value=''>";
                                echo "<a href='#' class='remove-btn' onclick='submitForm(" . $product['product_id'] . ")'><i class='fas fa-trash-alt'></i></a>"; // Poga produkta dzēšanai administrātoru lietotājiem
                                echo "</form>";
                                echo "<a href='/edit_product.php?id=" . $product['product_id'] . "' class='edit-btn'><i class='fas fa-pencil-alt'></i></a>"; // Poga produkta rediģēšanai administrātoru lietotājiem
                                echo "</div>";
                            }
                            echo "<a href='/product.php?id=" . $product['product_id'] . "'>"; // Saite uz produkta sīkāku infomrāciju
                            echo "<div class='product-card-top'>";
                            echo "<img class='product-card-img' src='" . $product['image_url'] . "' alt='" . $product['name'] . "'>"; // Produkta attēls
                            echo "</div>";
                            echo "<div class='box-down'>";
                            echo "<div class='card-footer'>";
                            echo "<div class='img-info'>";
                            echo "<span class='p-name'>" . $product['name'] . "</span>"; // Produkta nosaukums
                            echo "<span class='p-company'>" . $product['company'] . "</span>"; // Produkta kompānija
                            echo "</div>";
                            echo "<div class='img-price'>";
                            echo "<span>" . $product['price'] .  " €</span>"; // Produkta cena
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                            echo "</a>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No products found.</p>"; // Parāda ziņojumu, ja produkti nav atrasti
                    }
                ?>
                </div>
            </div>
        </div>
        <footer>
            <!-- Iekļauj lapas kājeni -->
            <?php include 'footer.php'; ?>
        </footer>
    </div>
<script>
    // Funkcija, lai apstiprinātu produkta dzēšanu
    function submitForm(productId) {
        if(confirm('Are you sure you want to delete this product?')) {
            document.getElementById('delete_product_id').value = productId;
            document.getElementById('delete_form').submit();
        }
    }
</script>
</body>
</html>