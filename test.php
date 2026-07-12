<?php

session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

include "db.php";
$chat = $_GET['chat'];
$user = $_SESSION['id'];

// Get parameters (using null coalescing operator to default to null if missing)
$last_message_id = isset($_GET['last_message_id']) && $_GET['last_message_id'] !== '' ? (int)$_GET['last_message_id'] : null;
$before_message_id = isset($_GET['before_message_id']) && $_GET['before_message_id'] !== '' ? (int)$_GET['before_message_id'] : null;

// Base SQL query setup
$sql = "
SELECT *
FROM messages
WHERE 
(
    (sender_id = ? AND receiver_id = ?)
    OR
    (sender_id = ? AND receiver_id = ?)
)
";

// 1. CHAT HISTORY (Scroll Up): Load 10 messages BEFORE a specific ID
if ($before_message_id !== null) {
    $sql .= " AND id < ? ORDER BY id DESC LIMIT 10";
    $bind_id = $before_message_id;
    $should_reverse = true;

// 2. LIVE POLLING: Load all NEW messages AFTER a specific ID
} elseif ($last_message_id !== null) {
    $sql .= " AND id > ? ORDER BY id ASC";
    $bind_id = $last_message_id;
    $should_reverse = false;

// 3. FIRST RUN / INITIAL LOAD: Load the LATEST 10 messages
} else {
    $sql .= " ORDER BY id DESC LIMIT 10";
    $bind_id = null;
    $should_reverse = true;
}

$stmt = $conn->prepare($sql);

// Dynamically bind parameters based on the query structure
if ($bind_id !== null) {
    $stmt->bind_param("iiiii", $user, $chat, $chat, $user, $bind_id);
} else {
    $stmt->bind_param("iiii", $user, $chat, $chat, $user);
}

$stmt->execute();
$result = $stmt->get_result();

$messages = []; 

while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'msg_id' => $row['id'],
        'type' => $row['message_type'] ?? "",
        'filename_store' => $row['file_name'] ?? "",
        'filename' => $row['org_file_name'] ?? "",
        'sender' => $row['sender_id'] ?? "",
        'time' => $row['created_at'] ?? "",
        'msg'    => $row['message_text'] ?? "",
        'filesize' => $row['file_size'] ?? "",
    ];
}

// If we fetched using DESC (newest first), reverse it for chronological UI display
if ($should_reverse) {
    $messages = array_reverse($messages);
}

// Output as valid JSON
echo json_encode($messages);
exit;