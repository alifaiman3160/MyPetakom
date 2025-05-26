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

// Initialize variables
$success_message = '';
$error_message = '';

// Get user information from session
$username = $_SESSION['username'];
$user_type = $_SESSION['user_type'];

// Check if user is authorized (advisor or staff)
if (!in_array($user_type, ['advisor', 'staff'])) {
    echo "<script>
        alert('Only advisors can create events.');
        window.location.href = 'loginsuccesful.php';
    </script>";
    exit;
}

// Fetch advisor data
$sql = "SELECT a.advisor_id, a.advisor_name, p.full_name 
        FROM advisor a
        JOIN profile p ON a.user_id = p.user_id
        WHERE p.username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
        alert('Your account is not properly configured as an advisor.');
        window.location.href = 'loginsuccesful.php';
    </script>";
    exit;
}

$advisor_data = $result->fetch_assoc();
$stmt->close();

// Get display name
$display_name = !empty($advisor_data['full_name']) ? $advisor_data['full_name'] : $advisor_data['advisor_name'];
$advisor_id = $advisor_data['advisor_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize inputs
    $event_name = trim($_POST['event_name']);
    $event_date = trim($_POST['event_date']);
    $event_time = trim($_POST['event_time']);
    $event_location = trim($_POST['event_location']);
    $event_description = trim($_POST['event_description']);
    $approval_letter = '';
    
    // Validate required fields
    if (empty($event_name) || empty($event_date) || empty($event_time) || empty($event_location) || empty($event_description)) {
        $error_message = "All required fields must be filled!";
    } else {
        // Handle file upload
        if (isset($_FILES['approval_letter']) && $_FILES['approval_letter']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $file_type = $_FILES['approval_letter']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $upload_dir = 'uploads/approval_letters/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_ext = pathinfo($_FILES['approval_letter']['name'], PATHINFO_EXTENSION);
                $file_name = time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['approval_letter']['tmp_name'], $upload_path)) {
                    $approval_letter = $file_name;
                } else {
                    $error_message = "Failed to upload approval letter.";
                }
            } else {
                $error_message = "Invalid file type. Only PDF, DOC, and DOCX files are allowed.";
            }
        }
        
        // Only proceed if no errors so far
        if (empty($error_message)) {
            // Insert event with prepared statement
            $insert_sql = "INSERT INTO events (event_name, event_date, event_time, event_location, 
                          event_description, approval_letter, advisor_id)
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sssssss", $event_name, $event_date, $event_time, 
                                   $event_location, $event_description, $approval_letter, $advisor_id);
            
            if ($insert_stmt->execute()) {
                $success_message = "Event created successfully!";
                // Clear form fields
                $event_name = $event_date = $event_time = $event_location = $event_description = '';
            } else {
                $error_message = "Error creating event: " . $conn->error;
            }
            $insert_stmt->close();
        }
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
    <title>MyPetakom - Create Event</title>
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
        .form-section {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            padding: 30px;
            max-width: 800px;
        }
        .section-title {
            border-bottom: 2px solid #8ecae6;
            font-weight: bold;
            margin-bottom: 25px;
            padding-bottom: 8px;
            font-size: 18px;
            letter-spacing: 1px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .file-input {
            position: absolute;
            left: -9999px;
        }
        .file-label {
            display: block;
            padding: 12px;
            background: #f8f9fa;
            border: 2px dashed #8ecae6;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: background 0.2s;
        }
        .file-label:hover {
            background: #e9ecef;
        }
        .btn-primary {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: #357abd;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
            margin-right: 10px;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
            .form-row {
                flex-direction: column;
                gap: 0;
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
                <a href="CreateEvent.php" class="submenu active">Create Event</a>
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
        <a href="#">About</a>
        <a href="#">Contact</a>
        <a href="logout.php" class="logout">LOG OUT</a>
    </div>
    <div class="main-content">
        <div class="form-section">
            <div class="section-title">CREATE NEW EVENT</div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="event_name">Event Name *</label>
                    <input type="text" id="event_name" name="event_name" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="event_date">Event Date *</label>
                        <input type="date" id="event_date" name="event_date" required>
                    </div>
                    <div class="form-group">
                        <label for="event_time">Event Time *</label>
                        <input type="time" id="event_time" name="event_time" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="event_location">Event Location *</label>
                    <input type="text" id="event_location" name="event_location" placeholder="e.g., Main Hall, Computer Lab 1" required>
                </div>
                
                <div class="form-group">
                    <label for="event_description">Event Description *</label>
                    <textarea id="event_description" name="event_description" placeholder="Provide a detailed description of the event, including objectives, activities, and expected outcomes..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="approval_letter">Upload Approval Letter</label>
                    <div class="file-upload">
                        <input type="file" id="approval_letter" name="approval_letter" class="file-input" accept=".pdf,.doc,.docx">
                        <label for="approval_letter" class="file-label">
                            Choose File (PDF, DOC, DOCX)
                        </label>
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <a href="loginsuccesful.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // File upload label update
        document.getElementById('approval_letter').addEventListener('change', function(e) {
            const label = document.querySelector('.file-label');
            if (e.target.files.length > 0) {
                label.textContent = e.target.files[0].name;
            } else {
                label.textContent = 'Choose File (PDF, DOC, DOCX)';
            }
        });

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