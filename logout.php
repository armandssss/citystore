<?php
// Start the session if not already started
session_start();

// Include the database connection file
include "db.php";

// Clear all session data
session_unset();

// Destroy the session
session_destroy();

// Redirect to the homepage
header("Location: /");

// Terminate script execution
exit;
?>
