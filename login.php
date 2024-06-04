<?php
// Pārbauda, vai pieprasījums ir AJAX, ja nē, atbild ar 403 kļūdu un iziet
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit(json_encode(array('error' => '404')));
}

// Sāk sesiju
session_start();
// Iekļauj datubāzes savienojuma failu
include "db.php";

// Funkcija paroles verifikācijai
function verifyPassword($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

$error_message = ""; // Inicializē kļūdu ziņojumu
$success_message = ""; // Inicializē veiksmes ziņojumu
$output = array(); // Inicializē izvada masīvu

// Pārbauda, vai pieprasījuma metode ir POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = $_POST["username_or_email"];
    $password = $_POST["password"];

    // SQL vaicājums, lai pārbaudītu lietotājvārdu vai e-pastu
    $sql = "SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Piesaista parametrus un izpilda vaicājumu
        $stmt->bind_param("ss", $username_or_email, $username_or_email);
        $stmt->execute();
        $stmt->store_result();

        // Pārbauda, vai ir atrasts kāds lietotājs
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $username, $email, $hashed_password, $role);
            $stmt->fetch();

            // Pārbauda, vai parole ir pareiza
            if (password_verify($password, $hashed_password)) {
                // Ja parole ir pareiza, saglabā lietotāja ID un lietotājvārdu sesijā
                $_SESSION["user_id"] = $id;
                $_SESSION["username"] = $username;

                // Pārbauda, vai lietotājs ir administrators
                if ($role === 'admin') {
                    $output['redirect'] = "dashboard.php";
                } else {
                    $output['redirect'] = "https://citystore.kvd.lv/";
                }
            } else {
                $output['error'] = "Invalid username/email or password.";
            }
        } else {
            $output['error'] = "Invalid username/email or password.";
        }

        // Aizver paziņojumu
        $stmt->close();
    } else {
        $output['error'] = "Error preparing statement: " . $conn->error;
    }
}

// Aizver datubāzes savienojumu
$conn->close();

// Sagatavo HTML formu un izvades saturu
$output['content'] = '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">
    <!-- Pieslēgšanās forma -->
    <h2>Sign In</h2>';
if (!empty($output['error'])) {
    $output['content'] .= '<p class="error">' . $output['error'] . '</p>';
}
$output['content'] .= '
    <!-- Ievadlauks, kur lietotājs var ievadīt savu lietotājvārdu vai e-pasta adresi -->
    <label for="username_or_email">Username or Email</label>
    <input type="text" id="username_or_email" name="username_or_email">

    <!-- Ievadlauks, kur lietotājs var ievadīt savu paroli -->
    <label for="password">Password</label>
    <input type="password" id="password" name="password">

    <!-- Pieslēgšanās poga -->
    <input type="submit" value="Sign In">

    <!-- Poga uz reģistrācijas logu -->
    <p>Not registered yet? <a href="#" onclick="openRegisterModal()">Register Here</a></p>

    <!-- Poga uz paroles atiestatīšanas logu -->
    <p><a href="#" onclick="openPasswordResetModal()">Forgot Your Password?</a></p>
</form>';

// Nosūta JSON izvadi
echo json_encode($output);
?>