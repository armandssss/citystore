<?php
session_start();
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

if (isset($_POST['new_username'])) {
    $new_username = $_POST['new_username'];

    if (strlen($new_username) < 4 || strlen($new_username) > 50) {
        $error_message = "Username length must be between 4 and 50 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $new_username)) {
        $error_message = "Username can only contain letters, numbers, and underscores.";
    } else {
        $check_username_query = "SELECT id FROM users WHERE username = ? AND id != ?";
        $stmt_check_username = $conn->prepare($check_username_query);
        $stmt_check_username->bind_param("si", $new_username, $user_id);
        $stmt_check_username->execute();
        $check_username_result = $stmt_check_username->get_result();

        if ($check_username_result && $check_username_result->num_rows > 0) {
            $error_message = "Username already in use. Please choose a different one.";
        } else {
            $update_username_query = "UPDATE users SET username = ? WHERE id = ?";
            $stmt_update_username = $conn->prepare($update_username_query);
            $stmt_update_username->bind_param("si", $new_username, $user_id);
            if ($stmt_update_username->execute()) {
                $username = $new_username;
            } else {
                $error_message = "Failed to update username.";
            }
        }
        $stmt_check_username->close();
    }
}

if (isset($_POST['new_email'])) {
    $new_email = $_POST['new_email'];

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        $check_email_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt_check_email = $conn->prepare($check_email_query);
        $stmt_check_email->bind_param("si", $new_email, $user_id);
        $stmt_check_email->execute();
        $check_email_result = $stmt_check_email->get_result();

        if ($check_email_result && $check_email_result->num_rows > 0) {
            $error_message = "Email address already in use. Please choose a different one.";
        } else {
            $update_email_query = "UPDATE users SET email = ? WHERE id = ?";
            $stmt_update_email = $conn->prepare($update_email_query);
            $stmt_update_email->bind_param("si", $new_email, $user_id);
            if ($stmt_update_email->execute()) {
                $email = $new_email;
            } else {
                $error_message = "Failed to update email.";
            }
            $stmt_update_email->close();
        }
        $stmt_check_email->close();
    }
}

if (isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'];

    if (strlen($new_password) < 5) {
        $error_message = "Password must be at least 5 characters long.";
    } elseif (!preg_match('/^[a-zA-Z0-9!@#$%^&*]+$/', $new_password)) {
        $error_message = "Password can only contain letters, numbers, and the following symbols: !@#$%^&*";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_password_query = "UPDATE users SET password = ? WHERE id = ?";
        $stmt_update_password = $conn->prepare($update_password_query);
        $stmt_update_password->bind_param("si", $hashed_password, $user_id);
        if (!$stmt_update_password->execute()) {
            $error_message = "Failed to update password.";
        }
        $stmt_update_password->close();
    }
}

$cart_count_query = "SELECT COUNT(*) AS cart_count FROM cart WHERE user_id = ?";
$stmt_cart_count = $conn->prepare($cart_count_query);
$stmt_cart_count->bind_param("i", $user_id);
$stmt_cart_count->execute();
$cart_count_result = $stmt_cart_count->get_result();

if ($cart_count_result && $cart_count_result->num_rows > 0) {
    $cart_data = $cart_count_result->fetch_assoc();
    $cart_count = $cart_data['cart_count'];
} else {
    $cart_count = 0;
}
$stmt_cart_count->close();
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
    <title>Settings - CityStore</title>
</head>
<body>
<div class="wrapper">
    <?php include 'header.php'; ?> 
    <div class="container">
    <div class="main-page-wrapper">
        <div class="user-center">
        <div class="settings-buttons">
            <a class="settings-btn" href="users_profile.php">Profile</a>
            <a class="settings-btn" href="orders.php">Orders</a>
            <a class="settings-btn active" href="settings.php">Settings</a>
        </div>
            <div class="user-profile">
            <h1>My Settings</h1>
                <?php if (!empty($error_message)): ?>
                    <p class="error"><?php echo $error_message; ?></p>
                <?php endif; ?>

                <form method="post" class="user-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <label for="new_username">Change Username:</label>
                    <input type="text" id="new_username" name="new_username" value="<?php echo $username; ?>"><br>
                    <label for="new_email">Change Email:</label>
                    <input type="text" id="new_email" name="new_email" value="<?php echo $email; ?>"><br>
                    <label for="new_password">Change Password:</label>
                    <input type="password" id="new_password" name="new_password" value="*********"><br>
                    <input type="submit" name="update" value="Update">
                </form>
            </div>
        </div>
    </div>
    </div>
    <?php include 'footer.php'; ?>
</div>
</body>
</html>
<?php
$conn->close();
?>
