<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../config.php");
include($ini_array['BasePath']."Scripts/SQL.php");
include($ini_array['BasePath']."Scripts/Encryption.php");

if(!isset($_POST['user']) || $_POST['user']=="" || !isset($_POST['pw']) || $_POST['pw']==""){
	$ErrorState = array(
		"login"=>"failed", "message"=>"", "header"=>getallheaders(), "POST"=>$_POST
	);
	if(!isset($_POST['user']) || $_POST['user']==""){
		if($ErrorState["message"]!="")$ErrorState["message"].=", ";
		$ErrorState["message"].="User Empty";
		//$ErrorState["message"].="User Empty, ".json_encode($_POST, true);
	}
	if(!isset($_POST['pw']) || $_POST['pw']==""){
		if($ErrorState["message"]!="")$ErrorState["message"].=", ";
		$ErrorState["message"].="PW Empty, ".json_encode($_POST, true);
	}
	echo json_encode($ErrorState, true);
	die;
}

if(isset($_POST['raw'])){
	$PrivateKey = $ini_array['PrivateKey'];
	$_POST['iv'] = Encryption::randomString(16);
	$_POST['pw'] = Encryption::encrypt($PrivateKey, $_POST['iv'], $_POST['pw']);
}

$SQL = new SQL($ini_array);
$Login_attempt = $SQL->Login($_POST['user'], $_POST['pw'], $_POST['iv']);
if(isset($Login_attempt['login_error'])){
	echo json_encode(array(
		"login"=>"failed",
		"message"=>$Login_attempt['login_error'],
		"Login_data"=>array(
			"user"=>$_POST['user'],
			"pw"=>$_POST['pw'],
			"iv"=>$_POST['iv'],
			"pkey"=>$ini_array['PrivateKey']
		)
	), true);
	die;
}

unset($Login_attempt['password']);
echo json_encode(array("login"=>"success", "data"=>$Login_attempt), true);
?>