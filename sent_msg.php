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
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    // Original filename (for display)
    $original_name = basename($_FILES["file"]["name"]);
    // Get extension
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    // Generate safe storage filename
    $stored_name = uniqid('', true) . "." . $extension;
    // Full path
    $targetFile = $targetDir . $stored_name;
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
        // Upload successful
    } else {
        die("Upload failed");
    }
    $receiver_id = $_POST['receiver_id'] ?? '';
    $user = (int)$_SESSION['id'];
    $msg = trim($_POST['message'] ?? "");
    
    $file_type = $_POST['message_type'];
    $file_size = $_POST['file_size'];
    $sql = "INSERT INTO `messages`(`sender_id`, `receiver_id`, `message_text`, `message_type`, `file_name`, `file_size`, `org_file_name`) VALUES (?,?,?,?,?,?,?)";
    $statement = $conn->prepare($sql);
    $statement->bind_param("iisssds", $user, $receiver_id, $msg, $file_type, $stored_name, $file_size, $original_name);
    $statement->execute();
    exit;
}
exit;
