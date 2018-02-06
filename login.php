<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once("config.php");
?>
<head>
	<link rel="manifest" href="manifest.json">
	<link rel="shortcut icon" type="image/png" href="assets/launcher_icons/16.png"/>
	<link rel="apple-touch-icon" href="assets/launcher_icons/192_apple.png">
	<meta name="theme-color" content="#1565C0">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="stylesheet" type="text/css" href="assets/css/colors.css">
	<link rel="stylesheet" type="text/css" href="assets/css/theme.css">
	<link rel="stylesheet" type="text/css" href="assets/css/login.css">
	<?php
		if(!isset($_GET['Refered'])){
			$_GET['Refered'] = "";
		}
		echo "<script>
		var key = '".$ini_array['PrivateKey']."';
		var BaseURL = '".(isset($_SERVER['HTTPS']) ? "https://" : "http://") .$ini_array['BaseURL']."';
		var Refered = '".$_GET['Refered']."';
		</script>";
	?>
	<script type="text/javascript" src="assets/js/jquery-3.2.1.min.js"></script>
	<script type="text/javascript" src="assets/js/cookieHandler.js"></script>
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