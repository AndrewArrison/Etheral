<?php

session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
include "db.php";
$chatid = 0;
if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $chatid = isset($_POST['id']) ? htmlspecialchars($_POST['id']) : 0;
} else {
    header("Location: logout.php");
    exit();
}

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
    <script src="chat.js"></script>
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

                <input type="file" id="fileUpload" class="hidden" accept="image/*,.pdf,.doc,.docx,.txt,.zip,video/*">

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
    async function sentmsg() {
        if (msgInput.value.trim() == '' && fileUpload.files.length === 0) {
            return;
        }
        try {
            if (fileUpload.files.length > 0) {
                
                const formData = new FormData();

                if (fileUpload.files.length > 0) {
                    formData.append('file', fileUpload.files[0]);
                }

                if (fileUpload.files[0].type.startsWith("image/"))
                {
                     formData.append('message_type', "image");
                } else if (fileUpload.files[0].type.includes("pdf"))
                {
                    formData.append('message_type', "file");
                } else if (fileUpload.files[0].type.startsWith("video"))
                {
                    formData.append('message_type', "video");
                }

                formData.append('message', msgInput.value);
                formData.append('receiver_id', <?=$chatid;?>);
                formData.append('file_size',(fileUpload.files[0].size / 1024 / 1024).toFixed(2) )

                const res = await fetch('sent_msg.php', {
                    method: 'POST',
                    body: formData
                });
                // const text = await res.text();
                // console.log(text);
                msgInput.value = '';
                fileUpload.value = "";
                previewArea.innerHTML = "";
            } else {
                const formData = new FormData();

                // 2. Add your text data to the same FormData object
                formData.append('message', msgInput.value);
                formData.append('receiver_id', <?=$chatid;?>);
                const res = await fetch('sent_msg.php', {
                    method: 'POST',
                    body: formData
                });
                // const text = await res.text();
                // console.log(text);
                msgInput.value = '';
            }
        } catch (error) {
            console.error("Error sending data : ", error);
        }

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
                icon = "🌄";
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
                            <button class="text-2xl closefile">
                                ✖
                            </button>
                    </div>
                `;
            previewArea.appendChild(item);
        });

    });
    // close selected file
    previewArea.addEventListener("click", (e) => {
        if (e.target.closest(".closefile")) {
            fileUpload.value = "";
            previewArea.innerHTML = "";

        }
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
    function escapeHtml(str) {
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }
    let lastMessageId = 0;
    //root 7
    async function loadMessages() {
        try {
            const response = await fetch("load_msg.php?user=<?= $user?>&chat=<?= $chatid?>&last_message_id=" +
                lastMessageId);
            // console.log(response.text());
            const data = await response.json();
            let chat = document.getElementById("chatArea");
            let user = <?= $_SESSION['id']; ?>
            // Loop through all received messages
            data.forEach(item => {
                if (item.msg.trim() !== "" || item.type !== "text") {
                    let recevied = `${item.sender}`;
                    let messageHtml = "nothing";
                    
                    const time12hr = timeto12(item.time);
                    if (item.type == "image") {
                    //image
                        if (recevied != user) {
                            //we got it
                            messageHtml = `<div class="flex">
                                        <div class="bg-white-500 p-2 rounded-2xl max-w-[75%] shadow">
                                            <a href="uploads/${item.filename_store}" download><img src="uploads/${item.filename_store}" data-src="uploads/${item.filename_store}" class="rounded-xl w-full object-cover"></a>
                                            <p>${escapeHtml(item.msg)}</p>
                                            <span class="text-xs text-gray-500 block text-right mt-1">
                                            ${time12hr}
                                            </span></div> </div>`;
                        } else {
                            //we sent it
                            messageHtml = `<div class="flex justify-end">
                                        <div class="bg-blue-500 text-blue-100 p-2 rounded-2xl max-w-[75%] shadow">
                                            <a href="uploads/${item.filename_store}" download><img src="uploads/${item.filename_store}" data-src="uploads/${item.filename_store}" class="rounded-xl w-full object-cover"></a>
                                            <p>${escapeHtml(item.msg)}</p>
                                            <span class="text-xs text-blue-100 block text-right mt-1">
                                            ${time12hr}
                                            </span></div> </div>`;
                        }
                        chat.insertAdjacentHTML("beforeend", messageHtml);
                        lastMessageId = item.msg_id;
                    } else if (item.type=="video") 
                    {
                                                if (recevied != user) {
                            //we got it
                            messageHtml = `<div class="flex">
                                        <div class="bg-white-500 p-2 rounded-2xl max-w-[75%] shadow">
                                            <a href="uploads/${item.filename_store}" download><video src="uploads/${item.filename_store}" data-src="uploads/${item.filename_store}" controls preload="metadata" class="rounded-xl w-full object-cover"></a>
                                            <p>${escapeHtml(item.msg)}</p>
                                            <span class="text-xs text-gray-500 block text-right mt-1">
                                            ${time12hr}
                                            </span></div> </div>`;
                        } else {
                            //we sent it
                            messageHtml = `<div class="flex justify-end">
                                        <div class="bg-blue-500 p-2 rounded-2xl max-w-[75%] shadow">
                                            <a href="uploads/${item.filename_store}" download><video src="uploads/${item.filename_store}" data-src="uploads/${item.filename_store}"
                                             controls  preload="metadata"  class="rounded-xl w-full object-cover"></a>
                                            <p>${escapeHtml(item.msg)}</p>
                                            <span class="text-xs text-blue-100 block text-right mt-1">
                                            ${time12hr}
                                            </span></div> </div>`;
                        }
                        chat.insertAdjacentHTML("beforeend", messageHtml);
                        lastMessageId = item.msg_id;
                    }else if (item.type == "file") {
                        if (recevied != user) {
                            messageHtml = `<div class="flex">
                                            <div class="bg-white px-4 py-3 rounded-2xl shadow max-w-[75%] flex items-center gap-3">
                                            <div class="text-3xl">
                                                📄
                                            </div>
                                            <div>
                                                <h2 class="font-medium">
                                                    ${escapeHtml(item.filename)}
                                                </h2>
                                                <p class="text-sm text-gray-500">
                                                    ${item.filesize} MB
                                                </p><span class="text-xs text-blue-100 block text-right mt-1">
                                            ${time12hr}
                                        </span></div></div></div>`
                        } else {
                            messageHtml = `<div class="flex justify-end">
                                            <div class="bg-blue-500 px-4 py-3 rounded-2xl shadow max-w-[75%] flex items-center gap-3">
                                            <div class="text-3xl">
                                                <a href="uploads/${item.filename_store}" download>📄</a>
                                            </div>
                                            <div>
                                                <h2 class="font-medium">
                                                   ${escapeHtml(item.filename)}
                                                </h2>
                                                <p class="text-sm text-blue-100">
                                                    ${item.filesize} MB
                                                </p>
                                                 <span class="text-xs text-blue-100 block text-right mt-1">
                                            ${time12hr}
                                        </span></div></div></div>`
                        }
                        chat.insertAdjacentHTML("beforeend", messageHtml);
                        lastMessageId = item.msg_id;
                    } else {
                        if (recevied != user) {
                            //we got it
                            messageHtml = `<div class="flex msg-d" data-id="1">
                                                <div class="bg-white px-4 py-3 rounded-2xl shadow max-w-[75%]">
                                                    <p>${escapeHtml(item.msg)}</p>
                                                    <span class="text-xs text-gray-500 block text-right mt-1">${time12hr}</span>
                                            </div></div>`;
                        } else {
                            //we sent it
                            messageHtml = `<div class="flex justify-end msg-d" data-id="2">
                                        <div class="bg-blue-500 text-white px-4 py-3 rounded-2xl shadow max-w-[75%]">
                                            <p>${escapeHtml(item.msg)}</p>
                                        <span class="text-xs text-blue-100 block text-right mt-1">
                                            ${time12hr}
                                        </span>
                                    </div>
                                    </div>`;
                        }
                        chat.insertAdjacentHTML("beforeend", messageHtml);

                        lastMessageId = item.msg_id;
                    }
                }
            });

            // Optional: Scroll to bottom automatically when new messages arrive
            // if(data.length > 0) { chat.scrollTop = chat.scrollHeight; }

        }catch (error) {
            console.error("Error fetching data:", error);
        } finally {
                setTimeout(loadMessages, 1000);
        }
    }
    loadMessages();
    </script>

</body>

</html>