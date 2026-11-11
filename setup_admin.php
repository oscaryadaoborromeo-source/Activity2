<?php
require_once 'config.php';

$username = 'admin';
$password = 'Admin@12345';  

$check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$check->execute([$username]);

if ($check->fetch()) {
    echo "Admin already exists. Delete this file.";
} else {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, status, role) VALUES (?, ?, 'approved', 'admin')");
    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
    echo "Admin created. Username: $username / Password: $password. NOW DELETE setup_admin.php.";
}
