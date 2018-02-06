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
	
	function addUser($post_data){
		if(isset($post_data['admin']) && $post_data['admin'] == "on"){
			$post_data['admin']=1;
		} else {
			$post_data['admin']=0;
		}
		$PrivateKey = $this->ini_array['PrivateKey'];
		$Firstname = $post_data['firstname'];
		$Lastname = $post_data['lastname'];
		$Username = $post_data['username'];
		$Mail = $post_data['mail'];
		$Admin = $post_data['admin'];
		$IV = $this->randomString(16); //Unieke code per user, voor "end to end" encryptie
		$PasswordRaw = $post_data['password'];
		$PasswordEnc = Encryption::encrypt($PrivateKey, $IV, $PasswordRaw);
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sql_Insert_User = "REPLACE INTO $prefixUsers (firstname, lastname, username, mail, admin, password, iv)
		VALUES ('$Firstname', '$Lastname', '$Username', '$Mail', $Admin, '$PasswordEnc', '$IV')";
		
		if (!mysqli_query($this->connection, $sql_Insert_User)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}
		
	}
	function updateUser($post_data){
		if($post_data['password'] == ""){
			unset($post_data['password']);
		} else {
			$IV = $this->randomString(16); //Unieke code per user, voor "end to end" encryptie
			$PasswordRaw = $post_data['password'];
			$PrivateKey = $this->ini_array['PrivateKey'];
			$PasswordEnc = Encryption::encrypt($PrivateKey, $IV, $PasswordRaw);
			$post_data['password'] = $PasswordEnc;
			$post_data['iv'] = $IV;
		}
		if(isset($post_data['admin']) && $post_data['admin'] == "on"){
			$post_data['admin']=1;
		} else {
			$post_data['admin']=0;
		}
		$username = $post_data['username'];
		unset($post_data['username']);
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sql_update_user = "UPDATE $prefixUsers SET ";
		foreach($post_data as $key => $value){
			if($sql_update_user != "UPDATE $prefixUsers SET ")$sql_update_user .= ", ";
			$sql_update_user .= "$key='$value'";
		}
		$sql_update_user .= "WHERE username='$username'";
		
		if (!mysqli_query($this->connection, $sql_update_user)) {
			return mysqli_connect_error();
		}
	}
	function removeUser($user){
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sql_remove_user = "DELETE FROM $prefixUsers WHERE username='$user'";
		echo $sql_remove_user;
		if (!mysqli_query($this->connection, $sql_remove_user)) {
			echo mysqli_connect_error();
		}
	}
	function getUser($user){
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sqlq_GetUser = "SELECT * from $prefixUsers WHERE (username = '$user') LIMIT 1";
		$sql_GetUser = mysqli_query($this->connection, $sqlq_GetUser);
		if(mysqli_num_rows($sql_GetUser) == 0){
			return null;
		}
		$UserData = mysqli_fetch_array($sql_GetUser, MYSQLI_ASSOC);
		return $UserData;
	}
	function getUsers(){
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sqlq_GetUsers = "SELECT * from $prefixUsers ORDER BY firstname ASC";
		$sql_GetUsers = mysqli_query($this->connection, $sqlq_GetUsers);
		if(mysqli_num_rows($sql_GetUsers) == 0){
			return null;
		}
		while ($row_user = mysqli_fetch_assoc($sql_GetUsers))
			$UserData[] = $row_user;
		return $UserData;
	}
	
	function addCategory($post_data){
		$Name = $post_data['name'];
		$Fields = array();
		foreach($post_data as $key => $value){
			if (strpos($key, 'field') !== false) {
				$keydata = explode("_", $key);
				$id = str_replace("field","", $keydata[0]);
				if($keydata[1]=='field'){
					$id_string = strtolower($value);
					$id_string = str_replace(" ", "_", $id_string);
					$Fields["$id"]["id"] = $id_string;
				}
				$Fields["$id"][$keydata[1]] = $value;
			}
		}
		$Fields = array_values($Fields);
		$FieldsJson = json_encode($Fields);
		$FieldsJsonEsc = mysqli_real_escape_string($this->connection, $FieldsJson);
		
		$icon_vector = "";
		if(isset($post_data['icon_vector'])){
			$icon_vector = $this->getImageFromCache($post_data['icon_vector']);
		}
		$icon_vectorEsc = mysqli_real_escape_string($this->connection, $icon_vector);
		
		$prefixCategories = $this->ini_array['msql_prefix']."Categories";
		$sql_Insert_Category = "REPLACE INTO $prefixCategories (name , icon_vector, data)
		VALUES ('$Name', '$icon_vectorEsc', '$FieldsJsonEsc')";
		if (!mysqli_query($this->connection, $sql_Insert_Category)) {
			//$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			echo mysqli_connect_error();
		}
		
	}
	function updateCategory($post_data){
		$Name = $post_data['name'];
		$CategoryId = $post_data['id'];
		$Fields = array();
		foreach($post_data as $key => $value){
			if (strpos($key, 'field') !== false) {
				$keydata = explode("_", $key);
				$id = str_replace("field","", $keydata[0]);
				if($keydata[1]=='field'){
					$id_string = strtolower($value);
					$id_string = str_replace(" ", "_", $id_string);
					$Fields["$id"]["id"] = $id_string;
				}
				$Fields["$id"][$keydata[1]] = $value;
			}
		}
		$Fields = array_values($Fields);
		$FieldsJson = json_encode($Fields);
		$FieldsJsonEsc = mysqli_real_escape_string($this->connection, $FieldsJson);
		
		$icon_vector = "";
		if(isset($post_data['icon_vector'])){
			$icon_vector = $this->getImageFromCache($post_data['icon_vector']);
			$icon_vectorEsc = mysqli_real_escape_string($this->connection, $icon_vector);
			$icon_vectorSQL = ", icon_vector='".$icon_vectorEsc."'";
		}
		
		
		$prefixCategories = $this->ini_array['msql_prefix']."Categories";
		$sql_update_Category = "UPDATE $prefixCategories SET name='$Name', data='$FieldsJsonEsc'$icon_vectorSQL WHERE id='$CategoryId'";

		if (!mysqli_query($this->connection, $sql_update_Category)) {
			echo mysqli_connect_error();
		}
	}
	function removeCategory($id){
		$prefixCategories = $this->ini_array['msql_prefix']."Categories";
		$sql_remove_Category = "DELETE FROM $prefixCategories WHERE id='$id'";
		echo $sql_remove_Category;
		if (!mysqli_query($this->connection, $sql_remove_Category)) {
			echo mysqli_connect_error();
		}
	}
	function getCategory($id){
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sqlq_GetUser = "SELECT * from $prefixUsers WHERE (username = '$user') LIMIT 1";
		$sql_GetUser = mysqli_query($this->connection, $sqlq_GetUser);
		if(mysqli_num_rows($sql_GetUser) == 0){
			return null;
		}
		$UserData = mysqli_fetch_array($sql_GetUser, MYSQLI_ASSOC);
		return $UserData;
	}
	function getCategories(){
		$prefixCategories = $this->ini_array['msql_prefix']."Categories";
		$sqlq_GetCategories = "SELECT * from $prefixCategories ORDER BY name ASC";
		$sql_GetCategories = mysqli_query($this->connection, $sqlq_GetCategories);
		if(mysqli_num_rows($sql_GetCategories) == 0){
			return array();
		}
		while ($row_Category = mysqli_fetch_assoc($sql_GetCategories))
			$CategoriesData[] = $row_Category;
		return $CategoriesData;
	}
	
	function addItem($post_data){
		$Name = $post_data['name'];
		$Category = $post_data['category'];
		$data = $post_data;
		unset($data['name']);
		unset($data['category']);
		$FieldsJson = json_encode($data);
		$FieldsJsonEsc = mysqli_real_escape_string($this->connection, $FieldsJson);
		
		$prefixItems = $this->ini_array['msql_prefix']."Items";
		$sql_Insert_Item = "REPLACE INTO $prefixItems (name, category_id, data)
		VALUES ('$Name', $Category, '$FieldsJsonEsc')";
		
		if (!mysqli_query($this->connection, $sql_Insert_Item)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}
		
	}
	function updateItem($post_data){
		$Name = $post_data['name'];
		$ItemId = $post_data['id'];
		$Category = $post_data['category'];
		$data = $post_data;
		unset($data['name']);
		unset($data['category']);
		unset($data['id']);
		$FieldsJson = json_encode($data);
		$FieldsJsonEsc = mysqli_real_escape_string($this->connection, $FieldsJson);
		
		$prefixItems = $this->ini_array['msql_prefix']."Items";
		$sql_update_Item = "UPDATE $prefixItems SET name='$Name', category_id='$Category', data='$FieldsJsonEsc' WHERE id='$ItemId'";
		echo $sql_update_Item;
		if (!mysqli_query($this->connection, $sql_update_Item)) {
			echo mysqli_connect_error();
		}
	}
	function removeItem($id){
		$prefixItems = $this->ini_array['msql_prefix']."Items";
		$sql_remove_Item = "DELETE FROM $prefixItems WHERE id='$id'";
		echo $sql_remove_Item;
		if (!mysqli_query($this->connection, $sql_remove_Item)) {
			echo mysqli_connect_error();
		}
	}
	function getItem($user){
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sqlq_GetUser = "SELECT * from $prefixUsers WHERE (username = '$user') LIMIT 1";
		$sql_GetUser = mysqli_query($this->connection, $sqlq_GetUser);
		if(mysqli_num_rows($sql_GetUser) == 0){
			return null;
		}
		$UserData = mysqli_fetch_array($sql_GetUser, MYSQLI_ASSOC);
		return $UserData;
	}
	function getItems(){
		$prefixItems = $this->ini_array['msql_prefix']."Items";
		$sqlq_GetItems = "SELECT * from $prefixItems ORDER BY CAST(name AS UNSIGNED), name";
		$sql_GetItems = mysqli_query($this->connection, $sqlq_GetItems);
		if(mysqli_num_rows($sql_GetItems) == 0){
			return array();
		}
		while ($row_Item = mysqli_fetch_assoc($sql_GetItems))
			$ItemsData[] = $row_Item;
		return $ItemsData;
	}
	
	function randomString($length) {
		$str = "";
		$characters = array_merge(range('a','z'), range('0','9'));
		$max = count($characters) - 1;
		for ($i = 0; $i < $length; $i++) {
			$rand = mt_rand(0, $max);
			$str .= $characters[$rand];
		}
		return $str;
	}
	function getImageFromCache($fileData){
		$path = $fileData['tmp_name'];
		$File = fopen($path, "r") or die("Unable to open file!");
		$FileContent = file_get_contents($path);
		
		return $FileContent;
	}
}
?>