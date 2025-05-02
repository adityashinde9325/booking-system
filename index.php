<?php
require_once 'auth.php';
requireLogin();
require_once 'database.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$eventsPerPage = 5;
$offset = ($page - 1) * $eventsPerPage;

try {
    $pdo = dbConnect();
    $totalEventsStmt = $pdo->query("SELECT COUNT(*) FROM events");
    $totalEvents = $totalEventsStmt->fetchColumn();
    $totalPages = ceil($totalEvents / $eventsPerPage);

    $stmt = $pdo->prepare("SELECT id, name, date, venue, available_seats FROM events ORDER BY date LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $eventsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching events: " . $e->getMessage());
    $events = [];
    $error = "Failed to load events.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Listing by aditya shinde</title>
    <link rel="stylesheet" href="src/output.css">
    
</head>
<body class="bg-white/20  backdrop-blur-lg font-sans antialiased">
    <div class="bg-gradient-to-br from-purple-600 to-indigo-800 py-16 md:py-24 lg:py-32 relative overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="text-center text-white mb-8 md:mb-12 lg:mb-16">
                <h2 class="text-xl font-semibold mb-2">Find Your Next Experience</h2>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight">Discover & Promote <br class="hidden md:inline">Upcoming Event</h1>
            </div>

            <div class="container mx-auto py-8">
        <h1 class="md:text-3xl text-sm font-bold text-center text-white mb-6">Available Events</h1>

        <div class="flex justify-center mb-4">
            <a href="bookings_history.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-3xl focus:outline-none focus:shadow-outline mr-2">View Booking History</a>
            <a href="logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-3xl focus:outline-none focus:shadow-outline mr-2">Logout</a>
            <a href="add_event.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-3xl focus:outline-none focus:shadow-outline">Add Event</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-200 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full backdrop-blur-xs shadow-md rounded-lg">
                <thead class="bg-white/20  backdrop-blur-lg">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Venue</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Available Seats</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white/30  backdrop-blur-xs rounded-2xl  divide-y divide-gray-200">
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td class="px-4 py-4 whitespace-nowrap text-white"><?php echo htmlspecialchars($event['name']); ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-white"><?php echo htmlspecialchars($event['date']); ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-white"><?php echo htmlspecialchars($event['venue']); ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-white" id="seats-<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['available_seats']); ?></td>
                                 <td class="px-4 py-4 whitespace-nowrap">
                                    <?php if ($event['available_seats'] > 0): ?>
                                        <button class="book-ticket-btn bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-3xl focus:outline-none focus:shadow-outline" data-event-id="<?php echo $event['id']; ?>">Book Ticket</button>
                                    <?php else: ?>
                                        <span class="text-red-500 font-semibold">Sold Out</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-4 py-4 text-center text-white">No events available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="flex justify-center mt-4">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="inline-flex items-center justify-center px-4 py-2 mr-2 text-sm font-semibold text-white bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 transition-colors">
                        Previous
                    </a>
                <?php else: ?>
                    <span class="inline-flex items-center justify-center px-4 py-2 mr-2 text-sm font-semibold text-gray-400 bg-gray-800 border border-transparent rounded-md cursor-not-allowed">
                        Previous
                    </span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="inline-flex items-center justify-center px-4 py-2 mr-2 text-sm font-semibold text-white bg-indigo-500 border border-transparent rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition-colors">
                            <?php echo $i; ?>
                        </span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>" class="inline-flex items-center justify-center px-4 py-2 mr-2 text-sm font-semibold text-white bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 transition-colors">
                            <?php echo $i; ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 transition-colors">
                        Next
                    </a>
                <?php else: ?>
                    <span class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-gray-400 bg-gray-800 border border-transparent rounded-md cursor-not-allowed">
                        Next
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
        </div>

        <div class="absolute top-10 right-10 rounded-full bg-indigo-400 opacity-30 w-24 h-24 md:w-32 md:h-32 lg:w-40 lg:h-40"></div>
        <div class="absolute bottom-10 left-10 rounded-full bg-purple-400 opacity-30 w-16 h-16 md:w-20 md:h-20 lg:w-24 lg:h-24"></div>
        <div class="absolute top-1/3 left-1/4 rounded-full bg-pink-400 opacity-20 w-32 h-32 md:w-48 md:h-48 lg:w-64 lg:h-64 -ml-16 -mt-16"></div>
        <div class="absolute bottom-1/3 right-1/4 rounded-full bg-blue-400 opacity-20 w-20 h-20 md:w-32 md:h-32 lg:w-40 lg:h-40 -mr-10 -mb-10"></div>

        
        
    </div>

    

<div id="bookingModal" class="fixed inset-0 z-50 flex items-center justify-center  bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md relative">
        <span class="close-button absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-2xl font-bold cursor-pointer">&times;</span>
        <p class="text-gray-700 text-lg text-center">We have booked your demo!</p>
    </div>
</div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bookButtons = document.querySelectorAll('.book-ticket-btn');
            const modal = document.getElementById('bookingModal');
            const closeButton = document.querySelector('.close-button');

            bookButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const eventId = this.getAttribute('data-event-id');
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'book_ticket.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const response = xhr.responseText;
                            if (response === 'success') {
                                modal.style.display = "block";
                                const seatsElement = document.querySelector(`#seats-${eventId}`);
                                if (seatsElement) {
                                    const currentSeats = parseInt(seatsElement.textContent);
                                    if (!isNaN(currentSeats) && currentSeats > 0) {
                                        seatsElement.textContent = currentSeats - 1;
                                    }
                                }
                            } else {
                                alert('Error booking ticket: ' + response);
                            }
                        } else {
                            alert('Request failed. Please try again.');
                        }
                    };
                    xhr.onerror = function() {
                        alert('Network error. Please try again.');
                    };
                    xhr.send('event_id=' + encodeURIComponent(eventId));
                });
            });

            closeButton.addEventListener('click', function() {
                modal.style.display = "none";
            });

            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            });

            const locationButton = document.querySelector('.bg-gradient-to-br .relative:nth-child(2) > button');
            const locationDropdown = document.querySelector('.bg-gradient-to-br .relative:nth-child(2) > .hidden');
            const categoryButton = document.querySelector('.bg-gradient-to-br .relative:nth-child(3) > button');
            const categoryDropdown = document.querySelector('.bg-gradient-to-br .relative:nth-child(3) > .hidden');

            if (locationButton && locationDropdown) {
                locationButton.addEventListener('click', () => {
                    locationDropdown.classList.toggle('hidden');
                });
            }

            if (categoryButton && categoryDropdown) {
                categoryButton.addEventListener('click', () => {
                    categoryDropdown.classList.toggle('hidden');
                });
            }

            document.addEventListener('click', (event) => {
                if (locationButton && locationDropdown && !locationButton.contains(event.target) && !locationDropdown.contains(event.target)) {
                    locationDropdown.classList.add('hidden');
                }
                if (categoryButton && categoryDropdown && !categoryButton.contains(event.target) && !categoryDropdown.contains(event.target)) {
                    categoryDropdown.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
