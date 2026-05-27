<?php

session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
include "db.php";
$chatid = $_GET['chat'];
$sql = "SELECT * FROM users WHERE id = $chatid";
$result = mysqli_query($conn, $sql);
$result = $result->fetch_assoc();
$chat = $result['username'];
$user = $_SESSION['id'];
$sql =  "
SELECT *
FROM messages
WHERE
    (sender_id = ? AND receiver_id = ?)
    OR
    (sender_id = ? AND receiver_id = ?)
ORDER BY created_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user, $chatid, $chatid, $user);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>

    <script src="https://cdn.tailwindcss.com"></script>

    <title>Responsive Chat</title>

    <style>
        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        body {
            margin: 0;
        }

        /* Better mobile keyboard handling */
        .chat-wrapper {
            height: 100dvh;
        }
    </style>
</head>

<body class="bg-gray-100">

    <!-- Main Chat -->
    <div class="chat-wrapper max-w-md mx-auto bg-white flex flex-col">

        <!-- Top Bar -->
        <div class="bg-blue-600 text-white p-4 flex items-center gap-4 shadow shrink-0">

            <button class="text-2xl">
                ←
            </button>

            <img
                src="https://i.pravatar.cc/150?img=12"
                class="w-12 h-12 rounded-full object-cover"
            >

            <div class="flex-1">
                <h1 class="font-semibold text-lg">
                    <?= $chat ?>
                </h1>

                <p class="text-sm text-blue-100">
                    Online
                </p>
            </div>

            <button class="text-xl">
                📞
            </button>

            <button class="text-xl">
                ⋮
            </button>

        </div>

        <!-- Messages -->
        <div id="chatArea" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-100">

            <!-- Receiver -->
            <div class="flex msg-d" data-id="1">

                <div class="bg-white px-4 py-3 rounded-2xl shadow max-w-[75%]">

                    <p>
                        Hey, this should behave like WhatsApp now.
                    </p>

                    <span class="text-xs text-gray-500 block text-right mt-1">
                        10:10 AM
                    </span>

                </div>

            </div>

            <!-- Sender -->
            <div class="flex justify-end msg-d" data-id="2">

                <div class="bg-blue-500 text-white px-4 py-3 rounded-2xl shadow max-w-[75%]">

                    <p>
                        Nice. The input stays fixed now.
                    </p>

                    <span class="text-xs text-blue-100 block text-right mt-1">
                        10:12 AM
                    </span>

                </div>

            </div>


        </div>

        <!-- Bottom Input -->
        <div
            class="bg-white border-t p-3 shrink-0 sticky bottom-0">

            <!-- Selected Files -->
            <div
                id="previewArea"
                class="mb-2 space-y-2"
            ></div>

            <!-- Input Row -->
            <div class="flex items-center gap-2">

                <!-- Upload -->
                <label
                    for="fileUpload"
                    class="bg-gray-100 hover:bg-gray-200 min-w-12 h-12 rounded-full flex items-center justify-center text-2xl cursor-pointer transition"
                >
                    +
                </label>

                <input
                    type="file"
                    id="fileUpload"
                    class="hidden"
                    multiple
                    accept="image/*,.pdf,.doc,.docx,.txt,.zip"
                >

                <!-- Input -->
                <input
                    type="text"
                    placeholder="Type a message"
                    class="flex-1 bg-gray-100 rounded-full px-5 py-3 outline-none"
                >

                <!-- Send -->
                <button
                    class="bg-blue-600 hover:bg-blue-700 text-white min-w-12 h-12 rounded-full text-xl transition"
                >
                    ➤
                </button>

            </div>

        </div>

    </div>

    <script>
        const fileUpload = document.getElementById("fileUpload");
        const previewArea = document.getElementById("previewArea");
        const chatArea = document.getElementById("chatArea");

        // File Preview
        fileUpload.addEventListener("change", () => {

            previewArea.innerHTML = "";

            Array.from(fileUpload.files).forEach(file => {

                const item = document.createElement("div");

                item.className =
                    "bg-gray-100 rounded-lg p-3 flex items-center gap-3";

                let icon = "📄";

                if (file.type.startsWith("image/")) {
                    icon = "🖼️";
                }

                item.innerHTML = `
                    <div class="text-2xl">${icon}</div>

                    <div class="flex-1 overflow-hidden">
                        <h2 class="font-medium truncate">
                            ${file.name}
                        </h2>

                        <p class="text-sm text-gray-500">
                            ${(file.size / 1024 / 1024).toFixed(2)} MB
                        </p>
                    </div>
                `;

                previewArea.appendChild(item);
            });

        });

        // Always stay at bottom
        function scrollToBottom() {
            chatArea.scrollTop = chatArea.scrollHeight;
        }

        scrollToBottom();

        // Mobile keyboard resize fix
        if (window.visualViewport) {

            window.visualViewport.addEventListener("resize", () => {

                document.querySelector(".chat-wrapper").style.height =
                    window.visualViewport.height + "px";

                scrollToBottom();
            });
        }
        let lastMessageId = 0;
        async function getPhpData() {
            try {
                const response = await fetch("load_msg.php?user=<?= $user?>&chat=<?= $chatid?>&last_message_id=" + lastMessageId);
                const data = await response.json(); // Parses the JSON automatically

                // Read them into separate JS buffers
                const buffer1 = data.buffer1;
                const buffer2 = data.buffer2;

                console.log("JS Buffer 1:", buffer1);
                console.log("JS Buffer 2:", buffer2);
                
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        }
        function loadMessages() {
            fetch("load_msg.php?user=<?= $user?>&chat=<?= $chatid?>&last_message_id=" + lastMessageId)

            .then(res => res.text())

            .then(data => {

                if(data.trim() !== "") {
                    if (data.includes("bye")) 
                    {
                        
                    }
                    let chat = document.getElementById("chatArea");

                    chat.insertAdjacentHTML("beforeend", data);

                    let messages = chat.querySelectorAll(".msg-d");

                    // lastMessageId =
                    //     messages[messages.length - 1]
                    //     .dataset.id;
                    lastMessageId = 3;
                    //chat.scrollTop = chat.scrollHeight;
                }
            });
        }
        setInterval(loadMessages, 1000);

    </script>

</body>
</html>