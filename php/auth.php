<?php
require_once('db.php');
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';


    if ($action === 'login') {
        $username = trim($_POST['username']);

        // Get sha256 of the password
        $password = $_POST['password'];
        $password = hash('sha256', $password);


        $stmt = $pdo->prepare('SELECT id, username, passwordhash, is_admin FROM user WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && ($password == $user['passwordhash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            header('Location: ../index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
            header('Location: ../login.php?error=' . urlencode($error));
            exit;
        }
    }


    if ($action === 'register') {
        $username = trim($_POST['username']);

        // Get sha256 of the password
        $password = $_POST['password'];
        $passwordhash = hash('sha256', $password);
    
        if (strlen($username) < 3 || strlen($password) < 3) {
            $error = 'Username and password must be at least 3 characters long.';
        } else {
            $check = $pdo->prepare('SELECT id FROM user WHERE username = ?');
            $check->execute([$username]);
    
            if ($check->fetch()) {
                $error = 'Username already exists!';
            } else {
                $insert = $pdo->prepare('INSERT INTO user (username, passwordhash) VALUES (?, ?)');
                $insert->execute([$username, $passwordhash]);
                header('Location: ../index.php?registered=1');
                exit;
            }
        }
    }
}
?>