<?php

/**
 * general Database Connection
**/
require("data.php");
require("xmldata.php");

class Database extends Data {

 private $connectionData;
 private $db;
 
 private $userTable;

 /**
  * Builds a new database connection and offers basic methods.
  */
 function __construct() {
  // build mysql connection with login-data from the xmlfile
  $this->readConfig();
  $link = mysql_connect($this->connectionData["server"],
		 $this->connectionData["user"], $this->connectionData["password"]);
  if(!$link) new Error("Unable to connect to database.");

  $this->db = mysql_select_db($this->connectionData["dbname"], $link);
  if(!$this->db) new Error("Unable to find database.");
  }

 // reads the database config from config/database.xml
 private function readConfig() {
  # Different locations for login and system
  $cfgfile = "config/database.xml";
  if(file_exists($cfgfile)) $config = new XmlData($cfgfile, "Database");
   else $config = new XmlData("../../" . $cfgfile, "Database");
  $this->connectionData = array(
   "server" => $config->getParameter("Server") . ":" . $config->getParameter("Port"),
   "user" => $config->getParameter("User"),
   "password" => $config->getParameter("Password"),
   "dbname" => $config->getParameter("Name")
   );
  $this->userTable = $config->getParameter("UserTable");
  }

 /**
  * Returns the value of a single cell.
  * @param unknown_type $table Table of the cell.
  * @param unknown_type $col Column of the cell.
  * @param unknown_type $where Where clause without the "WHERE".
  */
 public function getCell($table, $col, $where) {
  $query = "SELECT $col FROM $table WHERE $where";
  $res = $this->exe($query);
  $row = mysql_fetch_assoc($res);
  return $row[$col];
  }

 /**
  * Returns an array with the data from the query.
  * @param String $query SQL query.
  */
 public function getSelection($query) {
  # Execute Query
  $res = $this->exe($query);

  $dataTable = array();
  # add header
  $header = array();
  $i = 0;
  while ($i < mysql_num_fields($res)) {
   $meta = mysql_fetch_field($res, $i);
   if (!$meta) new Error("Invalid table header.");

   array_push($header, ucfirst($meta->name));
   $i++;
   }
  array_push($dataTable, $header);

  # add Data
  while($row = mysql_fetch_array($res)) {
   array_push($dataTable, $row);
   }

  return $dataTable;
  }
  
 /**
  * Returns an array of the form $id => name with the possible foreign keys
  * @param string $table The referenced table
  * @param string $idcolumn The referenced id column
  * @param string $namecolumn The name for the reference
  */
 public function getForeign($table, $idcolumn, $namecolumn) {
 	$query = "SELECT $idcolumn, $namecolumn FROM $table ORDER BY $namecolumn";
 	$res = $this->exe($query);
 	$ret = array();
 	
 	while($row = mysql_fetch_array($res)) {
 		$ret[$row[$idcolumn]] = $row[$namecolumn];
 	}
 	
 	return $ret;
 }

 /**
  * Returns just one row as an array.
  * @param String query SQL query.
  */
 public function getRow($query) {
  $res = $this->exe($query);
  return mysql_fetch_assoc($res);
  }

 /**
  * Executes the given String as an SQL statement.
  * @param String $query Database SQL query to be executed.
  * @return The ID if the query has been an insert statement
  * 	with an autoincrement generator. See PHP manual for details.
  */
 public function execute($query) {
  $res = mysql_query($query);
  if(!$res) new Error("The database query has failed:<br />" . mysql_error() . ".");
  return mysql_insert_id();
 }

 // internal execution
 private function exe($query) {
  $res = mysql_query($query);
  if(!$res) {
   require_once($GLOBALS['DIR_WIDGETS'] . "error.php");
   new Error("The database query has failed:<br />" . mysql_error() . ".<br>Debug:" . $query);
   }
   else return $res;
 }

 /**
  * Returns the name of the user table.
  */
 public function getUserTable() {
 	return $this->userTable;
 }
 
 /**
  * Returns the name of the database.
  */
 public function getDatabaseName() {
 	return $this->connectionData["dbname"];
 }
 
 /**
  * Returns the name of the fields in the given table.
  * @param String $table Name of the table. 
  */
 public function getFieldsOfTable($table) {
 	$res = $this->exe("SHOW COLUMNS FROM $table");
 	
 	$fields = array();
 	if(mysql_num_rows($res) > 0) {
 		while($row = mysql_fetch_assoc($res)) {
 			array_push($fields, $row["Field"]);
 		}
 	}
 	else {
 		new Error("Empty $table table. Please check your database " . $this->getDatabaseName() . "!");
 	}
 	return $fields;
 }
}

?>