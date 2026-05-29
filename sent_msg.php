<?php

session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
include "db.php";
// Read the raw POST data
$json_data = file_get_contents('php://input');

// Decode JSON into a PHP associative array
$data = json_decode($json_data, true);
$chat = $data['chat'];
$user = $_SESSION['id'];
$msg = $data['message'];


$sql = "INSERT INTO `messages`(`sender_id`, `receiver_id`, `message_text`) 
VALUES ('$user','$chat','$msg')";

mysqli_execute_query($conn, $sql);

exit;
