<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: user_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Login</title>
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <main>
        <h2 id="form-title">Login</h2>

        <?php if(isset($_GET['registered'])) echo '<p style="color:green;">Registration successful! You can now log in.</p>'; ?>

        <form method="POST" action="php/auth.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="hidden" name="action" id="action" value="login">
            <button type="submit" id="submit-btn">Login</button>
        </form>
        
        <div class="toggle">
            <p id="toggle-text"><a href="#" onclick="toggleForm()">Create account</a></p>
        </div>
    </main>

    <script src="./js/index.js"></script>
</body>
</html>
