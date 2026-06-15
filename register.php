<?php

include "db.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usernamea = $_POST["username"];
    $passworda = $_POST["password"];
    $sql = "SELECT * FROM users WHERE username = '$usernamea'";
    $result = mysqli_query($conn, $sql);
    $rows = mysqli_num_rows($result);
    if ($rows > 0)
    {
        //TODO
        echo "<script>
window.addEventListener('load', () => {
    const errordiv = document.getElementById('unavailUser');
    errordiv.classList.remove(`hidden`);
});</script>";
    } else {
        $fileName = "";
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'profilepics/';
            $fileName = basename($_FILES['profile_image']['name']);
            $targetPath = $uploadDir . $fileName;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                // $message = "File uploaded successfully: " . htmlspecialchars($fileName);
                // echo $message;
                $hashedPassword = password_hash($passworda, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, password_hash, profile_image) 
                VALUES ('$usernamea','$hashedPassword', '$fileName')";
                $result = mysqli_query($conn, $sql);
                header("Location: login.php");
                
            } else {
                // $message = "Error moving the uploaded file.";
                // echo $message;
                 echo "<script>
    window.addEventListener('load', () => {
        const errordiv = document.getElementById('imageunload');
        errordiv.classList.remove(`hidden`);
    });</script>";
            }
        } else {
            // $message = "Error code: " . $_FILES['profile_image']['error'];
            // echo $message;
            echo "<script>
    window.addEventListener('load', () => {
        const errordiv = document.getElementById('imageunload');
        errordiv.classList.remove(`hidden`);
    });</script>";
        }
        // hash password

    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="tw.js"></script>
    <title>Signup</title>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-lg bg-white rounded-2xl shadow-lg p-8">
        <div class="w-full bg-red-600 text-white py-3 my-3 rounded-lg transition flex justify-center hidden" id="unavailUser" disabled>
                Username is taken
        </div>
        <div class="w-full bg-red-600 text-white py-3 my-3 rounded-lg transition flex justify-center hidden" id="imageunload" disabled>
                Profile image didnt upload. Try again
        </div>
        <h1 class="text-3xl font-bold text-center mb-8">
            Create Account
        </h1>

        <form class="space-y-5" method="post" enctype="multipart/form-data">

            <!-- Profile Image Upload -->
            <div class="flex flex-col items-center">

                <div class="w-28 h-28 rounded-full bg-gray-200 overflow-hidden mb-4 flex items-center justify-center">
                    <img id="preview" class="w-full h-full object-cover hidden">

                    <span id="placeholder" class="text-gray-500 text-sm">
                        No Image
                    </span>
                </div>

                <label for="imageUpload"
                    class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                    Upload Image
                </label>

                <input type="file" id="imageUpload" name="profile_image" accept="image/*" class="hidden">
            </div>

            <!-- Username -->
            <div>
                <label class="block mb-2 font-medium">
                    Username
                </label>

                <input type="text" name="username"
                    placeholder=" Enter username"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Password -->
            <div>
                <label class="block mb-2 font-medium">
                    Password
                </label>

                <input type="password" name="password" placeholder="Enter password"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block mb-2 font-medium">
                    Confirm Password
                </label>

                <input type="password" placeholder="Confirm password"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg transition">
                Create Account
            </button>

        </form>

        <p class="text-center text-gray-500 mt-6">
            Already have an account?
            <a href="login.php" class="text-blue-600 hover:underline">
                Login
            </a>
        </p>

    </div>

    <script>
    const upload = document.getElementById("imageUpload");
    const preview = document.getElementById("preview");
    const placeholder = document.getElementById("placeholder");

    upload.addEventListener("change", function() {
        const file = this.files[0];

        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove("hidden");
            placeholder.classList.add("hidden");
        }
    });
    </script>

</body>

</html>