<?php
require_once('db.php');
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';


    if ($action === 'login') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];


        $stmt = $pdo->prepare('SELECT id, username, password_, is_admin FROM user WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($user && ($password == $user['password_'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            header('Location: ../user_dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
            header('Location: ../index.php');
            exit;
        }
    }


    if ($action === 'register') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
    
        if (strlen($username) < 3 || strlen($password) < 3) {
            $error = 'Username and password must be at least 3 characters long.';
        } else {
            $check = $pdo->prepare('SELECT id FROM user WHERE username = ?');
            $check->execute([$username]);
    
            if ($check->fetch()) {
                $error = 'Username already exists!';
            } else {
                $insert = $pdo->prepare('INSERT INTO user (username, password_) VALUES (?, ?)');
                $insert->execute([$username, $password]);
                header('Location: ../index.php?registered=1');
                exit;
            }
        }
    }
}
?>