<?php
// Start the session
session_start();

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "mypetakom"; // As specified in the PDF

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    // SQL query to check user credentials
    $sql = "SELECT * FROM profile WHERE username='$username' AND password='$password' AND user_type='$user_type'";
    $result = $conn->query($sql);
    
    // Check if user exists
    if ($result->num_rows > 0) {
        // Valid user, fetch user data
        $row = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['user_id'] = $row['id'];
        
        // Redirect to successful login page
        header("Location: ./loginsuccesful.php");
        exit();
    } else {
        // Invalid user, redirect to failed login page
        header("Location: ./login.php?error=1");
        exit();
    }
} else {
    // If not submitted via POST, redirect to login page
    header("Location: ./login.php");
    exit();
}

// Close connection
$conn->close();
?>