<?php
// Start the session if not already started
session_start();

// Include the database connection file
include "db.php";

// Function to update last seen timestamp
function updateLastSeen($conn, $user_id) {
    $update_sql = "UPDATE users SET last_seen = UTC_TIMESTAMP() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Update last seen timestamp
    updateLastSeen($conn, $user_id);
}

// Clear all session data
session_unset();

// Destroy the session
session_destroy();

// Redirect to the homepage
header("Location: /");

// Terminate script execution
exit;
?>
