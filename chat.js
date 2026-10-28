function timeto12(date) {
    const rdate = date.replace(" ", "T");
    const time12hr = new Date(rdate).toLocaleTimeString("en-US", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true
    });
    return time12hr;
}

function compareTime(date, nowtime) {
    const rdate = date.replace(" ", "T");
    const ntime = nowtime;
    if (rdate < ntime) {
        console.log("last time is old");
    } else {
    }
}

function updateLastseen() {
    fetch("lastseen.php");
    return;
}

function escapeHtml(str) {
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function parseMySQLDate(str) {
    const [datePart, timePart] = str.split(" ");
    const [y, m, d] = datePart.split("-").map(Number);
    const [hh, mm, ss] = (timePart || "00:00:00").split(":").map(Number);
    return new Date(y, m - 1, d, hh, mm, ss);
}

function formatDateLabel(dateStr) {
    const msgDate = parseMySQLDate(dateStr);
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    const sameDay = (a, b) =>
        a.getFullYear() === b.getFullYear() &&
        a.getMonth() === b.getMonth() &&
        a.getDate() === b.getDate();

    if (sameDay(msgDate, today)) return "Today";
    if (sameDay(msgDate, yesterday)) return "Yesterday";

    const options = { day: "numeric", month: "long" };
    if (msgDate.getFullYear() !== today.getFullYear()) {
        options.year = "numeric";
    }
    return msgDate.toLocaleDateString("en-US", options);
}

function renderDateLabels() {
    chatArea.querySelectorAll(".date-label").forEach(el => el.remove());

    const msgEls = chatArea.querySelectorAll(".msg-d");
    let lastDateKey = null;

    msgEls.forEach(el => {
        const rawDate = el.dataset.date;
        if (!rawDate) return;
        const dateKey = rawDate.split(" ")[0];

        if (dateKey !== lastDateKey) {
            lastDateKey = dateKey;
            const label = document.createElement("div");
            label.className = "date-label flex justify-center my-3";
            label.innerHTML = `<span class="bg-gray-300 text-gray-700 text-xs font-medium px-3 py-1 rounded-full">${formatDateLabel(rawDate)}</span>`;
            el.insertAdjacentElement("beforebegin", label);
        }
    });
}

function decrypt(public_key, pKey, cText) {
    let plaintext = "";
    let ciphertext = sodium.from_base64(cText);
    const nonce = ciphertext.slice(0, sodium.crypto_box_NONCEBYTES);
    let ciphertext2 = ciphertext.slice(sodium.crypto_box_NONCEBYTES);
    $public_key = public_key;
    try {
        plaintext = sodium.crypto_box_open_easy(
            ciphertext2,
            nonce,
            sodium.from_base64(public_key), // from pblc
            pKey // my private
        );
    } catch (error) {
        console.log(error);
    }
    return sodium.to_string(plaintext);
}

function encrypt(public_key, pKey, cText) {
    const plaintext = cText;
    const salt = sodium.randombytes_buf(sodium.crypto_pwhash_SALTBYTES);
    const nonce = sodium.randombytes_buf(sodium.crypto_box_NONCEBYTES);
    let ciphertext = "";
    try {
        ciphertext = sodium.crypto_box_easy(
            sodium.from_string(plaintext), // plaintext45645
            nonce,
            sodium.from_base64(public_key), // his pub
            pKey // my pri
        );
    } catch (error) {
        console.log(error);
    }
    const packed = new Uint8Array(nonce.length + ciphertext.length);
    packed.set(nonce);
    packed.set(ciphertext, nonce.length);
    const toSend = sodium.to_base64(packed);
    return toSend;
}



