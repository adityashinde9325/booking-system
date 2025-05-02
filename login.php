<?php
require_once 'auth.php'; // Include the auth.php file (assuming it handles session start)
require_once 'database.php';

if (isLoggedIn()) {
    header("Location: index.php"); // Redirect to event listing if already logged in
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        try {
            $pdo = dbConnect();
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: index.php");
                exit();
            } else {
                $errors[] = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $errors[] = 'Database error. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="src/output.css">
</head>
<body class="flex items-center justify-center h-screen w-full  relative overflow-hidden px-4 bg-cover bg-center bg-no-repeat" style="background-image: url('src/img/bg-img.jpg');">
    <div class="bg-white/20 max-h-full backdrop-blur-lg  rounded-3xl shadow-md  px-8 pt-6 pb-8 mb-4 w-full max-w-sm">
        <h1 class="block text-white text-2xl font-bold mb-6 text-center">Login</h1>

        <?php if (isset($_GET['registration_success'])): ?>
            <div class="bg-green-200 border border-green-400 text-green-200 px-4 py-3 rounded-4xl  relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">Registration successful! You can now log in.</span>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-200 border border-red-400 text-red-700 px-4 py-3 rounded-4xl relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error): ?>
                        <li class="block sm:inline"><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-white text-sm font-bold mb-2" for="username">
                    Username
                </label>
                <input class=" border rounded-3xl w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline" id="username" name="username" type="text" placeholder="Username" required>
            </div>
            <div>
                <label class="block text-white text-sm font-bold mb-2" for="password">
                    Password
                </label>
                <input class="border rounded-3xl w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" placeholder="Password" required>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-3xl focus:outline-none focus:shadow-outline" type="submit">
                    Login
                </button>
                <a class="inline-block align-baseline font-semibold text-sm text-white hover:text-blue-800" href="register.php">
                Don't have an account? Sign up
                </a>
            </div>
        </form>
    </div>
</body>
</html>