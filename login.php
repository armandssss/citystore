<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit(json_encode(array('error' => '404')));
}

session_start();
include "db.php";

function verifyPassword($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

$error_message = "";
$success_message = "";
$output = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = $_POST["username_or_email"];
    $password = $_POST["password"];

    $sql = "SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ss", $username_or_email, $username_or_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $username, $email, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION["user_id"] = $id;
                $_SESSION["username"] = $username;

                if ($role === 'admin') {
                    $output['redirect'] = "dashboard.php";
                } else {
                    $output['redirect'] = "http://localhost/";
                }
            } else {
                $output['error'] = "Invalid username/email or password.";
            }
        } else {
            $output['error'] = "Invalid username/email or password.";
        }

        $stmt->close();
    } else {
        $output['error'] = "Error preparing statement: " . $conn->error;
    }
}

$conn->close();

$output['content'] = '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">
    <h2>Sign In</h2>';
if (!empty($output['error'])) {
    $output['content'] .= '<p class="error">' . $output['error'] . '</p>';
}
$output['content'] .= '
    <label for="username_or_email">Username or Email</label>
    <input type="text" id="username_or_email" name="username_or_email">

    <label for="password">Password</label>
    <input type="password" id="password" name="password">

    <input type="submit" value="Sign In">
    <p>Not registered yet? <a href="#" onclick="openRegisterModal()">Register Here</a></p>
    <p><a href="#" onclick="openPasswordResetModal()">Forgot Your Password?</a></p>
</form>';

echo json_encode($output);
?>