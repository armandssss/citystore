<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

$is_admin = false;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check_admin_query = "SELECT role FROM users WHERE id = ?";
    $check_admin_stmt = $conn->prepare($check_admin_query);
    $check_admin_stmt->bind_param("i", $user_id);
    $check_admin_stmt->execute();
    $check_admin_result = $check_admin_stmt->get_result();

    if ($check_admin_result->num_rows > 0) {
        $row = $check_admin_result->fetch_assoc();
        $is_admin = ($row['role'] === 'admin');
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
}

$category_query = "SELECT * FROM categories";
$category_result = $conn->query($category_query);

if ($category_result) {
    $categories = $category_result->fetch_all(MYSQLI_ASSOC);
} else {
    $categories = array();
}

if (isset($_GET['search'])) {
    $searchQuery = $_GET['search_query'];

    $sql = "SELECT product_id, name, description, price, image_url, company FROM Products WHERE name LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchParam = "%" . $searchQuery . "%";
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif (isset($_GET['category'])) {
    $category_id = $_GET['category'];
    $sql = "SELECT p.product_id, p.name, p.description, p.price, p.image_url, p.company
            FROM products p
            INNER JOIN product_categories pc ON p.product_id = pc.product_id
            WHERE pc.category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT product_id, name, description, price, image_url, company FROM Products";
    $result = $conn->query($sql);
}

function is_loggedin($conn) {
    if (isset($_SESSION['user_id'])) {
        $currentDateTime = date('Y-m-d H:i:s');
        $stmt = $conn->prepare('UPDATE users SET last_seen = ? WHERE id = ?');
        $stmt->bind_param('si', $currentDateTime, $_SESSION['user_id']);
        $stmt->execute();
        return true;
    }

    return false;
}

is_loggedin($conn);
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
    <title>Home - CityStore</title>
</head>
<body>
    <div class="wrapper">
        <?php include 'header.php'; ?>
        <div class="container">
            <div class="main-page-wrapper">
                <div class="search-categories">
                    <div class="category-dropdown">
                        <select id="category-dropdown" onchange="window.location.href=this.value">
                            <option value="/" <?php if (!isset($_GET['category'])) echo 'selected'; ?>>All Products</option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="?category=<?php echo $category['category_id']; ?>"
                                    <?php if (isset($_GET['category']) && $_GET['category'] == $category['category_id']) echo 'selected'; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fa-solid fa-caret-down"></i>
                    </div>
                    <div class="search-container">
                        <form method="GET" action="/" class="search-form">
                            <div class="search-wrapper">
                                <input name="search_query" placeholder="Search for products..." class="search-input">
                                <button name="search" class="search-btn"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="products">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($product = $result->fetch_assoc()) {
                            echo "<div class='product-card'>";
                            echo "<a href='product.php?id=" . $product['product_id'] . "'>";
                            echo "<div class='product-card-top'>";
                            echo "<img class='product-card-img' src='" . $product['image_url'] . "' alt='" . $product['name'] . "'>";
                            echo "</div>";
                            echo "<div class='box-down'>";
                            echo "<div class='card-footer'>";
                            echo "<div class='img-info'>";
                            echo "<span class='p-name'>" . $product['name'] . "</span>";
                            echo "<span class='p-company'>" . $product['company'] . "</span>";
                            echo "</div>";
                            echo "<div class='img-price'>";
                            echo "<span>" . $product['price'] .  " €</span>";
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                            echo "</a>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No products found.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
        <footer>
            <?php include 'footer.php'; ?>
        </footer>
    </div>
</body>
</html>
