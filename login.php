<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once("config.php");
?>
<head>
	<link rel="stylesheet" type="text/css" href="assets/css/theme.css">
	<link rel="stylesheet" type="text/css" href="assets/css/login.css">
	<?php
		echo "<script>
		var key = '".$ini_array['PrivateKey']."';
		</script>";
	?>
	<script type="text/javascript" src="assets/js/jquery-3.2.1.min.js"></script>
	<script type="text/javascript" src="assets/js/crypto-js/crypto-js.js"></script>
	<script type="text/javascript" src="assets/js/login_check.js"></script>
	<title>Sagara Logboek | Login</title>
</head>
<body>
	<div class="login_container">
		<img class="login_icon" src="assets/images/app_icon.png">
		<div class="login_box">
			<form id="login_data">
				Username or Email Address<br>
				<input type="text" name="user_login" id="user_login" class="input" value="" size="20">
				Password<br>
				<input type="password" name="password_login" id="password_login" class="input" value="" size="20">
			</form>
			<div style="height: 32px">
				<div class="error_view"></div>
				<button id="login_button">Log In</button>
			</div>
		</div>
	</div>
</body>