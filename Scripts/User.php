<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../config.php");
include($ini_array['BasePath']."Scripts/SQL.php");
include($ini_array['BasePath']."Scripts/Encryption.php");

$SQL = new SQL($ini_array);

echo json_encode($_POST);

if($_GET['action'] == "Update"){
	$SQL->updateUser($_POST);
} else if($_GET['action'] == "Remove"){
	$SQL->removeUser($_POST['username']);
} else if($_GET['action'] == "Add"){
	$SQL->addUser($_POST);
}

?>