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
  <style>
    body { font-family: Arial; background: #f2f2f2; display: flex; align-items: center; justify-content: center; height: 100vh; }
    .container { background: white; padding: 2em; border-radius: 10px; width: 300px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; }
    input { width: 93.7%; padding: 8px; margin: 6px 0; }
    button { width: 100%; padding: 10px; margin-top: 10px; cursor: pointer; }
    .toggle { text-align: center; margin-top: 10px; }
  </style>
</head>
<body>
<div class="container">
  <h2 id="form-title">Login</h2>
  <?php if(isset($_GET['registered'])) echo '<p style="color:green;">Registration successful! You can now log in.</p>'; ?>
  <form method="POST" action="auth.php">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="hidden" name="action" id="action" value="login">
    <button type="submit" id="submit-btn">Login</button>
  </form>
  <div class="toggle">
    <p id="toggle-text"><a href="#" onclick="toggleForm()">Create account</a></p>
  </div>
</div>

<script>
let isLogin = true;
function toggleForm() {
  const formTitle = document.getElementById('form-title');
  const action = document.getElementById('action');
  const submitBtn = document.getElementById('submit-btn');
  const toggleText = document.getElementById('toggle-text');
  
  if (isLogin) {
    formTitle.innerText = 'Register';
    action.value = 'register';
    submitBtn.innerText = 'Create Account';
    toggleText.innerHTML = '<a href="#" onclick="toggleForm()">Login</a>';
  } else {
    formTitle.innerText = 'Login';
    action.value = 'login';
    submitBtn.innerText = 'Login';
    toggleText.innerHTML = '<a href="#" onclick="toggleForm()">Create account</a>';
  }
  isLogin = !isLogin;
}
</script>
</body>
</html>
