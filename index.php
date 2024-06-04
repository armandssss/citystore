<?php
// Sāk sesiju, ja tā vēl nav sākta
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Iekļauj datubāzes savienojuma failu
include 'db.php';

$is_admin = false; // Inicializē mainīgo, kas norāda, vai lietotājs ir administrators

// Pārbauda, vai sesijā ir lietotāja ID
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Vaicājums, lai pārbaudītu, vai lietotājs ir administrators
    $check_admin_query = "SELECT role FROM users WHERE id = ?";
    $check_admin_stmt = $conn->prepare($check_admin_query);
    $check_admin_stmt->bind_param("i", $user_id);
    $check_admin_stmt->execute();
    $check_admin_result = $check_admin_stmt->get_result();

    // Pārbauda, vai rezultāts satur rindas
    if ($check_admin_result->num_rows > 0) {
        $row = $check_admin_result->fetch_assoc();
        $is_admin = ($row['role'] === 'admin'); // Ja lietotājs ir administrators, uzstāda $is_admin uz true
    }

    // Pārbauda, vai sesijā ir lietotāja ID (atkārtoti)
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        // Vaicājums, lai iegūtu groza preču skaitu
        $cart_count_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
        $cart_count_stmt = $conn->prepare($cart_count_query);
        $cart_count_stmt->bind_param("i", $user_id);
        $cart_count_stmt->execute();
        $cart_count_result = $cart_count_stmt->get_result();

        // Pārbauda, vai rezultāts satur rindas
        if ($cart_count_result->num_rows > 0) {
            $row = $cart_count_result->fetch_assoc();
            $cart_count = $row['count']; // Iegūst preču skaitu grozā
        }
    }
}

// Vaicājums, lai iegūtu visas kategorijas
$category_query = "SELECT * FROM categories";
$category_result = $conn->query($category_query);

// Pārbauda, vai kategorijas vaicājums izdevies
if ($category_result) {
    $categories = $category_result->fetch_all(MYSQLI_ASSOC); // Iegūst visas kategorijas
} else {
    $categories = array(); // Ja vaicājums neizdevās, inicializē tukšu kategoriju masīvu
}

// Pārbauda, vai GET pieprasījumā ir meklēšanas vaicājums
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search_query'];
    // Vaicājums, lai meklētu produktus pēc nosaukuma
    $sql = "SELECT product_id, name, description, price, image_url, company FROM products WHERE name LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchParam = "%" . $searchQuery . "%";
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif (isset($_GET['category'])) {
    $category_id = $_GET['category'];
    // Vaicājums, lai iegūtu produktus pēc kategorijas
    $sql = "SELECT p.product_id, p.name, p.description, p.price, p.image_url, p.company
            FROM products p
            INNER JOIN product_categories pc ON p.product_id = pc.product_id
            WHERE pc.category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Vaicājums, lai iegūtu visus produktus
    $sql = "SELECT product_id, name, description, price, image_url, company FROM products";
    $result = $conn->query($sql);
}

// Funkcija, lai pārbaudītu, vai lietotājs ir pieteicies
function is_loggedin($conn) {
    if (isset($_SESSION['user_id'])) {
        $currentDateTime = date('Y-m-d H:i:s');
        // Atjaunina lietotāja pēdējā pieteikšanās laiku
        $stmt = $conn->prepare('UPDATE users SET last_seen = ? WHERE id = ?');
        $stmt->bind_param('si', $currentDateTime, $_SESSION['user_id']);
        $stmt->execute();
        return true; // Lietotājs ir pieteicies
    }

    return false; // Lietotājs nav pieteicies
}

is_loggedin($conn); // Pārbauda, vai lietotājs ir pieteicies

// Pārbauda, vai pieprasījuma metode ir POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pārbauda, vai tiek mēģināts dzēst produktu un vai lietotājs ir administrators
    if (isset($_POST['delete_product_id']) && $is_admin) {
        $delete_product_id = $_POST['delete_product_id'];
        // Vaicājums, lai dzēstu produktu
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