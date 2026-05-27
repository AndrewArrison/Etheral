<?php
$servername = "localhost";
$username = "root";     // Default XAMPP/WAMP user
$password = "";         // Default XAMPP password is empty
$dbname = "Chatapp";
// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
if($conn)
{
    echo "<script>alert('CONNECTED');</script>";
}
?>