<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['darkMode'])) {
    $_SESSION['dark_mode'] = filter_var($_GET['darkMode'], FILTER_VALIDATE_BOOLEAN);
}