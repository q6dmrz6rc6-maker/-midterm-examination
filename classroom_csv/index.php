<?php


session_start();

require_once __DIR__ . '/src/AccountStore.php';
require_once __DIR__ . '/src/Auth.php';

use Classroom\AccountStore;
use Classroom\Auth;

$store = new AccountStore(__DIR__ . '/data/admin.csv', __DIR__ . '/data/users.csv');
$auth  = new Auth($store);

$error = '';
$success = '';


if (!empty($_GET['registered'])) {
    $success = 'Registration successful. Please log in.';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    
    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        
        $user = $auth->authenticate($username, $password);
        if ($user !== false) {
            $_SESSION['user'] = $user; 
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Login - Classroom Manager</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
  <div class="login-box" role="main" aria-labelledby="login-title">
    <h2 id="login-title">Classroom Manager</h2>

    <?php if ($success): ?><p class="text-muted"><?= htmlspecialchars($success) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="post" novalidate>
      <input type="text" name="username" placeholder="Username" required autocomplete="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"><br>
      <input type="password" name="password" placeholder="Password" required autocomplete="current-password"><br>
      <button type="submit" class="btn">Login</button>
    </form>

    <p class="text-muted small">Don't have account? <a href="register.php">Register</a></p>
  </div>
</body>
</html>