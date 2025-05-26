<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPetakom - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%), url('your-background.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            justify-content: flex-start;
        }

        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
            margin: 0 auto;
            margin-top: 100px;
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
        }

        h2 {
            color: #444;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .login-button {
            background-color: #5A9BD6;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
        }

        .login-button:hover {
            background-color: #3A7BB5;
        }

        .forgot-password {
            display: block;
            margin-top: 15px;
            color: #FF8C00;
            text-decoration: none;
        }

        header {
            background-color: #5A9BD6;
            color: white;
            padding: 10px 0;
            text-align: center;
            width: 100%;
            position: fixed;
            top: 0;
            z-index: 1000;
        }

        footer {
            background-color: #333;
            color: white;
            padding: 10px 0;
            text-align: center;
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0 20px;">
            <img src="Logo Petakom.jpg" alt="Logo" style="height: 60px;">
            <h1 style="flex-grow: 1; text-align: center; margin: 0;">WELCOME TO MyPetakom!</h1>
        </div>
    </header>

    <div class="login-container">
        <h2>LOGIN</h2>
        <?php
        // Display error message if redirected from failed login
        if (isset($_GET['error']) && $_GET['error'] == 1) {
            echo '<div class="error-message">Invalid username or password. Please try again.</div>';
        }
        ?>
        <form action="./sessionhandler.php" method="post">
            <div class="input-group">
                <label for="username">STUDENT ID</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">PASSWORD</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="user-type">USER TYPE</label>
                <select id="user-type" name="user_type">
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            <button type="submit" class="login-button">LOGIN</button>
            <a href="forgotpassword.html" class="forgot-password">FORGOT PASSWORD</a>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 MyPetakom. All rights reserved.</p>
    </footer>
</body>
</html>