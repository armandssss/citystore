<?php
include 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_count_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
    $cart_count_result = $conn->query($cart_count_query);

    if ($cart_count_result->num_rows > 0) {
        $row = $cart_count_result->fetch_assoc();
        $cart_count = $row['count'];
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /");
    exit;
}

$user_id = $_SESSION['user_id'];

$check_admin_query = "SELECT role FROM users WHERE id = $user_id";
$check_admin_result = $conn->query($check_admin_query);

$is_admin = false;

if ($check_admin_result) {
    if ($check_admin_result->num_rows > 0) {
        $row = $check_admin_result->fetch_assoc();
        $user_role = $row['role'];
        $is_admin = ($user_role === 'admin');
    } else {
        echo "No rows returned for the user ID: $user_id<br>";
    }
} else {
    echo "Error executing the query: " . $conn->error . "<br>";
}

if (!$is_admin) {
    echo "You don't have permission to access this page.";
    exit;
}

$product = [];
$updateSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $sql = "SELECT * FROM products WHERE product_id = $product_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "No product found with ID: $product_id";
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];
    $image_url_2 = $_POST['image_url_2'];

    if ($_FILES['image_upload']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $tmp_name = $_FILES['image_upload']['tmp_name'];
        $image_name = basename($_FILES['image_upload']['name']);
        $image_url = $upload_dir . $image_name;

        if (!move_uploaded_file($tmp_name, $image_url)) {
            echo "Failed to move uploaded file.";
            exit;
        }
    }

    if ($_FILES['image_upload_2']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $tmp_name = $_FILES['image_upload_2']['tmp_name'];
        $image_name_2 = basename($_FILES['image_upload_2']['name']);
        $image_url_2 = $upload_dir . $image_name_2;

        if (!move_uploaded_file($tmp_name, $image_url_2)) {
            echo "Failed to move uploaded file.";
            exit;
        }
    }

    $sql = "UPDATE products SET name = ?, description = ?, price = ?, image_url = ?, image_url_2 = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssdssi", $name, $description, $price, $image_url, $image_url_2, $product_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: index.php?updateSuccess=true");
            exit();
        } else {
            echo "Error updating product: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
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
    <title>Edit Product</title>
</head>
<body>
    <div class="wrapper">
        <?php include 'header.php'; ?>
        <div class="container">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data" style="width: 100%;">
                <?php
                    if ($updateSuccess) {
                        echo "<p class='success-message'>Product successfully updated.</p>";
                    }
                ?>
                <h1>Edit Product</h1>
                <div class="form-group">
                    <input type="hidden" name="product_id" value="<?php echo isset($product['product_id']) ? $product['product_id'] : ''; ?>">
                    <label for="name">Name <span>Enter product name</span></label>
                    <input type="text" id="name" name="name" value="<?php echo isset($product['name']) ? $product['name'] : ''; ?>" class="form-controll" required>
                </div>
                <div class="form-group">
                    <label for="description">Description <span>Enter product description</span></label>
                    <input type="text" id="description" name="description" value="<?php echo isset($product['description']) ? $product['description'] : ''; ?>" class="form-controll" required>
                </div>
                <div class="form-group">
                    <label for="price">Price <span>Enter product price</span></label>
                    <input type="number" id="price" name="price" step="0.01" value="<?php echo isset($product['price']) ? $product['price'] : ''; ?>" class="form-controll" required>
                </div>
                <div class="form-group file-area">
                    <label for="image_upload">Image 1 <span>Your image should be at least 400x300 wide</span></label>
                    <input type="file" id="image_upload" name="image_upload" class="form-controll" onchange="displayImage(this, 'current-image')">
                    <div class="file-dummy <?php echo isset($product['image_url']) ? 'success' : ''; ?>">
                        <div class="success">Great, your file is selected. Proceed.</div>
                        <div class="default">Please select a file</div>
                        <img id="current-image" src="<?php echo isset($product['image_url']) ? $product['image_url'] : ''; ?>" alt="Current Image" style="<?php echo isset($product['image_url']) ? 'max-width: 100%; max-height: 200px; display: block; margin:auto;' : 'display:none;'; ?>">
                    </div>
                    <input type="hidden" id="image_url" name="image_url" value="<?php echo isset($product['image_url']) ? $product['image_url'] : ''; ?>">
                </div>
                <div class="form-group file-area">
                    <label for="image_upload_2">Image 2 <span>Your image should be at least 400x300 wide</span></label>
                    <input type="file" id="image_upload_2" name="image_upload_2" class="form-controll" onchange="displayImage(this, 'current-image-2')">
                    <div class="file-dummy <?php echo isset($product['image_url_2']) ? 'success' : ''; ?>">
                        <div class="success">Great, your file is selected. Proceed.</div>
                        <div class="default">Please select a file</div>
                        <img id="current-image-2" src="<?php echo isset($product['image_url_2']) ? $product['image_url_2'] : ''; ?>" alt="Current Additional Image" style="<?php echo isset($product['image_url_2']) ? 'max-width: 100%; max-height: 200px; display: block; margin:auto;' : 'display:none;'; ?>">
                    </div>
                    <input type="hidden" id="image_url_2" name="image_url_2" value="<?php echo isset($product['image_url_2']) ? $product['image_url_2'] : ''; ?>">
                </div>
                <div class="form-group">
                    <input type="submit" value="Update Product" class="form-controll">
                </div>
            </form>
        </div>
        <?php include 'footer.php'; ?>
    </div>
    <script>
        function displayImage(input, previewId) {
            var file = input.files[0];
            var imgPreview = document.getElementById(previewId);

            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    imgPreview.src = e.target.result;
                    imgPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>