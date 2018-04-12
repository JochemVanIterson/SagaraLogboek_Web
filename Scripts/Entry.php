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

if($_GET['action'] == "Insert"){
	$Data['entry_id'] = $SQL->addEntry($_POST);
} else if($_GET['action'] == "Update"){
	$Data['Update'] = $SQL->updateEntry($_POST);
} else if($_GET['action'] == "UpdateMulti"){
	$Data['Update'] = $SQL->updateMultiEntry($_POST);
} else if($_GET['action'] == "Finish"){
	$Data['Finish'] = $SQL->finishEntry($_POST);
} else if($_GET['action'] == "Get"){
	$Data['data'] = $SQL->getLastEntry($_POST);
} else if($_GET['action'] == "SingleInsert"){
	$Data['data'] = $SQL->addSingleEntry($_POST);
}
echo json_encode($Data);
?>