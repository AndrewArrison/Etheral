<?php
session_start();
include "db.php";

if(!isset($_SESSION["user"])) {
    header("Location: login.php");
    die();
}

$username = $_POST['name'];
$sql = "SELECT * FROM users WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "s",
    $username
);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
header('Content-Type: application/json');
echo json_encode([
    'private_key' => $row['encrypted_private_key'],
    'salt' => $row['kdf_salt'],
    'nonce' => $row['private_key_nonce']
]);
// header("Location: chat.php");
// die();
?>