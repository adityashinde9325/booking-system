<?php
require_once 'auth.php';
requireLogin(); // Ensure only logged-in users can access this
require_once 'database.php';

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
    $venue = filter_input(INPUT_POST, 'venue', FILTER_SANITIZE_STRING);
    $available_seats = filter_input(INPUT_POST, 'available_seats', FILTER_SANITIZE_NUMBER_INT);

    if (empty($name)) {
        $errors[] = 'Event name is required.';
    }
    if (empty($date)) {
        $errors[] = 'Event date is required.';
    }
    if (empty($time)) {
        $errors[] = 'Event time is required.';
    }
    if (empty($venue)) {
        $errors[] = 'Event venue is required.';
    }
    if (!is_numeric($available_seats) || $available_seats < 0) {
        $errors[] = 'Available seats must be a non-negative number.';
    }

    if (empty($errors)) {
        try {
            $pdo = dbConnect();
            $dateTime = $date . ' ' . $time . ':00'; // Combine date and time for DATETIME field
            $stmt = $pdo->prepare("INSERT INTO events (name, date, venue, available_seats) VALUES (:name, :date, :venue, :available_seats)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':date', $dateTime);
            $stmt->bindParam(':venue', $venue);
            $stmt->bindParam(':available_seats', $available_seats, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $successMessage = 'Event added successfully!';
            } else {
                $errors[] = 'Error adding event. Please try again.';
            }
        } catch (PDOException $e) {
            error_log("Error adding event: " . $e->getMessage());
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
    <title>Add New Event</title>
    <link rel="stylesheet" href="src/output.css">
</head>
<body class="flex items-center justify-center    w-full  relative  px-4 bg-cover bg-center bg-no-repeat" style="background-image: url('src/img/bg-img.jpg');">

    <div class="container mx-auto py-8">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Add New Event</h1>

        <div class="flex justify-center mb-4">
            <a href="index.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-3xl focus:outline-none focus:shadow-outline mr-2">Back to Events</a>
            <a href="logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-3xl focus:outline-none focus:shadow-outline">Logout</a>
        </div>

        <div class="bg-white/20 max-h-full backdrop-blur-lg  rounded-3xl shadow-md shadow-md rounded-3xl px-8 pt-6 pb-8 mb-4 max-w-md mx-auto">
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

            <?php if (!empty($successMessage)): ?>
                <div class="bg-green-200 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($successMessage); ?></span>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-white text-sm font-bold mb-2" for="name">
                        Event Name:
                    </label>
                    <input class="shadow appearance-none border rounded-3xl w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline" type="text" id="name" name="name" required>
                </div>
                <div>
                    <label class="block text-white text-sm font-bold mb-2" for="date">
                        Date:
                    </label>
                    <input class="shadow appearance-none border rounded-3xl w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline" type="date" id="date" name="date" required>
                </div>
                <div>
                    <label class="block text-white text-sm font-bold mb-2" for="time">
                        Time:
                    </label>
                    <input class="shadow appearance-none border rounded-3xl w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline" type="time" id="time" name="time" required>
                </div>
                <div>
                    <label class="block text-white text-sm font-bold mb-2" for="venue">
                        Venue:
                    </label>
                    <input class="shadow appearance-none border rounded-3xl w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline" type="text" id="venue" name="venue" required>
                </div>
                <div>
                    <label class="block text-white text-sm font-bold mb-2" for="available_seats">
                        Available Seats:
                    </label>
                    <input class="shadow appearance-none border rounded-3xl w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline" type="number" id="available_seats" name="available_seats" min="0" required>
                </div>
                <div class="flex items-center justify-end">
                    <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-3xl focus:outline-none focus:shadow-outline" type="submit">
                        Add Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>