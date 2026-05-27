<?php

session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
include "db.php";
$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0)
{
    //contine
} else {
    die();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="tw.js"></script>
    <title>Login Page</title>
</head>

<body class="bg-gray-100 h-screen overflow-hidden">

    <div class="max-w-md mx-auto bg-white h-screen flex flex-col shadow-lg">

        <!-- Chat List -->
        <div class="flex-1 overflow-y-auto">
            <?php
                while ($row = $result->fetch_assoc())
                {?>
                    <a href="chat.php?chat=<?= $row['id']; ?>" class="flex items-center gap-4 p-4 hover:bg-gray-100 border-b transition">
                        <img
                            src="https://i.pravatar.cc/150?img=1"
                            class="w-14 h-14 rounded-full object-cover"
                        >

                        <div class="flex-1 min-w-0">

                            <div class="flex justify-between items-center">

                                <h2 class="font-semibold text-lg truncate">
                                    <?= $row['username'] ?>
                                </h2>

                                <span class="text-sm text-gray-500">
                                    10:45 AM
                                </span>

                            </div>

                            <p class="text-gray-500 truncate">
                                Hey bro, did you finish the project?
                            </p>

                        </div>

                    </a>
                <?php }
            ?>
        </div>

    </div>

</body>

</html>