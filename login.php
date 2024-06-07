<?php
date_default_timezone_set('UTC');

// Check if the request is AJAX, if not, respond with a 403 error and exit
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit(json_encode(array('error' => '403')));
}

// Start session
session_start();

// Include database connection file
include "db.php";

// Function to verify password
function verifyPassword($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

$error_message = ""; // Initialize error message
$success_message = ""; // Initialize success message
$output = array(); // Initialize output array

// Check if request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = $_POST["username_or_email"];
    $password = $_POST["password"];

    // SQL query to check username or email
    $sql = "SELECT id, username, email, password, role, last_seen FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters and execute query
        $stmt->bind_param("ss", $username_or_email, $username_or_email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user is found
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id = $row['id'];
            $username = $row['username'];
            $hashed_password = $row['password'];
            $role = $row['role'];
            $last_seen = $row['last_seen'];

            // Check if password is correct
            if (verifyPassword($password, $hashed_password)) {
                // Update last_seen timestamp to current time in UTC
                $update_last_seen_sql = "UPDATE users SET last_seen = UTC_TIMESTAMP() WHERE id = ?";
                $update_stmt = $conn->prepare($update_last_seen_sql);
                $update_stmt->bind_param("i", $id);
                $update_stmt->execute();
                $update_stmt->close();

                // Store user ID and username in session
                $_SESSION["user_id"] = $id;
                $_SESSION["username"] = $username;

                // Redirect user based on role
                if ($role === 'admin') {
                    $output['redirect'] = "dashboard.php";
                } else {
                    $output['redirect'] = "/";
                }

                // Set status message to "Online"
                $output['status'] = "Online";
            } else {
                $output['error'] = "Invalid username/email or password.";
            }
        } else {
            $output['error'] = "Invalid username/email or password.";
        }

        // Close statement
        $stmt->close();
    } else {
        $output['error'] = "Error preparing statement: " . $conn->error;
    }
}

// Close database connection
$conn->close();

// Prepare HTML form and output content
$output['content'] = '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">
    <!-- Login form -->
    <h2>Sign In</h2>';
if (!empty($output['error'])) {
    $output['content'] .= '<p class="error">' . $output['error'] . '</p>';
}
$output['content'] .= '
    <!-- Input field for username or email -->
    <label for="username_or_email">Username or Email</label>
    <input type="text" id="username_or_email" name="username_or_email">

    <!-- Input field for password -->
    <label for="password">Password</label>
    <input type="password" id="password" name="password">

    <!-- Login button -->
    <input type="submit" value="Sign In">

    <!-- Link to registration modal -->
    <p>Not registered yet? <a href="#" onclick="openRegisterModal()">Register Here</a></p>

    <!-- Link to password reset modal -->
    <p><a href="#" onclick="openPasswordResetModal()">Forgot Your Password?</a></p>
</form>';

// Send JSON output
echo json_encode($output);
?>
