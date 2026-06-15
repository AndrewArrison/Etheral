<?php
session_start();
include("db.php");
$id = (int)$_SESSION['id'];
$sql = "UPDATE users SET last_seen = now() WHERE id = $id";
mysqli_query($conn, $sql);
?>