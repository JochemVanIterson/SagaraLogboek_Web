<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once("../config.php");

	if(!isset($_GET['included'])){
		echo "Not allowed";
		die;
	}
?>
<link rel="stylesheet" type="text/css" href="../assets/css/colors.css">
<link rel="stylesheet" type="text/css" href="../assets/css/admin/admin.css">
<link rel="stylesheet" type="text/css" href="../assets/css/ui.css">
<link rel="stylesheet" type="text/css" href="../assets/css/main.css">
<link rel="stylesheet" type="text/css" href="../assets/css/admin/users.css">

<script type="text/javascript" src="../assets/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="../assets/js/admin/Users.js"></script>

	
<div id="admin_content">
<?php //php inits
	include($ini_array['BasePath']."Scripts/SQL.php");
	$SQL = new SQL($ini_array);
	$BaseURL = (isset($_SERVER['HTTPS']) ? "https://" : "http://").$ini_array['BaseURL'];
	echo "<script>
		var BaseURL = '$BaseURL';
	</script>";
?>
	
<?php
	$Users = $SQL->getUsers();
	foreach($Users as $UserItm){
		echo "<div class='list_element' id='$UserItm[username]'>";
			if($UserItm['admin']==1){
				$isadmin = "admin";
			} else {
				$isadmin = "";
			}
			echo "<div class='unselectable header $isadmin'>";
				echo "$UserItm[firstname] $UserItm[lastname]";
			echo "</div>";
			echo "<div class='content'>";
				echo "<form id='$UserItm[username]' method='POST'>";
					echo "<table class='UserForm'>";
						echo "<input type='hidden' name='username' value='$UserItm[username]'>";
						echo "<tr>";
							echo "<td class='unselectable'>User Name</td>";
							echo "<td class='unselectable'>$UserItm[username]</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td class='unselectable'>Admin</td>";
							echo "<td>";
								echo "<label class='switch'>";
									($UserItm['admin'])?$Checked="checked":$Checked="";
									echo "<input $Checked type='checkbox' name='admin'>";
									echo "<span class='slider round'></span>";
								echo "</label>";
							echo "</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td class='unselectable'>First Name</td>";
							echo "<td style='padding:0px;'><input type='text' name='firstname' value='$UserItm[firstname]'></td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td class='unselectable'>Last Name</td>";
							echo "<td style='padding:0px;'><input type='text' name='lastname' value='$UserItm[lastname]'></td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td class='unselectable'>Mail</td>";
							echo "<td style='padding:0px;'><input type='text' name='mail' value='$UserItm[mail]'></td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td class='unselectable'>Password</td>";
							echo "<td style='padding:0px;'><input type='password' name='password' placeholder='Change Password'></td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td class='unselectable'>Last Login</td>";
							echo "<td class='unselectable'>$UserItm[last_login]</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td style='padding-right:0px;' align='right' colspan='2'>";
								echo "<button class='removeButton type='submit' formaction='".$BaseURL."Scripts/User.php?action=Remove'>Remove</button>";
								echo "<button class='saveButton type='submit' formaction='".$BaseURL."Scripts/User.php?action=Update'>Save</button>";
							echo "</td>";
						echo "</tr>";
					echo "</table>";
				echo "</form>";
			echo "</div>";
		echo "</div>";
	}
?>

<div class='list_element' id='AddUser'>
	<div class='unselectable header'>
		Add User
	</div>
	<div class='content'>
		<form class='AddUserForm' id='AddUser' method='POST'>
			<table class='UserForm'>
				<tr>
					<td class='unselectable'>User Name</td>
					<td style='padding:0px;'><input type='text' name='username'></td>
				</tr>
				<tr>
					<td class='unselectable'>Admin</td>
					<td>
						<label class='switch'>
							<input type='checkbox' name='admin'>
							<span class='slider round'></span>
						</label>
					</td>
				</tr>
				<tr>
					<td class='unselectable'>First Name</td>
					<td style='padding:0px;'><input type='text' name='firstname'></td>
				</tr>
				<tr>
					<td class='unselectable'>Last Name</td>
					<td style='padding:0px;'><input type='text' name='lastname'></td>
				</tr>
				<tr>
					<td class='unselectable'>Mail</td>
					<td style='padding:0px;'><input type='text' name='mail'></td>
				</tr>
				<tr>
					<td class='unselectable'>Password</td>
					<td style='padding:0px;'><input type='password' name='password'></td>
				</tr>
				<tr>
					<td style='padding-right:0px;' align='right' colspan='2'>
						<button class='saveButton' onClick='addUser()'>Save</button>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
</div>