<?php
// Include the database connection file
include "db.php";

// Define inactivity threshold
define('INACTIVITY_THRESHOLD', 300); // 5 minutes (300 seconds)

// Function to update last seen timestamp
function updateLastSeen($conn, $user_id) {
    $update_sql = "UPDATE users SET last_seen = UTC_TIMESTAMP() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Update last seen timestamp for logged-in users
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    updateLastSeen($conn, $user_id);
}
?>
