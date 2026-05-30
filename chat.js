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

            if (fileUpload.files[0].type.startsWith("image/")) {
                formData.append('message_type', "image");
            } else if (fileUpload.files[0].type.includes("pdf")) {
                formData.append('message_type', "file");
            }

            formData.append('message', msgInput.value);
            formData.append('receiver_id', <?= $chatid;?>);
            formData.append('file_size', (fileUpload.files[0].size / 1024 / 1024).toFixed(2))

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
            formData.append('receiver_id', <?= $chatid;?>);
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
                if (item.msg.trim() !== "" || item.message_type !== "text") {
                    let recevied = `${item.sender}`;
                    let messageHtml = "nothing";
                    const date = item.time.replace(" ", "T");

                    const time12hr = new Date(date).toLocaleTimeString("en-US", {
                        hour: "numeric",
                        minute: "2-digit",
                        hour12: true
                    });
                    if (item.type == "image") {
                        if (recevied != user) {
                            //we got it
                            messageHtml = `<div class="flex">
                                        <div class="bg-white-500 p-2 rounded-2xl max-w-[75%] shadow">
                                            <a href="uploads/${item.filename}" download><img src="uploads/${item.filename}" class="rounded-xl w-full object-cover"></a>
                                            <p>${item.msg}</p>
                                            <span class="text-xs text-gray-500 block text-right mt-1">
                                            ${time12hr}
                                            </span></div> </div>`;
                        } else {
                            //we sent it
                            messageHtml = `<div class="flex justify-end">
                                        <div class="bg-blue-500 p-2 rounded-2xl max-w-[75%] shadow">
                                            <a href="uploads/${item.filename}" download><img src="uploads/${item.filename}" class="rounded-xl w-full object-cover"></a>
                                            <p>${item.msg}</p>
                                            <span class="text-xs text-blue-100 block text-right mt-1">
                                            ${time12hr}
                                            </span></div> </div>`;
                        }
                        chat.insertAdjacentHTML("beforeend", messageHtml);
                        lastMessageId = item.msg_id;
                    } else if (item.type == "file") {
                        if (recevied != user) {
                            messageHtml = `<div class="flex">
                                            <div class="bg-white px-4 py-3 rounded-2xl shadow max-w-[75%] flex items-center gap-3">
                                            <div class="text-3xl">
                                                📄
                                            </div>
                                            <div>
                                                <h2 class="font-medium">
                                                    ${item.filename.slice(11)}
                                                </h2>
                                                <p class="text-sm text-gray-500">
                                                    ${item.filesize} MB
                                                </p><span class="text-xs text-blue-100 block text-right mt-1">
                                            ${time12hr}
                                        </span></div></div></div>`
                        } else {
                            let filename = item.filename
                            messageHtml = `<div class="flex justify-end">
                                            <div class="bg-blue-500 px-4 py-3 rounded-2xl shadow max-w-[75%] flex items-center gap-3">
                                            <div class="text-3xl">
                                                <a href="uploads/${item.filename}" download>📄</a>
                                            </div>
                                            <div>
                                                <h2 class="font-medium">
                                                   ${item.filename.slice(11)}
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