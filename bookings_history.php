<?php
require_once 'auth.php';
requireLogin();
require_once 'database.php';

$userId = getLoggedInUserId();
$itemsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

try {
    $pdo = dbConnect();
    $totalBookingsStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = :user_id");
    $totalBookingsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $totalBookingsStmt->execute();
    $totalBookings = $totalBookingsStmt->fetchColumn();
    $totalPages = ceil($totalBookings / $itemsPerPage);

    $stmt = $pdo->prepare("SELECT b.booking_time, e.name AS event_name, e.date AS event_date, e.venue AS event_venue
                            FROM bookings b
                            JOIN events e ON b.event_id = e.id
                            WHERE b.user_id = :user_id
                            ORDER BY b.booking_time DESC
                            LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching booking history: " . $e->getMessage());
    $bookings = [];
    $error = "Failed to load booking history.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <link rel="stylesheet" href="src/output.css">
</head>
<body class="flex items-center justify-center h-screen w-full  relative overflow-hidden px-4 bg-cover bg-center bg-no-repeat" style="background-image: url('src/img/bg-img.jpg');">
    <div class="container mx-auto py-8">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Booking History</h1>

        <div class="flex justify-center mb-4">
            <a href="index.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-3xl focus:outline-none focus:shadow-outline mr-2">Back to Events</a>
            <a href="logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-3xl focus:outline-none focus:shadow-outline">Logout</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-200 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($bookings)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white/20  backdrop-blur-lg  shadow-md rounded-lg">
                    <thead class="bg-white/20  backdrop-blur-lg">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Event Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Venue</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Booking Time</th>
                        </tr>
                    </thead>
                    <tbody class="backdrop-blur-xs divide-y divide-gray-200">
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td class="px-4 py-4 text-white whitespace-nowrap"><?php echo htmlspecialchars($booking['event_name']); ?></td>
                                <td class="px-4 py-4 text-white whitespace-nowrap"><?php echo htmlspecialchars($booking['event_date']); ?></td>
                                <td class="px-4 py-4 text-white whitespace-nowrap"><?php echo htmlspecialchars($booking['event_venue']); ?></td>
                                <td class="px-4 py-4 text-white whitespace-nowrap"><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center mt-6">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?php echo $currentPage - 1; ?>" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                Previous
                            </a>
                        <?php endif; ?>

                        <?php
                        // Display page numbers with a maximum of 5 visible buttons
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);

                        if ($startPage > 1) {
                            echo '<span class="relative inline-flex items-center px-4 py-2 border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                        }

                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="<?php echo ($i === $currentPage) ? 'bg-indigo-50 border-indigo-500 text-indigo-600 z-10' : 'bg-white border-gray-300 hover:bg-gray-50'; ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor;

                        if ($endPage < $totalPages) {
                            echo '<span class="relative inline-flex items-center px-4 py-2 border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                        }
                        ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?php echo $currentPage + 1; ?>" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                Next
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <p class="text-gray-700 text-center mt-4">No bookings found.</p>
        <?php endif; ?>
    </div>
</body>
</html>