<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../config.php");
//echo json_encode($ini_array, true);

$usernames = array("jochem", "jeroen", "anne", "martine");

if(!isset($_POST['user']) || $_POST['user']=="" || !isset($_POST['pw']) || $_POST['pw']==""){
	echo json_encode(array("login"=>"failed", "message"=>"no credentials"), true);
	die;
}
include($ini_array['BasePath']."Scripts/SQL.php");
$SQL = new SQL($ini_array);

if(!in_array($_POST['user'], $usernames)){
	echo json_encode(array("login"=>"failed", "message"=>"user not found"), true);
	die;
} else {
	
	echo json_encode(array("login"=>"success"), true);
}
?>