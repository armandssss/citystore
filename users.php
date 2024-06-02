<?php
include 'db.php';
session_start();

if (!(isset($_SESSION['user_id']) && $_SESSION['user_id'])) {
    header("Location: /");
    exit();
}
$user_id = $_SESSION['user_id'];
$check_admin_query = "SELECT role FROM users WHERE id = ?";
$check_admin_stmt = $conn->prepare($check_admin_query);
$check_admin_stmt->bind_param("i", $user_id);
$check_admin_stmt->execute();
$check_admin_result = $check_admin_stmt->get_result();

$is_admin = false;

if ($check_admin_result->num_rows > 0) {
    $row = $check_admin_result->fetch_assoc();
    $is_admin = ($row['role'] === 'admin');
} else {
    echo "Failed to fetch user role for user ID: $user_id";
}

if (!$is_admin) {
    header("Location: /");
    exit();
}

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $success_message = "User deleted successfully.";
    } else {
        $error_message = "Error deleting user.";
    }
    $stmt->close();
}

$user_query = "SELECT id, username, email FROM users";
$user_result = $conn->query($user_query);

if (!$user_result) {
    die("Query Failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-<correct_hash_here>" crossorigin="anonymous">
    <title>User Management</title>
</head>
<body>
    <div class="wrapper">
        <?php include 'header.php'; ?>
        <div class="container">
            <h1>Users</h1>
            <?php if (isset($success_message)): ?>
                <p class="success-message"><?php echo $success_message; ?></p>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($user_result->num_rows > 0): ?>
                        <?php while ($user = $user_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['username']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <a href="users.php?delete=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');" class="action-btn delete-btn"><i class="fas fa-trash-alt"></i> Delete</a>
                                </td>
                            </tr>
                            <?php echo "<!-- Debug: Fetched user: " . $user['username'] . " -->"; ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <footer>
            <?php include 'footer.php'; ?>
        </footer>
    </div>
</body>
</html>