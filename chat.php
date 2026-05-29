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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />

    <script src="tw.js"></script>

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

            <a href="viewchat.php">
                <button class="text-2xl">
                    ←
                </button>
            </a>

            <img src="https://i.pravatar.cc/150?img=12" class="w-12 h-12 rounded-full object-cover">

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
        <div class="bg-white border-t p-3 shrink-0 sticky bottom-0">

            <!-- Selected Files -->
            <div id="previewArea" class="mb-2 space-y-2"></div>

            <!-- Input Row -->
            <div class="flex items-center gap-2">

                <!-- Upload -->
                <label for="fileUpload"
                    class="bg-gray-100 hover:bg-gray-200 min-w-12 h-12 rounded-full flex items-center justify-center text-2xl cursor-pointer transition">
                    +
                </label>

                <input type="file" id="fileUpload" class="hidden" multiple accept="image/*,.pdf,.doc,.docx,.txt,.zip">

                <!-- Input -->
                <input type="text" placeholder="Type a message" id="msgInput"
                    class="flex-1 bg-gray-100 rounded-full px-5 py-3 outline-none">

                <!-- Send -->
                <button class="bg-blue-600 hover:bg-blue-700 text-white min-w-12 h-12 rounded-full text-xl transition"
                    id="senderbtn">
                    ➤
                </button>

            </div>

        </div>

    </div>

    <script>
    const fileUpload = document.getElementById("fileUpload");
    const previewArea = document.getElementById("previewArea");
    const chatArea = document.getElementById("chatArea");
    const msgInput = document.getElementById("msgInput");
    const senderbtn = document.getElementById("senderbtn");

    //sent message
    function sentmsg() {
        if (msgInput.value.trim() == '')
        {
            return;
        }
        const userData = {
            message: msgInput.value,
            chat: <?=$chatid;?>
        };

        fetch('sent_msg.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(userData) // Convert object to JSON string
            })
        msgInput.value = '';
            // .then(response => response.json()) // Parse JSON response from server
    }
    senderbtn.addEventListener("click", sentmsg);

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
    //root 7
    async function loadMessages() {
        try {
            const response = await fetch("load_msg.php?user=<?= $user?>&chat=<?= $chatid?>&last_message_id=" +
                lastMessageId);
            // This will now parse a clean array: [ {msg_id: 1, msg: "hi"}, {msg_id: 2, msg: "hello"} ]
            // console.log(response.text());
            const data = await response.json();
            let chat = document.getElementById("chatArea");
            let user = <?= $_SESSION['id']; ?>
            // Loop through all received messages
            data.forEach(item => {
                if (item.msg.trim() !== "") {
                    let recevied = `${item.sender}`;
                    let messageHtml = "nothing";
                    const date = item.time.replace(" ", "T");

                    const time12hr = new Date(date).toLocaleTimeString("en-US", {
                        hour: "numeric",
                        minute: "2-digit",
                        hour12: true
                    });
                    if (recevied != user) {
                        //we got it
                        messageHtml = `<div class="flex msg-d" data-id="1">
                                            <div class="bg-white px-4 py-3 rounded-2xl shadow max-w-[75%]">
                                                <p>${item.msg}</p>
                                                <span class="text-xs text-gray-500 block text-right mt-1">${time12hr}</span>
                                        </div></div>`;
                    } else {
                        //we sent it
                        messageHtml = `<div class="flex justify-end msg-d" data-id="2">
                                    <div class="bg-blue-500 text-white px-4 py-3 rounded-2xl shadow max-w-[75%]">
                                        <p>${item.msg}</p>
                                    <span class="text-xs text-blue-100 block text-right mt-1">
                                        ${time12hr}
                                    </span>
                                </div>
                                </div>`;
                    }
                    // let messageHtml = `<div class="msg-d" data-id="${item.msg_id}">${item.msg}</div>`;
                    chat.insertAdjacentHTML("beforeend", messageHtml);

                    // Update your lastMessageId tracking dynamically instead of hardcoding '3'
                    lastMessageId = item.msg_id;
                }
            });

            // Optional: Scroll to bottom automatically when new messages arrive
            // if(data.length > 0) { chat.scrollTop = chat.scrollHeight; }

        } catch (error) {
            console.error("Error fetching data:", error);
        }
    }

    // Poll the database every 1 second
    setInterval(loadMessages, 1000);
    </script>

</body>

</html>