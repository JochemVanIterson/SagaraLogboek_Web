<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once("../config.php");
	// ------------------------------------------- ADMIN Landing Page ------------------------------------------- //
?>
<head>
	<link rel="manifest" href="../manifest.json">
	<link rel="shortcut icon" type="image/png" href="../assets/launcher_icons/16.png"/>
	<link rel="apple-touch-icon" href="../assets/launcher_icons/192_apple.png">
	<meta name="theme-color" content="#1565C0">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="stylesheet" type="text/css" href="../assets/css/colors.css">
	<link rel="stylesheet" type="text/css" href="../assets/css/theme.css">
	<link rel="stylesheet" type="text/css" href="../assets/css/ui.css">
	<link rel="stylesheet" type="text/css" href="../assets/css/main.css">
	<link rel="stylesheet" type="text/css" href="../assets/css/admin/admin.css">
	
	<script type="text/javascript" src="../assets/js/jquery-3.2.1.min.js"></script>
	<script type="text/javascript" src="../assets/js/cookieHandler.js"></script>
	<script type="text/javascript" src="../assets/js/admin/admin.js"></script>
	<?php //php inits
		include($ini_array['BasePath']."Scripts/SQL.php");
		$SQL = new SQL($ini_array);
		$BaseURL = (isset($_SERVER['HTTPS']) ? "https://" : "http://").$ini_array['BaseURL'];
		$Refered = str_replace($ini_array['RelativeURL'], "", $_SERVER['REQUEST_URI']);
		
		if(!isset($_GET['page'])){
			$_GET['page'] = "Users";
		}
		echo "<script>
			var page = '$_GET[page]';
		</script>";
	?>
	<title>Sagara Logboek | Admin</title>
</head>

<body>

<!----------- Navigation Bar ----------->
<div class='navbar unselectable'>
	<!----------- Content Span Left ----------->
	<div class='navContent'>
		<a href='../'>
			<img class='app_icon' src='../assets/images/app_icon_white.png'>
		</a>
		<?php
		if(isset($_COOKIE['username'])){
			echo "<script>updateSession();</script>";
			$UserData = $SQL->getUser($_COOKIE['username']);
			echo "<span id='UserText'>$UserData[firstname] $UserData[lastname]</span>";
			$URL = $BaseURL;
			echo "<a class='navItem' id='' href='$URL'>Back</a>";
		}
		?>
	</div>
	<!----------- Button Span Right ----------->
	<?php
		if(isset($_COOKIE['username'])){
			echo "<button class='navItem' id='logoutButton' onclick='removeSession(true)'>Log Out</button>";
		} else {
			echo "<button class='navItem' id='logoutButton' onclick=\"location.href='$BaseURL/login.php?Refered=".$Refered."'\">Log In</button>";
		}
	?>
</div>

<?php
if(!isset($_COOKIE['username'])){ // Check for login
	echo "<div class='full_screen_error'>";
	echo "<span style='padding-bottom: 15px;'>You are not logged in</span>";
	echo "<button class='Button' onclick=\"location.href='".$BaseURL."login.php?Refered=".$Refered."'\">Log In</button>";
	echo "</div>";
	die;
}

if(!$UserData['admin']){ // Check if user is allowed
	echo "<div class='full_screen_error'>";
	echo "<span style='padding-bottom: 15px;'>You are not allowed to enter this page</span>";
	echo "<button class='Button' onclick=\"removeSession(false);location.href='".$BaseURL."login.php?Refered=".$Refered."'\">Log in as an Admin User</button>";
	echo "</div>";
	die;
}
?>

<div class='admin_view'>
	<div class='admin_selectors unselectable'>
		<div class='admin_selector_dd' id='admin_selector_dd'>Users</div>
		<div class='admin_selector_itm Selected' id='Users'>Users</div>
		<div class='admin_selector_itm' id='Categories'>Categories</div>
		<div class='admin_selector_itm' id='Items'>Items</div></div>
	<div class='admin_page'>
	</div>
</div>
</body>