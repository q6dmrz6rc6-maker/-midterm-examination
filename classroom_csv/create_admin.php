<?php

require_once __DIR__ . '/src/AccountStore.php';

use Classroom\AccountStore;


$store = new AccountStore(__DIR__ . '/data/admin.csv', __DIR__ . '/data/users.csv');

$username = 'minh';
$password = 'minhquang1'; 
$name = 'Administrator';

if ($store->existsUser($username)) {
    echo "Admin '{$username}' already exists.\n";
    exit;
}

$ok = $store->addAdmin($username, password_hash($password, PASSWORD_DEFAULT), $name);
if ($ok) {
    echo "Admin '{$username}' created with password '{$password}'.\n";
    echo "You can now log in at /index.php. Delete this file after use for security.\n";
} else {
    echo "Failed to create admin. Check file permissions for data/admin.csv\n";
}
?>