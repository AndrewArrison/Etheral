<?php

session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_FILES['file']))
{
    $receiver_id = $_POST['receiver_id'] ?? '';
    $user = (int)$_SESSION['id'];
    $msg = trim($_POST['message'] ?? "");

    $sql = "INSERT INTO `messages`(`sender_id`, `receiver_id`, `message_text`) VALUES (?,?,?)";
    $statement = $conn->prepare($sql);
    $statement->bind_param("iis", $user, $receiver_id, $msg);
    $statement->execute();
    exit;
} else {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $file_name = time() . "_" . basename($_FILES["file"]["name"]);
    $targetFile = $targetDir . $file_name;
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
        // echo "File uploaded successfully: " . $targetFile;
    } else {
        // echo "Error: Could not move uploaded file.";
    }
    $receiver_id = $_POST['receiver_id'] ?? '';
    $user = (int)$_SESSION['id'];
    $msg = trim($_POST['message'] ?? "");
    
    $file_type = $_POST['message_type'];
    $file_size = $_POST['file_size'];
    $sql = "INSERT INTO `messages`(`sender_id`, `receiver_id`, `message_text`, `message_type`, `file_name`, `file_size`) VALUES (?,?,?,?,?,?)";
    $statement = $conn->prepare($sql);
    $statement->bind_param("iisssd", $user, $receiver_id, $msg, $file_type, $file_name, $file_size);
    $statement->execute();
    exit;
}
exit;
