<?php

session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
include "db.php";
$chat = $_GET['chat'];
$user = $_SESSION['id'];
$last_message_id = $_GET['last_message_id'];

$sql = "
SELECT *
FROM messages
WHERE
(
    (sender_id = ? AND receiver_id = ?)
    OR
    (sender_id = ? AND receiver_id = ?)
)
AND id > ?
ORDER BY created_at ASC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iiiii",
    $user,
    $chat,
    $chat,
    $user,
    $last_message_id
);

$stmt->execute();

$result = $stmt->get_result();


// ... Your database connection and query logic ...
// Make absolutely sure there are NO echo, print, or <script> tags above this line!

$messages = []; // Create a master array

while ($row = $result->fetch_assoc()) {
    // Push each message object into our master array
    $messages[] = [
        'msg_id' => $row['id'],
        'sender' => $row['sender_id'],
        'time' => $row['created_at'],
        'msg'    => $row['message_text']
    ];
}
// Output the entire collection as ONE valid JSON array
echo json_encode($messages);
exit;
