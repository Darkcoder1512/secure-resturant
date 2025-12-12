<?php
// Central DB connection with externalized credentials
// Reads MySQL credentials from env vars or a local config file to avoid hardcoding

// Try environment variables first
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: null;
$dbPass = getenv('DB_PASS') ?: null;
$dbName = getenv('DB_NAME') ?: 'restaurant';

// If env not set, try config file
if ($dbUser === null || $dbPass === null) {
	$configPath = __DIR__ . '/db.config.php';
	if (file_exists($configPath)) {
		$cfg = include $configPath; // expects ['host','user','pass','name']
		$dbHost = $cfg['host'] ?? $dbHost;
		$dbUser = $cfg['user'] ?? 'root';
		$dbPass = $cfg['pass'] ?? '';
		$dbName = $cfg['name'] ?? $dbName;
	} else {
		// Fallback to local dev defaults
		$dbUser = 'root';
		$dbPass = '';
	}
}

// First connect without specifying a database
$con = new mysqli($dbHost, $dbUser, $dbPass);
if ($con->connect_error) {
	die('Unable to connect..!!');
}

// Create database if it doesn't exist, then select it
$con->query("CREATE DATABASE IF NOT EXISTS `" . $con->real_escape_string($dbName) . "`");
if (!$con->select_db($dbName)) {
	die('Unable to select database..!!');
}
?>