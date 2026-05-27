<?php

$servername = "localhost";
$username = "root";     // Default XAMPP/WAMP user
$password = "";         // Default XAMPP password is empty
$dbname = "Chatapp";
// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

session_start();
//include

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users
            WHERE username='$username'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {

        $user = mysqli_fetch_assoc($result);

        // verify password
        if (password_verify($password, $user["password_hash"])) {

            $_SESSION["user"] = $user["username"];
            $_SESSION["id"] = $user["id"];
            header("Location: viewchat.php");
            exit();

        } else {
            echo "Wrong password";
        }

    } else {
        echo "User not found";
    }
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
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg">

        <h1 class="text-3xl font-bold text-center mb-6">
            Login
        </h1>

        <form class="space-y-5" method="post">

            <div>
                <label class="block mb-2 font-medium">
                    Username
                </label>

                <input 
                    type="text"
                    name="username"
                    placeholder="Enter your Username"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="block mb-2 font-medium">
                    Password
                </label>

                <input 
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <button 
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg transition"
            >
                Sign In
            </button>

        </form>

        <p class="text-center text-gray-500 mt-6">
            Don't have an account?
            <a href="#" class="text-blue-600 hover:underline">
                Register
            </a>
        </p>

    </div>

</body>
</html>