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
<style>
.file-area {
  width: 100%;
  position: relative;
  
  input[type=file] {
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
  
  .file-dummy {
    width: 100%;
    padding: 30px;
    background: rgba(255,255,255,0.2);
    border: 2px dashed rgba(255,255,255,0.2);
    text-align: center;
    transition: background 0.3s ease-in-out;
    
    .success {
      display: none;
    }
  }
  
  &:hover .file-dummy {
    background: rgba(255,255,255,0.1);
  }
  
  input[type=file]:focus + .file-dummy {
    outline: 2px solid rgba(255,255,255,0.5);
    outline: -webkit-focus-ring-color auto 5px;
  }
  
  input[type=file]:valid + .file-dummy {
    border-color: rgba(0,255,0,0.4);
    background-color: rgba(0,255,0,0.3);

    .success {
      display: inline-block;
    }
    .default {
      display: none;
    }
  }
}

.file-area input[type="file"], input[type=text], input[type=number], textarea {
  background-color: #fff;
  border-color: #ccc;
  box-shadow: none;
}

.file-area .file-dummy {
  border-color: #ccc;
  width: calc(100% - 8px);
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
    margin-left:10px;
  
  span {
    float: right;
    text-transform: none;
    font-weight: 200;
    line-height: 1em;
    font-style: italic;
    opacity: 0.8;
    margin-right:10px;
  }
}

.form-controll {
  display: block;
  padding: 8px 16px;
  width: 100%;
  font-size: 16px;
  background-color: rgba(255,255,255,0.2);
  border: 1px solid rgba(255,255,255,0.3);
  color: #fff;
  font-weight: 200;
  
  &:focus {
    outline: 2px solid rgba(255,255,255,0.5);
    outline: -webkit-focus-ring-color auto 5px;
  }
}

.form-controll,
.file-area input[type="file"] {
  display: block;
  padding: 8px 16px;
  width: 100%;
  font-size: 16px;
  background-color: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: #000;
  font-weight: 200;
  border-color: #ccc;
}

.form-group {
  max-width: 500px;
  margin: auto;
  margin-bottom: 30px;
  display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.back-to-article {
  color: #fff;
  text-transform: uppercase;
  font-size: 12px;
  position: absolute;
  right: 20px;
  top: 20px;
  text-decoration: none;
  display: inline-block;
  background: rgba(0,0,0,0.6);
  padding: 10px 18px;
  transition: all 0.3s ease-in-out;
  opacity: 0.6;
  
  &:hover {
    opacity: 1;
    background: rgba(0,0,0,0.8);
  }
}
</style>
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