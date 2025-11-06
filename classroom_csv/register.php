<?php


require_once __DIR__ . '/src/AccountStore.php';
require_once __DIR__ . '/src/Auth.php';

use Classroom\AccountStore;
use Classroom\Auth;

$store = new AccountStore(__DIR__ . '/data/admin.csv', __DIR__ . '/data/users.csv');
$auth = new Auth($store);

$errors = [];
$values = ['username' => '', 'name' => ''];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $name     = trim((string)($_POST['name'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $password2= (string)($_POST['password2'] ?? '');

    $values['username'] = $username;
    $values['name'] = $name;

    
    if ($username === '') $errors[] = 'Username cannot be blank.';
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) $errors[] = 'Username must contain only letters/numbers/_ and be 3-30 characters long.';
    if (strlen($password) < 6) $errors[] = 'Passoword must contain at least 6 characters.';
    if ($password !== $password2) $errors[] = 'Confirmation password does not match.';

    
    if (empty($errors)) {
        $res = $auth->registerUser($username, $password, $name);
        if (empty($res['ok'])) {
            $errors[] = $res['error'] ?? 'Registration failed.';
        } else {
            
            header('Location: index.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Sign Up - Classroom Manager</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
  <div class="login-box" role="main" aria-labelledby="register-title">
    <h2 id="register-title">Register an account</h2>

    <?php if (!empty($errors)): ?>
      <ul style="color:#b91c1c;padding-left:18px">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <form method="post" novalidate>
      <input type="text" name="username" placeholder="Username" required value="<?= htmlspecialchars($values['username']) ?>"><br>
      <input type="text" name="name" placeholder="Họ tên (tùy chọn)" value="<?= htmlspecialchars($values['name']) ?>"><br>
      <input type="password" name="password" placeholder="Password" required><br>
      <input type="password" name="password2" placeholder="Confirm Password" required><br>
      <button type="submit" class="btn">Register</button>
    </form>

    <p class="text-muted small"><a href="index.php">Back to login</a></p>
  </div>
</body>
</html>