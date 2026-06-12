<?php
include "db.php";
session_start();
$userid = $_SESSION["id"];
$sql = "UPDATE users SET last_seen = now() WHERE id = $userid";
mysqli_query($conn, $sql);
$conn->close();
session_destroy();
header("Location: login.php");
exit();
?>