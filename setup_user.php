<?php
require_once __DIR__ . '/connect.php';
if (!isset($con) || !($con instanceof mysqli)) {
    die('Unable to connect..!!');
}

$con->set_charset('utf8mb4');

// Create user table if it doesn't exist (plaintext for simple login)
$ddl = "CREATE TABLE IF NOT EXISTS `user` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

if (!$con->query($ddl)) {
    die('Failed to create user table');
}

// Seed admin if not present
$check = $con->prepare('SELECT id FROM user WHERE username = ?');
$u = 'admin';
$check->bind_param('s', $u);
$check->execute();
$res = $check->get_result();
if (!$res || $res->num_rows === 0) {
    $pwd = 'admin123';
    $ins = $con->prepare('INSERT INTO user (username, password) VALUES (?, ?)');
    $ins->bind_param('ss', $u, $pwd);
    $ins->execute();
    echo 'Seeded admin user (admin/admin123)';
} else {
    echo 'User table exists; admin already present';
}

?>