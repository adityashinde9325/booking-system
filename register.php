<?php
require_once 'database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {
            $pdo = dbConnect();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Username already exists.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $hashedPassword);
                if ($stmt->execute()) {
                    header("Location: login.php?registration_success=1");
                    exit();
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
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
    <title>Register</title>
    <link rel="stylesheet" href="src/output.css">
</head>
<body class="flex items-center justify-center h-screen w-full  relative overflow-hidden px-4 bg-cover bg-center bg-no-repeat" style="background-image: url('src/img/bg-img.jpg');">
   

<div class="relative my-10 flex bg-white/20 max-h-full backdrop-blur-lg  rounded-3xl max-w-4xl w-full p-5  md:flex-row flex-col items-center justify-center shadow-lg">

<div class=" max-h-full  rounded-3xl px-8 pt-6 pb-8 mb-4 w-full max-w-sm">
        <h1 class="block text-white text-2xl font-bold mb-6 text-center">Registration Form</h1>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-200 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
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
                    Username:
                </label>
                <input class="shadow appearance-none border rounded-3xl w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline" type="text" id="username" name="username" required>
            </div>
            <div>
                <label class="block text-white text-sm font-bold mb-2" for="password">
                    Password:
                </label>
                <input class="shadow appearance-none border rounded-3xl w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline" type="password" id="password" name="password" required>
            </div>
            <div>
                <label class="block text-white text-sm font-bold mb-2" for="confirm_password">
                    Confirm Password:
                </label>
                <input class="shadow appearance-none border rounded-3xl w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline" type="password" id="confirm_password" name="confirm_password" required>
            </div>

      <div class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-4">
    <button class="w-24 bg-white text-blue-500 font-semibold py-2 rounded-3xl hover:bg-blue-100 transition" type="submit">
        Register
    </button>
    <a class="text-sm text-white hover:underline" href="login.php">
    Don't have an account? Sign up
    </a>
</div>


        </form>
    </div>


    <div class="w-full relative md:w-1/2 flex items-center justify-center p-6">
            <div class="h-20 w-20 bg-blue-400 absolute top-4 right-3 rounded-[20px]"> </div>
            <div class="h-20 w-20 bg-blue-400 absolute bottom-3 left-2 rounded-[20px]"> </div>
            <div class="bg-white/30 backdrop-blur-lg   rounded-3xl p-6 flex flex-col items-center justify-center w-full h-full">
                <img src="src/img/login-svg-1.png" alt="Lock Icon" class="w-40 md:w-60 mx-auto opacity-80">
                <div class="mt-6 md:mt-10 text-center">
                 
                    <p class="text-dark text-center mt-2 text-white">booking.com</p>
                </div>
            </div>
        </div>
                    </div>
</body>
</html>