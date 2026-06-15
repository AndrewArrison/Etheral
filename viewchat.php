<?php

session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
include "db.php";
$id = (int)$_SESSION['id'];
$sql = "UPDATE users SET last_seen = now() WHERE id = $id";
mysqli_query($conn, $sql);
$sql = "SELECT * FROM users WHERE id != $id";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0)
{
    //TODO continue
} else {
    echo "ERROR";
    die();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="chat.js"></script>
    <script src="tw.js"></script>
    <title>Login Page</title>
</head>

<body class="bg-gray-100 h-screen overflow-hidden">

    <div class="max-w-md mx-auto bg-white h-screen flex flex-col shadow-lg">
        <!-- Top Bar -->
        <div class="bg-blue-600 text-white p-4 flex items-center gap-4 shadow shrink-0 justify-end">
            <button class="text-xl">
                <a href="logout.php">Logout</a>
            </button>
            <!-- <button class="text-xl">
                ⋮
            </button> -->

        </div>
        <!-- Chat List -->
        <div class="flex-1 overflow-y-auto">
            <?php
                while ($row = $result->fetch_assoc())
                {?>
                    <a href="#" onclick="submitPost(event, {id:<?= $row['id'] ?>})" class="flex items-center gap-4 p-4 hover:bg-gray-100 border-b transition">
                        <img
                            src="profilepics/<?= $row['profile_image'] ?>"
                            class="w-14 h-14 rounded-full object-cover"
                        >

                        <div class="flex-1 min-w-0">

                            <div class="flex justify-between items-center">

                                <h2 class="font-semibold text-lg truncate">
                                    <?= $row['username'] ?>
                                </h2>

                                <span class="text-sm text-gray-500 last-seen" data-last-seen="<?= htmlspecialchars($row['last_seen'], ENT_QUOTES) ?>" >
                                   <?= $row['last_seen'] ?>
                                </span>

                            </div>

                            <p class="text-gray-500 truncate">
                                TODO : Put me
                            </p>

                        </div>

                    </a>
                <?php }
            ?>
        </div>

    </div>

</body>
<script>
    function submitPost(event, data) {
        event.preventDefault();
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "chat.php";
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
            const hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = key;
            hiddenField.value = data[key];
            form.appendChild(hiddenField);
            }
        }
        document.body.appendChild(form);
        form.submit();
    }
    document.querySelectorAll('.last-seen').forEach(span => {
    span.textContent = timeto12(span.dataset.lastSeen);
    });
    setInterval(() => {
    fetch("lastseen.php");
    }, 45000);
</script>

</html>