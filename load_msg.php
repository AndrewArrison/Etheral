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

while($row = $result->fetch_assoc())
{
    if($row['sender_id'] != $user)
    {
        // echo '<p>hi</p>'.$row['id'].''.$row['message_text'];
        $response = [
        'msg_id' => $row['id'],
        'msg' => $row['message_text']
        ];

            // Output as a single JSON string
        echo json_encode($response);
    }

}