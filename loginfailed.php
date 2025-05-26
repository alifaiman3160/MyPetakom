<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPetakom - Login Failed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            justify-content: flex-start;
        }
        
        header {
            background-color: #5A9BD6;
            color: white;
            padding: 10px 0;
            text-align: center;
            width: 100%;
        }
        
        .error-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
            margin: 0 auto;
            margin-top: 100px;
        }
        
        h1, h2 {
            color: #333;
        }
        
        .error-message {
            color: #FF0000;
            font-size: 18px;
            margin: 20px 0;
        }
        
        .back-button {
            background-color: #5A9BD6;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-button:hover {
            background-color: #3A7BB5;
        }
        
        footer {
            background-color: #333;
            color: white;
            padding: 10px 0;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0 20px;">
            <img src="Logo Petakom.jpg" alt="Logo" style="height: 60px;">
            <h1 style="flex-grow: 1; text-align: center; margin: 0;">MyPetakom</h1>
        </div>
    </header>

    <div class="error-container">
        <h2>Login Failed</h2>
        <div class="error-message">
            Invalid username or password. Please check your credentials and try again.
        </div>
        <a href="./login.php" class="back-button">Back to Login</a>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 MyPetakom. All rights reserved.</p>
    </footer>
</body>
</html>