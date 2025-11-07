<?php

function showAccountButton($edit = 'name') {
    echo '<div class="change-password-container">
    <details class="account-button-card">';
    editAccount($edit);
    echo '</details>
    </div>';
}

function editAccount($edit) {
    if ($edit === 'name') {
        echo '
        <summary><h2 class="change-password-header">Change Username</h2></summary>
        <form method="POST" class="change-password-form">
            <input type="hidden" name="action" value="change_username">

            <label for="new_username">New Username:</label><br>
            <input type="text" id="new_username" name="new_username" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <button type="submit">Change Username</button>
        </form>';
    } elseif ($edit === 'password') {
        echo '
        <summary><h2 class="change-password-header">Change Password</h2></summary>
        <form method="POST" class="change-password-form">
            <input type="hidden" name="action" value="change_password">

            <label for="old_password">Old Password:</label><br>
            <input type="password" id="old_password" name="old_password" required><br><br>

            <label for="new_password">New Password:</label><br>
            <input type="password" id="new_password" name="new_password" required><br><br>

            <label for="confirm_password">Confirm New Password:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>

            <button type="submit">Change Password</button>
        </form>';
    }
}

function passwordChangeMessage() {
    if (isset($_SESSION['change_password_message']) && $_SESSION['change_password_message'] !== '') {
        if ($_SESSION['change_password_message'] === 'Password changed successfully.' || $_SESSION['change_password_message'] === 'Username changed successfully.') {
            echo '<p class="change-password-message success-card">' . $_SESSION['change_password_message'] . '</p>';
        } else {
            echo '<p class="change-password-message error-card">' . $_SESSION['change_password_message'] . '</p>';
        }
        // Clear the message after displaying
        $_SESSION['change_password_message'] = '';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') { 
    require_once('db.php');
    // if session not started, start it
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    $userId = $_SESSION['user_id'];
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Get sha256 of the passwords
    $oldPasswordHash = hash('sha256', $oldPassword);
    $newPasswordHash = hash('sha256', $newPassword);

    // Fetch current password hash from database
    $stmt = $pdo->prepare('SELECT passwordhash FROM user WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && ($oldPasswordHash === $user['passwordhash'])) {
        if ($newPassword === $confirmPassword) {
            // Update password in database
            $update = $pdo->prepare('UPDATE user SET passwordhash = ? WHERE id = ?');
            $update->execute([$newPasswordHash, $userId]);
            $_SESSION['change_password_message'] = 'Password changed successfully.';
        } else {
            $_SESSION['change_password_message'] = 'New passwords do not match.';
        }
    } else {
        $_SESSION['change_password_message'] = 'Old password is incorrect.';
    }
    
}

// Handle username change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_username') { 
    require_once('db.php');
    // if session not started, start it
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    $userId = $_SESSION['user_id'];
    $newUsername = $_POST['new_username'];
    $password = $_POST['password'];

    // Get sha256 of the password
    $passwordHash = hash('sha256', $password);

    // Fetch current password hash from database
    $stmt = $pdo->prepare('SELECT passwordhash FROM user WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && ($passwordHash === $user['passwordhash'])) {
        // Update username in database
        $update = $pdo->prepare('UPDATE user SET username = ? WHERE id = ?');
        $update->execute([$newUsername, $userId]);
        $_SESSION['change_password_message'] = 'Username changed successfully.';
        // Update session username
        $_SESSION['username'] = $newUsername;
    } else {
        $_SESSION['change_password_message'] = 'Password is incorrect.';
    }
    
}