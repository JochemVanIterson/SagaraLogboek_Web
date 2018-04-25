<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../config.php");
include($ini_array['BasePath']."Scripts/SQL.php");
include($ini_array['BasePath']."Scripts/Encryption.php");

$SQL = new SQL($ini_array);

//echo json_encode($_POST);

$response = array();

if($_GET['action'] == "Update"){
	$SQL->updateItem($_POST);
} else if($_GET['action'] == "Remove"){
	$SQL->removeItem($_POST['id']);
} else if($_GET['action'] == "Add"){
	$SQL->addItem($_POST);
} else if($_GET['action'] == "Get"){
	$data = $SQL->getItemEntries($_POST);
	echo json_encode($data, true);
}


?>