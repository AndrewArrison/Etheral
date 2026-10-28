<?php

session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
include "db.php";
$chat = $_GET['chat'];
$user = $_SESSION['id'];

$last_message_id = isset($_GET['last_message_id']) && $_GET['last_message_id'] !== '' ? (int)$_GET['last_message_id'] : null;
$before_message_id = isset($_GET['before_message_id']) && $_GET['before_message_id'] !== '' ? (int)$_GET['before_message_id'] : null;
$bind_id = null;
$should_reverse = false;

$sql = "
SELECT *
FROM messages
WHERE
(
    (sender_id = ? AND receiver_id = ?)
    OR
    (sender_id = ? AND receiver_id = ?)
)";

if ($before_message_id !== null)
{
    $sql .= " AND id < ? ORDER BY id DESC LIMIT 10";
    $bind_id = $before_message_id;
    $should_reverse = false;
} elseif($last_message_id !== null)
{
    $sql .= " AND id > ? ORDER BY id ASC";
    $bind_id = $last_message_id;
    $should_reverse = false;
} else {
    $sql .= " ORDER BY id DESC LIMIT 10";
    $bind_id = null;
    $should_reverse = true;
}

$stmt = $conn->prepare($sql);
if ($bind_id !== null)
{
    $stmt->bind_param(
        "iiiii",
        $user,
        $chat,
        $chat,
        $user,
        $bind_id
    );
} else {
    $stmt->bind_param(
    "iiii",
    $user,
    $chat,
    $chat,
    $user
);
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
            'is_old' => ($before_message_id !== null) ? true:false,
            'msg_date' => $row['created_at'],
    ];
}
if ($should_reverse)
{
    $messages = array_reverse($messages);
}

echo json_encode($messages);
exit;
