<?php
include 'db.php';
session_start();

if (!(isset($_SESSION['user_id']) && $_SESSION['user_id'])) {
    header("Location: /");
    exit();
}
$user_id = $_SESSION['user_id'];
$check_admin_query = "SELECT role FROM users WHERE id = $user_id";
$check_admin_result = $conn->query($check_admin_query);

$is_admin = false;

if ($check_admin_result->num_rows > 0) {
    $row = $check_admin_result->fetch_assoc();
    $is_admin = ($row['role'] === 'admin');
}

if (!$is_admin) {
    header("Location: /");
    exit();
}

$cart_count = 0;
$cart_count_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
$cart_count_result = $conn->query($cart_count_query);

if ($cart_count_result->num_rows > 0) {
    $row = $cart_count_result->fetch_assoc();
    $cart_count = $row['count'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'];
  $description = $_POST['description'];
  $price = $_POST['price'];
  $company = $_POST['company'];

  $image = $_FILES['image']['name'];
  $image2 = $_FILES['image2']['name'];

  $targetDirectory = "uploads/";
  $targetFilePath = $targetDirectory . basename($image);
  $targetFilePath2 = $targetDirectory . basename($image2);

  if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath) && move_uploaded_file($_FILES['image2']['tmp_name'], $targetFilePath2)) {
      $sql = "INSERT INTO products (name, description, price, image_url, image_url_2, company) VALUES (?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);

      if ($stmt) {
          $stmt->bind_param("ssdsss", $name, $description, $price, $targetFilePath, $targetFilePath2, $company);
          $stmt->execute();

          if ($stmt->affected_rows > 0) {
              $successMessage = "Product successfully added.";
          } else {
              $errorMessage = "Error adding product: " . $stmt->error;
          }

          $stmt->close();
      } else {
          $errorMessage = "Error preparing statement: " . $conn->error;
      }
  } else {
      $errorMessage = "Failed to upload the images.";
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
  <title>Admin Dashboard</title>
</head>
<body>
<div class="wrapper">
<?php include 'header.php'; ?> 
<div class="container">
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" style="width: 100%;">
<?php if(isset($successMessage)): ?>
    <p class="success-message"><?php echo $successMessage; ?></p>
<?php endif; ?>
  <h1>Add Product</h1>
  
  <div class="form-group">
    <label for="name">Name <span>Enter product name</span></label>
    <input type="text" name="name" id="name" class="form-controll" required/>
  </div>

  <div class="form-group">
    <label for="description">Description <span>Enter product description</span></label>
    <input type="text" name="description" id="description" class="form-controll" required/>
  </div>

  <div class="form-group">
    <label for="price">Price <span>Enter product price</span></label>
    <input type="number" name="price" id="price" class="form-controll" step="0.01" required/>
  </div>
  
  <div class="form-group file-area">
    <label for="image">Image 1 <span>Your image should be at least 400x300 wide</span></label>
    <input type="file" name="image" id="image" onchange="displayImage(this, 'image-preview-1')" required/>
    <div class="file-dummy">
        <div class="success">Great, your file is selected. Proceed.</div>
        <div class="default">Please select a file</div>
        <img id="image-preview-1" src="" alt="Image Preview" style="max-width: 100%; max-height: 200px; display: none; margin:auto;">
    </div>
  </div>
    
  <div class="form-group file-area">
      <label for="image2">Image 2 <span>Your image should be at least 400x300 wide</span></label>
      <input type="file" name="image2" id="image2" onchange="displayImage(this, 'image-preview-2')" required/>
      <div class="file-dummy">
          <div class="success">Great, your file is selected. Proceed.</div>
          <div class="default">Please select a file</div>
          <img id="image-preview-2" src="" alt="Image Preview" style="max-width: 100%; max-height: 200px; display: none; margin:auto;">
      </div>
  </div>

  <div class="form-group">
  <label for="company">Company <span>Select product company</span></label>
  <select name="company" id="company" class="form-controll" required>
    <option value="" disabled selected>Select a company</option>
    <option value="Apple">Apple</option>
    <option value="Samsung">Samsung</option>
    <option value="Sony">Sony</option>
  </select>
</div>

  
  <div class="form-group">
    <input type="submit" value="Add Product">
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