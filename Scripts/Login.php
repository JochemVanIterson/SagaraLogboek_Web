<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../config.php");
include($ini_array['BasePath']."Scripts/SQL.php");
include($ini_array['BasePath']."Scripts/Encryption.php");

//$_POST['user'] = "jochem.vaniterson";
//$_POST['pw'] = "ruimte";
//
//$PrivateKey = $ini_array['PrivateKey'];
//$PersonalKey = "7hz7brbszvzq2zn7";
//$password_enc = Encryption::encrypt($PrivateKey, $PersonalKey, $_POST['pw']);
////echo $password_enc;
//$_POST['pw'] = $password_enc;
//$_POST['iv'] = $PersonalKey;

if(!isset($_POST['user']) || $_POST['user']=="" || !isset($_POST['pw']) || $_POST['pw']==""){
	echo json_encode(array("login"=>"failed", "message"=>"no credentials"), true);
	die;
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