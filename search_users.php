<?php
session_start();
if (!isset($_SESSION["user"])) {
    http_response_code(401);
    exit();
}
include "db.php";

$me = (int)$_SESSION['id'];
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode([]);
    exit();
}

$sql = "SELECT id, username, profile_image FROM users
        WHERE username LIKE ? AND id != ?
        LIMIT 10";
$stmt = $conn->prepare($sql);
$likeQ = "%" . $q . "%";
$stmt->bind_param("si", $likeQ, $me);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'id' => $row['id'],
        'username' => $row['username'],
        'profile_image' => $row['profile_image'],
    ];
}
echo json_encode($users);
exit;