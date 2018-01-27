<?php
class SQL {
	private $connection;
	private $ini_array;
	
	function __construct($ini_array) {
		$this->ini_array = $ini_array;
		
		$SQLservername = $this->ini_array["msql_server"];
		$SQLusername = $this->ini_array["msql_user"];
		$SQLpassword = $this->ini_array["msql_pwd"];
		$SQLdbname = $this->ini_array["msql_db"];
		
		$this->connection = mysqli_connect($SQLservername, $SQLusername, $SQLpassword, $SQLdbname) or die("Error " . mysqli_error($this->connection));
	}
   
	function Login($user, $password, $publicIV){
		// ---------Check if User in DB--------- //
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sqlq_GetUser = "SELECT * from $prefixUsers WHERE (username = '$user') LIMIT 1";
		$sql_GetUser = mysqli_query($this->connection, $sqlq_GetUser);
		if(mysqli_num_rows($sql_GetUser) == 0){
			return array("login_error"=>"User doesn't exists");
		}
		
		// ---------Check if PW matches UserData--------- //
		$PrivateKey = $this->ini_array['PrivateKey'];
		$UserData = mysqli_fetch_array($sql_GetUser, MYSQLI_ASSOC);
		// Decrypt with temperary key
		$password_dec = Encryption::decrypt($PrivateKey, $publicIV, $password);
		// Encrypt with personal key
		$password_enc = Encryption::encrypt($PrivateKey, $UserData['iv'], $password_dec);
		if($UserData['password'] != $password_enc){
			return array("login_error"=>"Wrong password");
		}
		
		// ---------Login Successful, Update last login--------- //
		$sqlq_Update_LastLogin = "UPDATE $prefixUsers SET last_login=NOW() WHERE username='$user'";
		if (!mysqli_query($this->connection, $sqlq_Update_LastLogin)) {
			echo "Error updating record: " . mysqli_error($this->connection);
		    return array("login_error"=>"Database error");
		}
		return $UserData;
	}
}
?>