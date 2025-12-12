<?php
require_once __DIR__ . '/csrf.php';
// Basic security headers
header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; style-src 'self'; img-src 'self' data:; script-src 'self'");
// Secure-ish login using prepared statements (prevents SQL injection)
if (isset($_POST['username'])) {
	// Verify CSRF token for login
	$tok = $_POST['csrf_token'] ?? '';
	if (!csrf_check($tok)) {
		http_response_code(403);
		die('Invalid CSRF token');
	}
	$uname = isset($_POST['username']) ? trim($_POST['username']) : '';
	$pwd1 = isset($_POST['password']) ? trim($_POST['password']) : '';

	require_once __DIR__ . '/connect.php'; // connects to 'restaurant'
	if (!isset($con) || !($con instanceof mysqli)) {
		die('Unable to connect..!!');
	}

	// Ensure user table exists; create if missing and seed minimal admin
	$con->query("CREATE TABLE IF NOT EXISTS `user` (
	  `id` INT AUTO_INCREMENT PRIMARY KEY,
	  `username` VARCHAR(50) NOT NULL UNIQUE,
	  `password` VARCHAR(255) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=latin1");
	// Seed admin if table is empty
	$check = $con->query("SELECT COUNT(*) AS c FROM user");
	if ($check && ($row = $check->fetch_assoc()) && intval($row['c']) === 0) {
		$con->query("INSERT INTO user (username, password) VALUES ('admin', 'admin123')");
	}

	// Use prepared statement to avoid SQL injection
	$stmt = $con->prepare('SELECT id FROM user WHERE username = ? AND password = ? LIMIT 1');
	if (!$stmt) { die('Prepare failed'); }
	$stmt->bind_param('ss', $uname, $pwd1);
	$stmt->execute();
	$stmt->store_result();
	if ($stmt->num_rows === 1) {
		echo 'Login Successfully...!!';
		header('refresh:2; url=mainpage.html');
	} else {
		echo 'Failed to login';
	}
	$stmt->close();
}
?>