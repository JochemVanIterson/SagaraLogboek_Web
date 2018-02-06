<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../config.php");
include($ini_array['BasePath']."Scripts/SQL.php");
include($ini_array['BasePath']."Scripts/Encryption.php");

$SQL = new SQL($ini_array);

echo json_encode($_POST);

if(isset($_FILES["icon_vector"])){
	$file = $_FILES["icon_vector"];
	
	$extension = array_values(array_slice(explode('.', $file['name']), -1))[0];
	if($extension!=="svg"){
		die(json_encode(array("error"=>"filetype")));
	}
	$_POST['icon_vector'] = $file;
	echo json_encode($file);
}

if($_GET['action'] == "Update"){
	$SQL->updateCategory($_POST);
} else if($_GET['action'] == "Remove"){
	$SQL->removeCategory($_POST['id']);
} else if($_GET['action'] == "Add"){
	$SQL->addCategory($_POST);
}

?>