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

$messages = []; 

while ($row = $result->fetch_assoc()) {
    // if ($row['message_type'] == "text")
    // {
    //     $messages[] = [
    //         'msg_id' => $row['id'],
    //         'type' => $row['message_type'],
    //         'sender' => $row['sender_id'],
    //         'time' => $row['created_at'],
    //         'msg'    => $row['message_text']
    //     ];
    // }
    // elseif($row['message_type'] == "file")
    // {
    //     $messages[] = [
    //         'msg_id' => $row['id'],
    //         'type' => $row['message_type'],
    //         'filename' => $row['file_name'],
    //         'sender' => $row['sender_id'],
    //         'time' => $row['created_at'],
    //         'msg'    => $row['message_text'],
    //         'filesize' => $row['file_size'] ?? "",
    //     ];
    // } 
    // else {
    //     $messages[] = [
    //         'msg_id' => $row['id'],
    //         'type' => $row['message_type'],
    //         'filename' => $row['file_name'],
    //         'sender' => $row['sender_id'],
    //         'time' => $row['created_at'],
    //         'msg'    => $row['message_text']
    //     ];
    // }
    $messages[] = [
            'msg_id' => $row['id'],
            'type' => $row['message_type'] ?? "",
            'filename' => $row['file_name'] ?? "",
            'sender' => $row['sender_id'] ?? "",
            'time' => $row['created_at'] ?? "",
            'msg'    => $row['message_text'] ?? "",
            'filesize' => $row['file_size'] ?? "",
    ];
}
// Output the entire collection as ONE valid JSON array
echo json_encode($messages);
exit;
