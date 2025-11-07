<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Login</title>
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <main>
        <header>
            <h2 id="form-title">Login</h2>
        </header>
        
        <?php if(isset($_GET['error'])) echo '<p style="color:red;" id="error-message">' . htmlspecialchars($_GET['error']) . '</p>'; ?>
        
        <?php if(isset($_GET['registered'])) echo '<p style="color:green;">Registration successful! You can now log in.</p>'; ?>
        
        <form method="POST" action="php/auth.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="hidden" name="action" id="action" value="login">
            <button type="submit" id="submit-btn">Login</button>
        </form>
        
        <div class="toggle">
            <p id="toggle-text"><a href="#" onclick="toggleForm()">Create account</a></p>
            <a href="./index.php" id="back-button">Back</a>
        </div>
    </main>

    <script src="./js/login.js"></script>
</body>
</html>
