<?php
// Pārbauda, vai pieprasījums ir AJAX, ja nē, atbild ar 403 kļūdu un iziet
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit(json_encode(array('error' => '404')));
}

// Sāk sesiju un iznīcina jebkuru esošo sesiju
session_start();
session_destroy();

// Sāk jaunu sesiju
session_start();

// Iekļauj datubāzes savienojuma failu
include "db.php";

// Funkcija paroles hash izveidošanai
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Funkcija e-pasta validācijai
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

$error_message = ""; // Inicializē kļūdu ziņojumu
$output = array(); // Inicializē izvada masīvu

// Pārbauda, vai pieprasījuma metode ir POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Validācijas
    if (strlen($username) < 4 || strlen($username) > 15) {
        $error_message = "Username length must be between 4 and 15 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_message = "Username can only contain letters, numbers, and underscores.";
    } elseif (!isValidEmail($email)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($password) < 5) {
        $error_message = "Password must be at least 5 characters long.";
    } elseif (!preg_match('/^[a-zA-Z0-9!@#$%^&*]+$/', $password)) {
        $error_message = "Password can only contain letters, numbers, and the following symbols: !@#$%^&*";
    } else {
        // Pārbauda, vai lietotājvārds vai e-pasts jau pastāv datubāzē
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

        // Ja nav kļūdu, turpina ar reģistrāciju
        if (empty($error_message)) {
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
                            // Reģistrācija veiksmīga
                            $output['success'] = "Account has been registered successfully.";
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

// Sagatavo HTML formu un izvades saturu
$output['content'] = '
<!-- Ievades forma reģistrācijai -->
<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">
    <h2>Register</h2>';
if (!empty($output['error'])) {
    $output['content'] .= '<span class="error">' . $output['error'] . '</span>';
}
$output['content'] .= '
    <!-- Šis ir ievadlauks, kur lietotājs var ievadīt savu e-pasta adresi. -->
    <label for="email">Email</label>
    <input type="text" name="email" autocomplete="username">

    <!-- Šis ir ievadlauks, kur lietotājs var ievadīt savu lietotājvārdu. -->
    <label for="username">Username</label>
    <input type="text" name="username" autocomplete="username">

    <!-- Šis ir ievadlauks, kur lietotājs var ievadīt savu paroli. -->
    <label for="password">Password</label>
    <input type="password" name="password" autocomplete="username">

<!-- Ievadlauku ievades poga -->    
    <input type="submit" value="Register">
<!-- Pieslēgšanās poga, kura atver pieslēgšanās logu -->  
    <p>Already registered? <a href="#" onclick="openLoginModal()">Sign In Here</a></p>
</form>';

// Nosūta JSON izvadi
echo json_encode($output);
?>