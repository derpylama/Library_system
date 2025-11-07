<?php

function showAccountButton() {
    // Track toggle state in session
    if (!isset($_SESSION['change_password_message'])) {
        $_SESSION['change_password_message'] = '';
    }
    if (!isset($_SESSION['show_change_password'])) {
        $_SESSION['show_change_password'] = false;
    }

    // If button is pressed, flip the state
    if (isset($_POST['toggle_change_password'])) {
        $_SESSION['show_change_password'] = !$_SESSION['show_change_password'];
    }

    // Choose button label based on state
    $buttonText = $_SESSION['show_change_password'] ? 'Close Edit Password' : 'Open Edit Password';

    // Display the button
    echo '
    <form method="POST" class="change-password-button">
        <button type="submit" name="toggle_change_password">' . $buttonText . '</button>
    </form>
    ';

    // If state is true, show the edit form
    if ($_SESSION['show_change_password']) {
        editAccount();
    }
}

function editAccount() {
    echo '
    <div class="change-password-container card">
    <h2 class="change-password-header">Change Password</h2>
    <form method="POST" class="change-password-form">
        <input type="hidden" name="action" value="change_password">

        <label for="old_password">Old Password:</label><br>
        <input type="password" id="old_password" name="old_password" required><br><br>

        <label for="new_password">New Password:</label><br>
        <input type="password" id="new_password" name="new_password" required><br><br>

        <label for="confirm_password">Confirm New Password:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <button type="submit">Change Password</button>
    </form>
    </div>
    ';
}

function passwordChangeMessage() {
    if (isset($_SESSION['change_password_message']) && $_SESSION['change_password_message'] !== '') {
        if ($_SESSION['change_password_message'] === 'Password changed successfully.') {
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
