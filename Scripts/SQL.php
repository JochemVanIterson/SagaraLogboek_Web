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
	function CheckLogin($cookie){
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sqlq_GetUser = "SELECT * from $prefixUsers WHERE (username = '$cookie[username]' AND iv = '$cookie[iv]') LIMIT 1";
		$sql_GetUser = mysqli_query($this->connection, $sqlq_GetUser);
		if(mysqli_num_rows($sql_GetUser) == 0){
			return array("login_error"=>"iv doesnt match");
		}
		return array("login"=>"success", "data"=>mysqli_fetch_array($sql_GetUser, MYSQLI_ASSOC));
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
	function getUsers($web = true){
		$prefixUsers = $this->ini_array['msql_prefix']."Users";
		$sqlq_GetUsers = "SELECT * from $prefixUsers ORDER BY firstname ASC";
		$sql_GetUsers = mysqli_query($this->connection, $sqlq_GetUsers);
		if(mysqli_num_rows($sql_GetUsers) == 0){
			return null;
		}
		while ($row_user = mysqli_fetch_assoc($sql_GetUsers)){
			if(!$web){
				unset($row_user['password']);
				unset($row_user['iv']);
			}
			$UserData[] = $row_user;
		}
		return $UserData;
	}
	
	function addCategory($post_data){
		$Name = $post_data['name'];
		
		// Create Field array
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
		
		
		$icon_vector = "NULL";
		if(isset($post_data['icon_vector']) && $post_data['icon_vector']!=""){
			$icon_vector = $this->getImageFromCache($post_data['icon_vector']);
			$icon_vectorEsc = "'".mysqli_real_escape_string($this->connection, $icon_vector)."'";
		} else {
			$icon_vectorEsc = $icon_vector;
		}
		
		
		$prefixCategories = $this->ini_array['msql_prefix']."Categories";
		$sql_Insert_Category = "REPLACE INTO $prefixCategories (name , icon_vector, data)
		VALUES ('$Name', $icon_vectorEsc, '$FieldsJsonEsc')";
		//echo $sql_Insert_Category;
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
		while ($row_Category = mysqli_fetch_assoc($sql_GetCategories)){
			$image = $row_Category['icon_vector'];
			if($image!=null){
				$row_Category['icon_vector'] = base64_encode($row_Category['icon_vector']);
			} else {
				unset($row_Category['icon_vector']);
			}
			$CategoriesData[] = $row_Category;
		}
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
	function getItem($post_data){
		$item_id = $post_data['id'];
		$prefixItems = $this->ini_array['msql_prefix']."Items";
		$sqlq_GetItem = "SELECT * from $prefixItems WHERE (id = '$item_id') LIMIT 1";
		$sql_GetItem = mysqli_query($this->connection, $sqlq_GetItem);
		if(mysqli_num_rows($sql_GetItem) == 0){
			return null;
		}
		$ItemData = mysqli_fetch_array($sql_GetItem, MYSQLI_ASSOC);
		return $ItemData;
	}
	function getItemEntries($post_data){
		$itemData = $this->getItem($post_data);
		$entryData = $this->getEntriesByItem($post_data);
		$returnData = array();
		$returnData['item'] = $itemData;
		$returnData['entry'] = $entryData;
		return $returnData;
	}
	function getItems(){
		$prefixItems = $this->ini_array['msql_prefix']."Items";
		$sqlq_GetItems = "SELECT * from $prefixItems ORDER BY CAST(name AS UNSIGNED), name";
		$sql_GetItems = mysqli_query($this->connection, $sqlq_GetItems);
		if(mysqli_num_rows($sql_GetItems) == 0){
			return array();
		}
		while ($row_Item = mysqli_fetch_assoc($sql_GetItems)){
			$image = $row_Item['icon_vector'];
			if($image!=null){
				$row_Item['icon_vector'] = base64_encode($row_Item['icon_vector']);
			} else {
				unset($row_Item['icon_vector']);
			}
			$ItemsData[] = $row_Item;
		}
		return $ItemsData;
	}
	
	function addEntry($post_data){
		$item_id = $post_data["item_id"];
		$user_name = $post_data["username"];
		$user = $this->getUser($user_name);
		$user_id = $user["id"];
		$device_id = $post_data["device_id"];
		$type = $post_data["type"];
		
		$DataArrayEsc = "";
		if(isset($post_data["dataUpdate"])){
			$newDataStr = $post_data["dataUpdate"];
			$newData = json_decode($newDataStr);
			$DataArray = array($newData);
			$DataArrayStr = json_encode($DataArray, true);
			$DataArrayEsc = mysqli_real_escape_string($this->connection, $DataArrayStr);
		}
		
		$StartArrayEsc = "";
		if(isset($post_data["data_start"])){
			$newStartStr = $post_data["data_start"];
			$newStart = json_decode($newStartStr);
			//$StartArray = array($newStart);
			$StartArrayStr = json_encode($newStart, true);
			$StartArrayEsc = mysqli_real_escape_string($this->connection, $StartArrayStr);
		}
		
		$prefixEntries = $this->ini_array['msql_prefix']."Entries";
		$prefixItems = $this->ini_array['msql_prefix']."Items";
		$sql_Insert_Entry = "INSERT INTO $prefixEntries (item_id, user_id, device_id, type, location_data, data_start, datetime_start, last_update)
		VALUES ($item_id, $user_id, '$device_id', '$type', '$DataArrayEsc', '$StartArrayEsc', NOW(), NOW())";
		if (!mysqli_query($this->connection, $sql_Insert_Entry)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}
		$inserted_id = mysqli_insert_id($this->connection);
		
		$prefixItems = $this->ini_array['msql_prefix']."Items";
		$sql_update_Item = "UPDATE $prefixItems SET sailing_user=$user_id WHERE id='$item_id'";
		if (!mysqli_query($this->connection, $sql_update_Item)) {
			echo mysqli_connect_error();
		}
		
		return $inserted_id;
	}
	function addSingleEntry($post_data){
		$item_id = $post_data["item_id"];
		$user_name = $post_data["username"];
		$user = $this->getUser($user_name);
		$user_id = $user["id"];
		$device_id = $post_data["device_id"];
		$type = $post_data["type"];
		
		$StartArrayEsc = "";
		if(isset($post_data["data_start"])){
			$newStartStr = $post_data["data_start"];
			$newStart = json_decode($newDataStr);
			$StartArray = array($newData);
			$StartArrayStr = json_encode($DataArray, true);
			$StartArrayEsc = mysqli_real_escape_string($this->connection, $DataArrayStr);
		}
		
		$prefixEntries = $this->ini_array['msql_prefix']."Entries";
		$prefixItems = $this->ini_array['msql_prefix']."Items";
		$sql_Insert_Entry = "INSERT INTO $prefixEntries (item_id, user_id, device_id, type, data_start, datetime_start, last_update, datetime_stop)
		VALUES ($item_id, $user_id, '$device_id', '$type', '$StartArrayEsc', NOW(), NOW(), NOW())";
		if (!mysqli_query($this->connection, $sql_Insert_Entry)) {
			$install_error['msql_error'] = "SQL error: ".mysqli_connect_error();
			return $install_error;
		}
	}
	function updateEntry($post_data){
		$entry_id = $post_data["entry_id"];
		$device_id = $post_data["device_id"];
		$newDataStr = $post_data["dataUpdate"];
		$newData = json_decode($newDataStr);
		//$newData['time'] = date("Y-m-d H:i:s");
		$existingEntry = $this->getEntry($entry_id);
		if($existingEntry['device_id']!=$device_id){
			return array("error"=>"device_id");
		}
		$oldData = $existingEntry['location_data'];
		if($oldData == ""){
			$oldData = "[]";
		}
		$DataArray = json_decode($oldData);
		array_push($DataArray, $newData);
		$DataArrayStr = json_encode($DataArray, true);
		
		$DataArrayEsc = mysqli_real_escape_string($this->connection, $DataArrayStr);
		$prefixEntries = $this->ini_array['msql_prefix']."Entries";
		
		$sql_update_Entry = "UPDATE $prefixEntries SET location_data='$DataArrayEsc' WHERE id='$entry_id'";
		if (!mysqli_query($this->connection, $sql_update_Entry)) {
			echo mysqli_connect_error();
		}
	}
	function updateMultiEntry($post_data){
		$entry_id = $post_data["entry_id"];
		$device_id = $post_data["device_id"];
		$newDataStr = $post_data["dataUpdate"];
		$newData = json_decode($newDataStr, true);
		$existingEntry = $this->getEntry($entry_id);
		if($existingEntry['device_id']!=$device_id){
			return array("error"=>"device_id");
		}
		$oldData = $existingEntry['location_data'];
		if($oldData == ""){
			$oldData = "[]";
		}
		$DataArray = json_decode($oldData);
		for($i=0; $i<count($newData);$i++){
			array_push($DataArray, $newData[$i]);
		}
		//array_push($DataArray, $newData);
		$DataArrayStr = json_encode($DataArray, true);
		
		$DataArrayEsc = mysqli_real_escape_string($this->connection, $DataArrayStr);
		$prefixEntries = $this->ini_array['msql_prefix']."Entries";
		
		$sql_update_Entry = "UPDATE $prefixEntries SET location_data='$DataArrayEsc' WHERE id='$entry_id'";
		if (!mysqli_query($this->connection, $sql_update_Entry)) {
			echo mysqli_connect_error();
		}
	}
	function finishEntry($post_data){
		$item_id = $post_data["item_id"];
		$entry_id = $post_data["entry_id"];
		$device_id = $post_data["device_id"];
		
		$existingEntry = $this->getEntry($entry_id);
		if($existingEntry['device_id']!=$device_id){
			return array("error"=>"device_id");
		}
		
		$prefixItems = $this->ini_array['msql_prefix']."Items";
		$sql_update_Item = "UPDATE $prefixItems SET sailing_user=-1 WHERE id='$item_id'";
		if (!mysqli_query($this->connection, $sql_update_Item)) {
			return mysqli_connect_error();
		}

		$prefixEntries = $this->ini_array['msql_prefix']."Entries";
		$sql_finish_Entry = "UPDATE $prefixEntries SET datetime_stop=NOW() WHERE id='$entry_id'";
		if (!mysqli_query($this->connection, $sql_finish_Entry)) {
			return mysqli_connect_error();
		}
	}
	function getEntry($id){
		$prefixEntries = $this->ini_array['msql_prefix']."Entries";
		$sqlq_GetEntry = "SELECT * from $prefixEntries WHERE (id = $id) LIMIT 1";
		$sql_GetEntry = mysqli_query($this->connection, $sqlq_GetEntry);
		if(mysqli_num_rows($sql_GetEntry) == 0){
			return null;
		}
		$EntryData = mysqli_fetch_array($sql_GetEntry, MYSQLI_ASSOC);
		return $EntryData;
	}
	function getEntriesByItem($post_data){
		$item_id = $post_data['id'];
		$prefixEntries = $this->ini_array['msql_prefix']."Entries";
		$sqlq_GetEntry = "SELECT * from $prefixEntries WHERE (item_id = $item_id) ORDER BY id DESC";
		$sql_GetEntry = mysqli_query($this->connection, $sqlq_GetEntry);
		if(mysqli_num_rows($sql_GetEntry) == 0){
			return null;
		}
		while($row_Item = mysqli_fetch_assoc($sql_GetEntry)){
			$row_Item['location_data'] = json_decode($row_Item['location_data'], true);
			$EntryData[] = $row_Item;
		}
		return $EntryData;
	}
	function getLastEntry($post_data){
		$item_id = $post_data['id'];
		$prefixEntries = $this->ini_array['msql_prefix']."Entries";
		$sqlq_GetEntry = "SELECT * from $prefixEntries WHERE (item_id = $item_id) ORDER BY id DESC LIMIT 1";
		$sql_GetEntry = mysqli_query($this->connection, $sqlq_GetEntry);
		if(mysqli_num_rows($sql_GetEntry) == 0){
			return null;
		}
		$EntryData = mysqli_fetch_array($sql_GetEntry, MYSQLI_ASSOC);
		return $EntryData;
	}
	
	function getLastLocations($post_data){
		$prefixEntries = $this->ini_array['msql_prefix']."Entries";
		$sqlq_GetEntries = "SELECT * from $prefixEntries ORDER BY id DESC";
		$sql_GetEntries = mysqli_query($this->connection, $sqlq_GetEntries);
		if(mysqli_num_rows($sql_GetEntries) == 0){
			return null;
		}
		$EntryData = [];
		while($row_Item = mysqli_fetch_assoc($sql_GetEntries)){
			if(!array_key_exists($row_Item['item_id'], $EntryData)){
				$row_Item['location_data'] = @end(json_decode($row_Item['location_data'], true));
				unset($row_Item['device_id']);
				$EntryData[$row_Item['item_id']] = $row_Item;
			}
		}
		return array_values($EntryData);
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
		if(!isset($fileData['tmp_name'])){
			return "";
		}
		$path = $fileData['tmp_name'];
		$File = fopen($path, "r") or die("Unable to open file!");
		$FileContent = file_get_contents($path);
		
		return $FileContent;
	}
}
?>