<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once("config.php");
	// ------------------------------------------- MAIN Landing Page ------------------------------------------- //
?>
<head>
	<link rel="manifest" href="manifest.json">
	<link rel="shortcut icon" type="image/png" href="assets/launcher_icons/16.png"/>
	<link rel="apple-touch-icon" href="assets/launcher_icons/192_apple.png">
	<meta name="theme-color" content="#1565C0">

	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="stylesheet" type="text/css" href="assets/css/colors.css">
	<link rel="stylesheet" type="text/css" href="assets/css/theme.css">
	<link rel="stylesheet" type="text/css" href="assets/css/main.css">
	<script type="text/javascript" src="assets/js/cookieHandler.js"></script>
	<?php //php inits
		include($ini_array['BasePath']."Scripts/SQL.php");
		$SQL = new SQL($ini_array);
	?>
	<title>Sagara Logboek</title>
</head>

<body>

<!----------- Navigation Bar ----------->
<div class='navbar'>
	<!----------- Content Span Left ----------->
	<div class='navContent'>
		<img class='app_icon' src='assets/images/app_icon_white.png'>
		<?php
		if(isset($_COOKIE['username'])){
			echo "<script>updateSession();</script>";
			$UserData = $SQL->getUser($_COOKIE['username']);
			echo "<span id='UserText'>$UserData[firstname] $UserData[lastname]</span>";
			// check user premissions (admin)
			if($UserData['admin']){
				$URL = (isset($_SERVER['HTTPS']) ? "https://" : "http://").$ini_array['BaseURL']."admin/";
				echo "<a class='navItem' href='$URL'>Admin</a>";
			}
		}
		?>
	</div>
	<!----------- Button Span Right ----------->
	<?php
		if(isset($_COOKIE['username'])){
			echo "<button class='navItem' id='logoutButton' onclick='removeSession(true)'>Log Out</button>";
		} else {
			$Refered = str_replace($ini_array['RelativeURL'], "", $_SERVER['REQUEST_URI']);
			echo "<button class='navItem' id='logoutButton' onclick=\"location.href='login.php?Refered=".$Refered."'\">Log In</button>";
		}
	?>
</div>

<!----------- Content ----------->
<div class='content_view' style=''></div>

</body>