<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../config.php");
include($ini_array['BasePath']."Scripts/SQL.php");

$SQL = new SQL($ini_array);
$Data = array();

if(!isset($_COOKIE['username']) || !isset($_COOKIE['iv'])){
	if(!isset($_POST['username']) || !isset($_POST['iv'])){
		die(json_encode(array("login_error" => "missing credentials")));
	}
	$_COOKIE['username'] = $_POST['username'];
	$_COOKIE['iv'] = $_POST['iv'];
}

$LoginCheck = $SQL->CheckLogin($_COOKIE);
if(isset($LoginCheck['login_error'])){
	$Data['login_error'] = $LoginCheck['login_error'];
	die(json_encode($Data));
}

//echo json_encode($_POST);


$Data["data"] = $SQL->getLastLocations($_POST);
echo json_encode($Data, true);

?>