<?php
include "db.php";
session_start();
if(!isset($_SESSION['id']))
{
    header("Location: login.php");
    die;
}
$userid = $_SESSION["id"];
$sql = "UPDATE users SET last_seen = now(), is_active = '0' WHERE id = $userid";
mysqli_query($conn, $sql);
$conn->close();
session_destroy();
header("Location: login.php");
exit();
?>