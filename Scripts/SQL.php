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
}
?>