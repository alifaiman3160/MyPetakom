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

// Get user's full name for display (fallback to username if full_name is not available)
$display_name = !empty($user_data['full_name']) ? $user_data['full_name'] : $user_data['username'];

// Fetch recent activities with images from database
// This is a placeholder - in production, you'd fetch this from your database
$activities = [
    [
        'name' => 'Combat',
        'date' => '23/05/2025',
        'time' => '10:00 am',
        'image' => 'combat.jpg',
        'description' => 'Annual sports competition between students'
    ],
    [
        'name' => 'Annual Tech Conference',
        'date' => '15/05/2025',
        'time' => '08:00 am',
        'image' => 'anualtech.jpg',
        'description' => 'Industry experts share insights on latest technology trends'
    ],
    [
        'name' => 'Campus Clean-up',
        'date' => '17/05/2025',
        'time' => '10:00 am',
        'image' => 'campus-cleanup.jpg',
        'description' => 'Environmental initiative to maintain campus cleanliness'
    ],
    [
        'name' => 'Majlis Kecemerlangan Pelajar',
        'date' => '30/05/2025',
        'time' => '10:00 am',
        'image' => 'majlis.jpg',
        'description' => 'Award ceremony recognizing student achievements'
    ]
];

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPetakom - Home</title>
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
            display: flex;
            flex-direction: column;
            gap: 40px;
            padding: 30px 40px;
        }
        .story-section, .activity-section {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            padding: 30px;
        }
        .section-title {
            border-bottom: 2px solid #8ecae6;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 3px;
            font-size: 15px;
            letter-spacing: 1px;
        }
        .story-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .story-content {
            font-size: 14px;
            line-height: 1.6;
            text-align: justify;
        }
        .activity-section {
            width: 100%;
        }
        .activity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .activity-card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .activity-image {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }
        .activity-details {
            padding: 15px;
        }
        .activity-name {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 15px;
        }
        .activity-date {
            color: #666;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .activity-description {
            font-size: 13px;
            color: #333;
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
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <img src="Logo Petakom.jpg" alt="PETAKOM Logo">
        <nav>
            <a href="loginsuccesful.php" class="active">Home</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="#" class="parent-menu" onclick="toggleSubmenu('event-submenu')">Event Management</a>
            <div id="event-submenu" class="submenu-container">
                <a href="CreateEvent.php" class="submenu">Create Event</a>
                <a href="ViewEvent.php" class="submenu">View Events</a>
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
        <div class="story-section">
            <div class="section-title">ABOUT PETAKOM</div>
            <div class="story-content">
                <p>Persatuan Teknologi Komputer (PETAKOM) is the student association for the Faculty of Computing at Universiti Malaysia Pahang (UMP). Established in 2002, PETAKOM serves as a platform for computer science and information technology students to develop their professional skills, leadership abilities, and community engagement.</p>
                
                <p>The association's primary objectives include:</p>
                <ul>
                    <li>Fostering academic excellence among computing students</li>
                    <li>Organizing technical workshops, seminars, and competitions</li>
                    <li>Building connections with industry partners for career opportunities</li>
                    <li>Promoting innovation and technological advancement</li>
                    <li>Supporting community service initiatives</li>
                </ul>
                
                <p>PETAKOM has grown significantly over the years, earning recognition for its outstanding contributions to student development and university life. The association's activities are guided by its commitment to producing skilled, ethical, and industry-ready computing professionals.</p>
            </div>
        </div>
        
        <div class="activity-section">
            <div class="section-title">RECENT ACTIVITIES</div>
            <div class="activity-grid">
                <?php foreach ($activities as $activity): ?>
                <div class="activity-card">
                    <img src="<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>" class="activity-image">
                    <div class="activity-details">
                        <div class="activity-name"><?php echo htmlspecialchars($activity['name']); ?></div>
                        <div class="activity-date"><?php echo htmlspecialchars($activity['date']); ?> • <?php echo htmlspecialchars($activity['time']); ?></div>
                        <div class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
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

        // Initialize submenus on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set max-height for expanded submenus
            const expandedSubmenus = document.querySelectorAll('.submenu-container:not(.collapsed)');
            expandedSubmenus.forEach(function(submenu) {
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
            });
        });
    </script>
</body>
</html>