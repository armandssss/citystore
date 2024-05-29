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
    header("Location: http://localhost/citystore/");
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

    if ($_FILES['image_upload']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $tmp_name = $_FILES['image_upload']['tmp_name'];
        $image_name = basename($_FILES['image_upload']['name']);
        $image_url = $upload_dir . $image_name;

        if (move_uploaded_file($tmp_name, $image_url)) {
        } else {
            echo "Failed to move uploaded file.";
            exit;
        }
    }

    $sql = "UPDATE products SET name = ?, description = ?, price = ?, image_url = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssdsi", $name, $description, $price, $image_url, $product_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $updateSuccess = true;
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
    <style>
        .file-area {
            width: 100%;
            position: relative;
        }

        .file-area input[type=file] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-area .file-dummy {
            width: 100%;
            padding: 30px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            text-align: center;
            transition: background 0.3s ease-in-out;
        }

        .file-area:hover .file-dummy {
            background: rgba(255, 255, 255, 0.1);
        }

        .file-area input[type=file]:focus + .file-dummy {
            outline: 2px solid rgba(255, 255, 255, 0.5);
            outline: -webkit-focus-ring-color auto 5px;
        }

        .file-area input[type=file]:valid + .file-dummy {
            border-color: rgba(0, 255, 0, 0.4);
            background-color: rgba(0, 255, 0, 0.3);
        }

        .file-area input[type="file"],
        .form-controll,
        .form-controll {
            background-color: #fff;
            border-color: #ccc;
            box-shadow: none;
            display: block;
            padding: 8px 16px;
            width: 100%;
            font-size: 16px;
            color: #000;
            font-weight: 200;
        }

        .file-area .file-dummy {
            /* background-color: #f0f0f0; */
            border-color: #ccc;
            width: calc(100% - 18px);
            padding: 30px 0px;
        }

        h1 {
            text-align: center;
            margin: 50px auto;
            font-weight: 100;
        }

        label {
            font-weight: 500;
            display: block;
            margin: 4px 0;
            text-transform: uppercase;
            font-size: 13px;
            overflow: hidden;
            display: flex;
            width: 100%;
            justify-content: space-between;
            margin-left: 10px;
        }

        label span {
            float: right;
            text-transform: none;
            font-weight: 200;
            line-height: 1em;
            font-style: italic;
            opacity: 0.8;
            margin-right: 10px;
        }

        .form-controll:focus {
            outline: 2px solid rgba(255, 255, 255, 0.5);
            outline: -webkit-focus-ring-color auto 5px;
        }

        .form-group {
            max-width: 30%;
            margin: auto;
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <header>
            <?php include 'header.php'; ?> 
        </header>
        <div class="big-container">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                <?php
                    if ($updateSuccess) {
                        echo "<p class='success-message'>Product successfully updated.</p>";
                    }
                ?>
                <h1>Edit Product</h1>
                <div class="form-group">
                <input type="hidden" name="product_id" value="<?php echo isset($product['product_id']) ? $product['product_id'] : ''; ?>">
                    <label for="name">Product Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo isset($product['name']) ? $product['name'] : ''; ?>" class="form-controll">
                </div>
                <div class="form-group">
                    <label for="description">Product Description:</label>
                    <input type="text" id="description" name="description" value="<?php echo isset($product['description']) ? $product['description'] : ''; ?>" class="form-controll">
                </div>
                <div class="form-group">
                    <label for="price">Product Price:</label>
                    <input type="text" id="price" name="price" step="0.01" value="<?php echo isset($product['price']) ? $product['price'] : ''; ?>" class="form-controll">
                </div>
                <div class="form-group file-area">
                    <label for="image_upload">Upload Image:</label>
                    <input type="file" id="image_upload" name="image_upload" class="form-controll">
                    <div class="file-dummy">
                        <div class="success">Great, your file is selected. Proceed.</div>
                        <div class="default">Please select a file</div>
                    </div>
                    <input type="text" id="image_url" name="image_url" value="<?php echo isset($product['image_url']) ? $product['image_url'] : ''; ?>" style="display: none;">
                </div>
                <div class="form-group">
                    <input type="submit" value="Update Product" class="form-controll">
                </div>
            </form>
        </div>
        <?php include 'footer.php'; ?>
    </div>
</body>
</html>