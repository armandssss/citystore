<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit(json_encode(array('error' => '404')));
}

require '\PHPMailer-master\PHPMailer-master\src\Exception.php';
require '\PHPMailer-master\PHPMailer-master\src\PHPMailer.php';
require '\PHPMailer-master\PHPMailer-master\src\SMTP.php';
require '\db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';
$success_message = '';
$output = array(); // Initialize output array

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } else {
        $check_email_query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_email_query);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $new_password = generateRandomPassword();
                $password_updated = true;

                if ($password_updated) {
                    $subject = "Password Reset";
                    $message = "Your new password is: " . $new_password;

                    $smtp_username = 'citystore.help@gmail.com';
                    $smtp_password = 'uvub qfqf vqis ybch';
                    $smtp_host = 'smtp.gmail.com';
                    $smtp_port = 587;

                    $mail = new PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->Host = $smtp_host;
                        $mail->SMTPAuth = true;
                        $mail->Username = $smtp_username;
                        $mail->Password = $smtp_password;
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = $smtp_port;

                        $mail->setFrom($smtp_username);
                        $mail->addAddress($email);

                        $mail->isHTML(true);
                        $mail->Subject = $subject;
                        $mail->Body = $message;

                        $mail->send();

                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_query = "UPDATE users SET password = ? WHERE email = ?";
                        $stmtUpdate = $conn->prepare($update_query);

                        if ($stmtUpdate) {
                            $stmtUpdate->bind_param("ss", $hashed_password, $email);
                            $stmtUpdate->execute();
                            $stmtUpdate->close();
                            $success_message = "Email has been sent. Password updated successfully!";
                        } else {
                            $error_message = "Error updating password: " . $conn->error;
                        }

                    } catch (Exception $e) {
                        $error_message = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                } else {
                    $error_message = "Failed to update the password. Please try again later.";
                }
            } else {
                $error_message = "Email isn't registered";
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    }
}

function generateRandomPassword() {
    $length = 10;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

// Prepare JSON response
$output = array(
    'error' => $error_message,
    'success_message' => $success_message,
    'content' => '<form id="passwordResetForm" method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">
    <h2>Password Reset</h2>'
);
if (!empty($error_message)) {
    $output['content'] .= '<span class="error">' . $error_message . '</span>';
} elseif (!empty($success_message)) {
    $output['content'] .= '<span class="success-message">' . $success_message . '</span>';
}
$output['content'] .= '
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>
    <input type="submit" value="Reset Password">
    <p>Go back to <a href="#" onclick="openLoginModal()">Sign In page</a></p>
</form>';

// Output response as JSON
header('Content-Type: application/json');
echo json_encode($output);
?>