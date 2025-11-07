<?php
require_once('db.php');

function showAccountButton() {
    // Track toggle state in session
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
    <form method="POST">
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
    <h2>Change Password</h2>
    <form method="POST">
        <input type="hidden" name="action" value="change_password">

        <label for="old_password">Old Password:</label><br>
        <input type="password" id="old_password" name="old_password" required><br><br>

        <label for="new_password">New Password:</label><br>
        <input type="password" id="new_password" name="new_password" required><br><br>

        <label for="confirm_password">Confirm New Password:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <button type="submit">Change Password</button>
    </form>
    ';
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $userId = $_SESSION['user_id'];
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Hash passwords
    $oldPasswordHash = hash('sha256', $oldPassword);
    $newPasswordHash = hash('sha256', $newPassword);

    // Fetch current password hash from database
    $stmt = $pdo->prepare('SELECT passwordhash FROM user WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $oldPasswordHash === $user['passwordhash']) {
        if ($newPassword === $confirmPassword) {
            // Update password in database
            $update = $pdo->prepare('UPDATE user SET passwordhash = ? WHERE id = ?');
            $update->execute([$newPasswordHash, $userId]);
            echo 'Password changed successfully.';
        } else {
            echo 'New passwords do not match.';
        }
    } else {
        echo 'Old password is incorrect.';
    }
}
