<?php
header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; script-src 'self'");
require_once __DIR__ . '/csrf.php';
?>
<html>
<head>
	<title>Welcome to the Khana Khazana Restaurant</title>
</head>

<body>
	<form action="login.php" method="post">
	<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
	Username :  <input type="text" name="username"> 
				<br/>
	Password :  <input type="password" name="password">
				<br/>
				<input type="submit" value="Log In">
	</form>
</body>

</html>
