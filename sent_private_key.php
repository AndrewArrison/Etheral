<?php
session_start();
include "db.php";

if(!isset($_SESSION["user"])) {
    header("Location: login.php");
    die();
}

$encrypted_private_key = $_POST['key'];
$private_key_nonce = $_POST['nonce'];
$kdf_salt = $_POST['salt'];
$username = $_POST['name'];
$pk = $_POST['pk'];
$sql = "UPDATE users
        SET public_key = ?,
            encrypted_private_key = ?,
            private_key_nonce = ?,
            kdf_salt = ?,
            key_initialized  = 1
        WHERE username = ? AND key_initialized = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssss",
    $pk,
    $encrypted_private_key,
    $private_key_nonce,
    $kdf_salt,
    $username
);
$stmt->execute();
die();
?>