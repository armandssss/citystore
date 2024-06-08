<?php
include "update_last_seen.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

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

$user_id = $_SESSION['user_id'];
$error_message = '';

$user_query = "SELECT username, email, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result && $user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $username = $user_data['username'];
    $email = $user_data['email'];
    $profile_picture = $user_data['profile_picture'];
} else {
    header("Location: index.php");
    exit;
}
$stmt->close();

$cart_count_query = "SELECT COUNT(*) AS cart_count FROM cart WHERE user_id = $user_id";
$cart_count_result = $conn->query($cart_count_query);

if ($cart_count_result && $cart_count_result->num_rows > 0) {
    $cart_data = $cart_count_result->fetch_assoc();
    $cart_count = $cart_data['cart_count'];
} else {
    $cart_count = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['remove_profile_picture'])) {
        $update_pic_query = "UPDATE users SET profile_picture = NULL WHERE id = ?";
        $stmt = $conn->prepare($update_pic_query);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $profile_picture = null;
        } else {
            $error_message = "Failed to remove profile picture.";
        }
        $stmt->close();
    } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_type = $_FILES['profile_picture']['type'];
        if (($file_type != "image/jpeg") && ($file_type != "image/png") && ($file_type != "image/gif")) {
            $error_message = "Only JPG, PNG, and GIF files are allowed.";
        } else {
            $file_size = $_FILES['profile_picture']['size'];
            if ($file_size > 5242880) {
                $error_message = "File size exceeds maximum limit (5MB).";
            } else {
                $upload_dir = 'uploads/';
                $temp_name = $_FILES['profile_picture']['tmp_name'];
                $original_name = $_FILES['profile_picture']['name'];
                $new_filename = uniqid() . '_' . $original_name;
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($temp_name, $destination)) {
                    $update_pic_query = "UPDATE users SET profile_picture = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_pic_query);
                    $stmt->bind_param("si", $destination, $user_id);
                    if ($stmt->execute()) {
                        $profile_picture = $destination;
                    } else {
                        $error_message = "Failed to update profile picture in the database.";
                    }
                    $stmt->close();
                } else {
                    $error_message = "Failed to move uploaded file to the uploads folder.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>
        window.onload = function() {
            const profilePic = document.getElementById('currentProfilePic');
            const fileInput = document.getElementById('profile_picture');
            const changeProfilePic = document.getElementById('changeProfilePic');
            const profilePictureForm = document.getElementById('profilePictureForm');

            profilePic.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', () => {
                profilePictureForm.submit();
            });

            changeProfilePic.addEventListener('click', () => {
                fileInput.click();
            });
        };
        
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-<correct_hash_here>" crossorigin="anonymous">
    <title>Profile - CityStore</title>
</head>
<body>
<div class="wrapper">
<?php include 'header.php'; ?> 
<div class="container">
    <div class="main-page-wrapper">
        <div class="user-center">
        <div class="settings-buttons">
            <a class="settings-btn active" href="users_profile.php">Profile</a>
            <a class="settings-btn" href="orders.php">Orders</a>
            <a class="settings-btn" href="settings.php">Settings</a>
        </div>
            <div class="user-profile">
            <?php if (!empty($error_message)) : ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <h1>My Profile</h1>
            <div class="profile-pic-container">
                <?php
                echo '<img src="' . $profile_picture . '" alt="Profile Picture" id="currentProfilePic">';
                echo '
                    <form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" enctype="multipart/form-data" id="profilePictureForm">
                        <input type="file" id="profile_picture" name="profile_picture" style="display: none;">
                        <input type="submit" name="update_picture" style="display: none;">
                    </form>
                ';
                ?>
                <div class="profile-pic-overlay" id="changeProfilePic">Change Picture</div>
            </div>
                <div class="user-info">
                <p><?php echo "<b>Username:</b> " . $username; ?></p>
                <p><?php echo "<b>Email: </b>" . $email; ?></p>
            </div>
            </div>

        </div>
        </div>
        </div>
        <?php include 'footer.php'; ?>
        </div>
</div>
</body>
</html>
<?php
$conn->close();
?>