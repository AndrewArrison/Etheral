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

$sql = "
SELECT u.id, u.username, u.profile_image, u.last_seen,
       m.message_text, m.message_type, m.created_at
FROM (
    SELECT
        CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS other_id,
        MAX(id) AS last_msg_id
    FROM messages
    WHERE sender_id = ? OR receiver_id = ?
    GROUP BY other_id
) latest
JOIN messages m ON m.id = latest.last_msg_id
JOIN users u ON u.id = latest.other_id
ORDER BY m.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $id, $id, $id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="chat.js"></script>
    <script src="tw.js"></script>
    <title>Chats</title>
</head>

<body class="bg-gray-100 h-screen overflow-hidden">

    <div class="max-w-md mx-auto bg-white h-screen flex flex-col shadow-lg">

        <!-- Top Bar -->
        <div class="bg-blue-600 text-white p-4 flex items-center gap-4 shadow shrink-0 justify-between">
            <h1 class="text-lg font-semibold">Chats</h1>
            <button class="text-xl bg-amber-500 px-2 py-1 rounded-2xl shadow">
                <a href="logout.php">Logout</a>
            </button>
        </div>

        <!-- Search Box -->
        <div class="p-3 border-b shrink-0 relative">
            <input
                type="text"
                id="searchInput"
                placeholder="Search users..."
                class="w-full bg-gray-100 rounded-full px-5 py-3 outline-none"
                autocomplete="off"
            >
            <div id="searchResults"
                 class="absolute left-3 right-3 bg-white border rounded-xl shadow-lg mt-1 max-h-72 overflow-y-auto hidden z-10">
            </div>
        </div>

        <!-- Chat List -->
        <div class="flex-1 overflow-y-auto" id="chatList">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <a href="#" onclick="submitPost(event, {id:<?= $row['id'] ?>})" class="flex items-center gap-4 p-4 hover:bg-gray-100 border-b transition">
                        <img
                            src="profilepics/<?= $row['profile_image'] ?>"
                            class="w-14 h-14 rounded-full object-cover"
                        >

                        <div class="flex-1 min-w-0">

                            <div class="flex justify-between items-center">

                                <h2 class="font-semibold text-lg truncate">
                                    <?= htmlspecialchars($row['username']) ?>
                                </h2>

                                <span class="text-sm text-gray-500 last-seen" data-last-seen="<?= htmlspecialchars($row['last_seen'], ENT_QUOTES) ?>">
                                   <?php $rtime = new DateTime($row['last_seen']);
                        $ntime = new DateTime(Date("Y-m-d H:i:s"));
                        $delta = $ntime->diff($rtime);
                        echo getActiveStatus($delta);?>
                                </span>

                            </div>

                            <p class="text-gray-500 truncate">
                                <?php
                                if ($row['message_type'] === 'text' || $row['message_type'] === '') {
                                    echo htmlspecialchars($row['message_text']);
                                } else {
                                    echo "📎 " . ucfirst($row['message_type']);
                                }
                                ?>
                            </p>

                        </div>

                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-gray-400 mt-8">No chats yet. Search above to start one.</p>
            <?php endif; ?>
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

    setInterval(() => {
        fetch("lastseen.php");
    }, 45000);

    // ---- Search ----
    const searchInput = document.getElementById("searchInput");
    const searchResults = document.getElementById("searchResults");
    let searchTimeout = null;

    searchInput.addEventListener("input", () => {
        clearTimeout(searchTimeout);
        const q = searchInput.value.trim();

        if (q === "") {
            searchResults.classList.add("hidden");
            searchResults.innerHTML = "";
            return;
        }

        searchTimeout = setTimeout(() => doSearch(q), 250);
    });

    async function doSearch(q) {
        try {
            const res = await fetch("search_users.php?q=" + encodeURIComponent(q));
            const users = await res.json();

            if (users.length === 0) {
                searchResults.innerHTML = `<p class="p-3 text-gray-400 text-sm">No users found</p>`;
                searchResults.classList.remove("hidden");
                return;
            }

            searchResults.innerHTML = users.map(u => `
                <div class="flex items-center gap-3 p-3 hover:bg-gray-100 cursor-pointer search-result-item"
                     data-id="${u.id}">
                    <img src="profilepics/${u.profile_image}" class="w-10 h-10 rounded-full object-cover">
                    <span class="font-medium">${escapeHtmlClient(u.username)}</span>
                </div>
            `).join("");

            searchResults.classList.remove("hidden");
        } catch (err) {
            console.error("Search error:", err);
        }
    }

    searchResults.addEventListener("click", (e) => {
        const item = e.target.closest(".search-result-item");
        if (!item) return;
        const id = item.dataset.id;
        submitPost(e, { id: id });
    });

    // hide results when clicking elsewhere
    document.addEventListener("click", (e) => {
        if (!e.target.closest("#searchInput") && !e.target.closest("#searchResults")) {
            searchResults.classList.add("hidden");
        }
    });

    function escapeHtmlClient(str) {
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>

</html>