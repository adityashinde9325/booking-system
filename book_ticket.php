<?php
require_once 'auth.php';
requireLogin();
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventId = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);
    $userId = getLoggedInUserId();

    if ($eventId && $userId) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction(); // Start transaction for atomicity

            // 1. Check if there are available seats
            $stmt = $pdo->prepare("SELECT available_seats FROM events WHERE id = :event_id AND available_seats > 0 FOR UPDATE"); // Lock row for update
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->execute();
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($event) {
                // 2. Reduce available seats
                $stmt = $pdo->prepare("UPDATE events SET available_seats = available_seats - 1 WHERE id = :event_id");
                $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
                $stmt->execute();

                // 3. Store booking details
                $stmt = $pdo->prepare("INSERT INTO bookings (user_id, event_id) VALUES (:user_id, :event_id)");
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
                $stmt->execute();

                $pdo->commit(); // Commit the transaction
                echo 'success';
            } else {
                $pdo->rollBack(); // Rollback if no seats available
                echo 'No available seats for this event.';
            }

        } catch (PDOException $e) {
            $pdo->rollBack(); // Rollback on error
            error_log("Booking error: " . $e->getMessage());
            echo 'Database error. Please try again.';
        }
    } else {
        echo 'Invalid request parameters.';
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    echo 'Invalid request.';
}
?>