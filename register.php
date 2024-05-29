<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit(json_encode(array('error' => '404')));
}

session_start();
session_destroy();

// Start a new session
session_start();

include "db.php";

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

$error_message = ""; // Initialize error message
$output = array(); // Initialize output array

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Validations
    if (strlen($username) < 4 || strlen($username) > 50) {
        $error_message = "Username length must be between 4 and 50 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_message = "Username can only contain letters, numbers, and underscores.";
    } elseif (!isValidEmail($email)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($password) < 5) {
        $error_message = "Password must be at least 5 characters long.";
    } elseif (!preg_match('/^[a-zA-Z0-9!@#$%^&*]+$/', $password)) {
        $error_message = "Password can only contain letters, numbers, and the following symbols: !@#$%^&*";
    } else {
        $checkExisting = "SELECT username, email FROM users WHERE username = ? OR email = ?";
        $stmtCheck = $conn->prepare($checkExisting);

        if ($stmtCheck) {
            $stmtCheck->bind_param("ss", $username, $email);
            $stmtCheck->execute();
            $stmtCheck->store_result();

            if ($stmtCheck->num_rows > 0) {
                $stmtCheck->bind_result($existingUsername, $existingEmail);

                while ($stmtCheck->fetch()) {
                    if ($existingUsername === $username) {
                        $error_message = "Username already exists.";
                    }
                    if ($existingEmail === $email) {
                        $error_message = "Email already exists.";
                    }
                }
            }
            $stmtCheck->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }

        if (empty($error_message)) { // If no error, proceed with registration
            $hashed_password = hashPassword($password);

            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("sss", $username, $email, $hashed_password);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $new_user_id = $stmt->insert_id;
                    $default_profile_pic = 'https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg';
                    $update_default_pic_query = "UPDATE users SET profile_picture = ? WHERE id = ?";
                    $stmtDefaultPic = $conn->prepare($update_default_pic_query);

                    if ($stmtDefaultPic) {
                        $stmtDefaultPic->bind_param("si", $default_profile_pic, $new_user_id);
                        $stmtDefaultPic->execute();

                        if ($stmtDefaultPic->affected_rows > 0) {
                            // Registration successful
                            $output['success'] = "Registration successful. Sign in to continue shopping.";
                        } else {
                            $error_message = "Error setting default profile picture.";
                        }                        

                        $stmtDefaultPic->close();
                    } else {
                        $error_message = "Error preparing statement for profile picture: " . $conn->error;
                    }
                } else {
                    $error_message = "Error registering user: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $error_message = "Error preparing statement for user registration: " . $conn->error;
            }
        }
    }
}

$conn->close();

$output['error'] = $error_message;

// Add content to the output
$output['content'] = '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">
    <h2>Register</h2>';
if (!empty($output['error'])) {
    $output['content'] .= '<span class="error">' . $output['error'] . '</span>';
}
$output['content'] .= '
    <label for="email">Email</label>
    <input type="text" name="email">

    <label for="username">Username</label>
    <input type="text" name="username">


    <label for="password">Password</label>
    <input type="password" name="password">

    <input type="submit" value="Register">
    <p>Already registered? <a href="#" onclick="openLoginModal()">Sign In Here</a></p>
</form>';

echo json_encode($output);
?>