<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_GET['receiver_id'];

    $sql = "SELECT * FROM messages 
            WHERE (sender_id = '$sender_id' AND receiver_id = '$receiver_id') 
            OR (sender_id = '$receiver_id' AND receiver_id = '$sender_id') 
            ORDER BY sent_at ASC";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        header('Content-Type: application/json');
        echo json_encode($messages);
    } else {
        echo "No messages";
    }
}
?>