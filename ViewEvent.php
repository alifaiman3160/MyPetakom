<?php
// Include authenticator to check if user is logged in
include 'authenticator.php';

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "mypetakom";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user information from session
$username = $_SESSION['username'];
$user_type = $_SESSION['user_type'];

// Fetch user data from database
$sql = "SELECT * FROM profile WHERE username='$username'";
$result = $conn->query($sql);
$user_data = $result->fetch_assoc();

// Get user's full name for display 
$display_name = !empty($user_data['full_name']) ? $user_data['full_name'] : $user_data['username'];

// Handle search 
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if (!empty($search_query)) {
    $search_condition = "WHERE event_name LIKE '%$search_query%' OR event_description LIKE '%$search_query%' OR event_location LIKE '%$search_query%'";
}

// Fetch events from database
$events_sql = "SELECT * FROM events $search_condition ORDER BY event_date ASC";
$events_result = $conn->query($events_sql);

// Sample events data 
$sample_events = [
    [
        'id' => 1,
        'event_name' => 'Annual Tech Conference',
        'event_date' => '21/06/2025',
        'event_time' => '10:00 am',
        'event_location' => 'DK1, Faculty of Computing',
        'event_description' => 'Join us for the Annual Tech Conference, a premier gathering for technology professionals, entrepreneurs, and students to explore the latest trends and innovations in the tech industry. This year\'s conference will feature keynote speakers, hands-on workshops, and networking sessions on topics ranging from AI, IoT, Cybersecurity, and Blockchain to Startup Ecosystems.',
        'qr_data' => 'EVENT_ID_1_ANNUAL_TECH_CONF_2025'
    ],
    [
        'id' => 2,
        'event_name' => 'Campus Clean-up',
        'event_date' => '15/06/2025',
        'event_time' => '08:00 am',
        'event_location' => 'Main Campus Ground',
        'event_description' => 'Environmental initiative to maintain campus cleanliness and promote sustainability awareness among students and staff.',
        'qr_data' => 'EVENT_ID_2_CAMPUS_CLEANUP_2025'
    ],
    [
        'id' => 3,
        'event_name' => 'Programming Workshop',
        'event_date' => '28/06/2025',
        'event_time' => '02:00 pm',
        'event_location' => 'Computer Lab 2',
        'event_description' => 'Hands-on programming workshop covering modern web development technologies including React, Node.js, and database integration.',
        'qr_data' => 'EVENT_ID_3_PROGRAMMING_WORKSHOP_2025'
    ]
];

// Use sample data if no database events found
$events_to_display = [];
if ($events_result && $events_result->num_rows > 0) {
    while ($row = $events_result->fetch_assoc()) {
        $events_to_display[] = $row;
    }
} else {
    // Filter sample events based on search
    if (!empty($search_query)) {
        foreach ($sample_events as $event) {
            if (stripos($event['event_name'], $search_query) !== false ||
                stripos($event['event_description'], $search_query) !== false ||
                stripos($event['event_location'], $search_query) !== false) {
                $events_to_display[] = $event;
            }
        }
    } else {
        $events_to_display = $sample_events;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPetakom - View Events</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #e0eafc;
        }
        .sidebar {
            width: 250px;
            background: #b3e0ff;
            min-height: 100vh;
            padding: 0;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            z-index: 100;
        }
        .sidebar img {
            display: block;
            margin: 30px auto 20px auto;
            height: 70px;
        }
        .sidebar nav {
            width: 100%;
        }
        .sidebar nav a {
            display: block;
            padding: 15px 30px;
            color: #222;
            text-decoration: none;
            font-size: 16px;
            transition: background 0.2s;
        }
        .sidebar nav a.active, .sidebar nav a:hover {
            background: #8ecae6;
            font-weight: bold;
        }
        .sidebar nav .submenu {
            background: #d4f1ff;
            padding-left: 50px;
            padding-top: 10px;
            padding-bottom: 10px;
            font-size: 14px;
        }
        .sidebar nav .submenu:hover {
            background: #c0e7ff;
        }
        .sidebar nav .parent-menu {
            position: relative;
        }
        .sidebar nav .parent-menu::after {
            content: '▼';
            position: absolute;
            right: 20px;
            font-size: 12px;
            transition: transform 0.2s;
        }
        .sidebar nav .parent-menu.collapsed::after {
            transform: rotate(-90deg);
        }
        .submenu-container {
            display: block;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .submenu-container.collapsed {
            max-height: 0;
        }
        .topbar {
            margin-left: 250px;
            background: #b3e0ff;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 40px;
        }
        .topbar .user-welcome {
            margin-right: auto;
            font-weight: bold;
            font-size: 16px;
        }
        .topbar a {
            margin-left: 30px;
            color: #222;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
        }
        .topbar a.logout {
            color: #222;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .main-content {
            margin-left: 270px;
            margin-top: 40px;
            padding: 30px 40px;
        }
        .page-header {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            padding: 30px;
            margin-bottom: 30px;
        }
        .page-title {
            border-bottom: 2px solid #8ecae6;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 8px;
            font-size: 18px;
            letter-spacing: 1px;
        }
        .search-container {
            position: relative;
            max-width: 400px;
        }
        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        .event-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        .event-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }
        .event-name {
            font-weight: bold;
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }
        .event-datetime {
            color: #666;
            font-size: 14px;
        }
        .event-body {
            padding: 20px;
            display: flex;
            gap: 20px;
        }
        .qr-section {
            flex-shrink: 0;
            text-align: center;
        }
        .qr-code {
            width: 120px;
            height: 120px;
            border: 2px solid #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            margin-bottom: 10px;
        }
        .qr-placeholder {
            font-size: 10px;
            color: #999;
            text-align: center;
            line-height: 1.2;
        }
        .qr-instruction {
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .event-details {
            flex: 1;
        }
        .event-location {
            font-weight: bold;
            color: #4a90e2;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .event-description {
            color: #555;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        .event-actions {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            text-align: right;
        }
        .btn-done {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-done:hover {
            background: #218838;
        }
        .no-events {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        @media (max-width: 900px) {
            .main-content {
                margin-left: 0;
                padding: 80px 20px 20px;
            }
            .sidebar {
                width: 100%;
                min-height: unset;
                position: fixed;
                height: 60px;
                flex-direction: row;
                align-items: center;
            }
            .sidebar img {
                height: 40px;
                margin: 10px 20px;
            }
            .sidebar nav {
                display: none;
            }
            .topbar {
                margin-left: 0;
                margin-top: 60px;
                position: fixed;
                width: 100%;
                box-sizing: border-box;
                z-index: 90;
            }
            .events-grid {
                grid-template-columns: 1fr;
            }
            .event-body {
                flex-direction: column;
                align-items: center;
            }
            .qr-section {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <img src="Logo Petakom.jpg" alt="PETAKOM Logo">
        <nav>
            <a href="loginsuccesful.php">Home</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="#" class="parent-menu active" onclick="toggleSubmenu('event-submenu')">Event Management</a>
            <div id="event-submenu" class="submenu-container">
                <a href="CreateEvent.php" class="submenu">Create Event</a>
                <a href="ViewEvent.php" class="submenu active">View Events</a>
                <a href="manage_committee.php" class="submenu">Manage Committee</a>
            </div>
            <a href="#" class="parent-menu" onclick="toggleSubmenu('merit-submenu')">Merit Management</a>
            <div id="merit-submenu" class="submenu-container collapsed">
                <a href="add_merit.php" class="submenu">Add Merit</a>
                <a href="view_merits.php" class="submenu">View Merits</a>
                <a href="merit_reports.php" class="submenu">Merit Reports</a>
            </div>
            <a href="attendance.php">Attendance</a>
            <a href="profile.php">Profile</a>
            <a href="administration.php">Administration</a>
        </nav>
    </div>
    <div class="topbar">
        <div class="user-welcome">Welcome, <?php echo htmlspecialchars($display_name); ?></div>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="logout.php" class="logout">LOG OUT</a>
    </div>
    <div class="main-content">
        <div class="page-header">
            <div class="page-title">VIEW EVENTS</div>
            <div class="search-container">
                <form method="GET" action="">
                    <input type="text" name="search" class="search-input" placeholder="Search events..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <span class="search-icon">🔍</span>
                </form>
            </div>
        </div>

        <?php if (empty($events_to_display)): ?>
            <div class="no-events">
                <?php if (!empty($search_query)): ?>
                    No events found matching "<?php echo htmlspecialchars($search_query); ?>". <a href="view_events.php">View all events</a>
                <?php else: ?>
                    No events available at the moment.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events_to_display as $event): ?>
                <div class="event-card">
                    <div class="event-header">
                        <div class="event-name"><?php echo htmlspecialchars($event['event_name']); ?></div>
                        <div class="event-datetime">
                            <?php echo htmlspecialchars($event['event_date']); ?> • <?php echo htmlspecialchars($event['event_time']); ?>
                        </div>
                    </div>
                    <div class="event-body">
                        <div class="qr-section">
                            <div class="qr-code">
                                <div class="qr-placeholder">
                                    ████████<br>
                                    █&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;█<br>
                                    █&nbsp;████&nbsp;█<br>
                                    █&nbsp;████&nbsp;█<br>
                                    █&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;█<br>
                                    ████████
                                </div>
                            </div>
                            <div class="qr-instruction">
                                Please scan the QR code to know the event details !
                            </div>
                        </div>
                        <div class="event-details">
                            <div class="event-location">
                                Event Geolocation: <?php echo htmlspecialchars($event['event_location']); ?>
                            </div>
                            <div class="event-description">
                                <?php echo nl2br(htmlspecialchars($event['event_description'])); ?>
                            </div>
                        </div>
                    </div>
                    <div class="event-actions">
                        <button class="btn-done" onclick="markEventDone(<?php echo $event['id']; ?>)">DONE</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const parentMenu = submenu.previousElementSibling;
            
            if (submenu.classList.contains('collapsed')) {
                submenu.classList.remove('collapsed');
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
                parentMenu.classList.remove('collapsed');
            } else {
                submenu.classList.add('collapsed');
                submenu.style.maxHeight = '0px';
                parentMenu.classList.add('collapsed');
            }
        }

        function markEventDone(eventId) {
            if (confirm('Mark this event as completed?')) {
                // You can implement AJAX call here to update event status
                alert('Event marked as completed!');
                // Example AJAX implementation:
                // fetch('update_event_status.php', {
                //     method: 'POST',
                //     headers: {'Content-Type': 'application/json'},
                //     body: JSON.stringify({event_id: eventId, status: 'completed'})
                // });
            }
        }

        // Initialize submenus on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set max-height for expanded submenus
            const expandedSubmenus = document.querySelectorAll('.submenu-container:not(.collapsed)');
            expandedSubmenus.forEach(function(submenu) {
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
            });

            // Auto-submit search form on input
            const searchInput = document.querySelector('.search-input');
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 500);
            });
        });
    </script>
</body>
</html>