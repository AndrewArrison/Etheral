<?php
$servername = "localhost";
$username = "root";     // Default XAMPP/WAMP user
$password = "";         // Default XAMPP password is empty
$dbname = "Chatapp";
// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
if($conn)
{
    // echo "<script>alert('CONNECTED');</script>";
}
date_default_timezone_set('Asia/Kolkata');
function getActiveStatus(DateInterval $delta): string
{
    $d = (int)$delta->format('%d');
    $h = (int)$delta->format('%h');
    if((int)$delta->format('%i') == 0)
    {
        return "Online";
    }
    if ($d > 0)
    {
        return min($d, 23) . 'd ago';
    }
    if ($h > 0)
    {
        return min($h, 23) . 'h ago';
    }
    return max(1, (int)$delta->format('%i')) . 'm ago';
}
?>