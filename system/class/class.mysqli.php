<?php
/**
 * Database Class for MySQLi
 * 
 * MySQLi Class to allow easy and clean access to common mysql commands
 * Based on MySQL Class v1.1 by frozila in 2011
 * 
 * @version 1.0.0
 * 
 * @author Wisnu Hafid <wisnuhafid@gmail.com>
 * @link wisnu-hafid.net
 * @copyright 2016 WebHade Creative
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 * 
 * 
 * Changelog
 * 
 * beta - 21 July 2012
 * - logging sql query syntax to var query_text to show if no error happen for debugging
 * - auto save from POST var
 * - get single field value
 * - get all or selected data from database to array
 * 
 * v1.0.0 - 10 Nov 2016
 * - mysqli version for all methods of frozila's MySQL Class
 * - fix mysqli_field_name deprecated function
 * 
 */

class Database {

var $server   = ""; // database server
var $user     = ""; // database login name
var $pass     = ""; // database login password
var $database = ""; // database name
var $pre      = ""; // table prefix

//internal info
var $error = "";
var $errno = 0;
var $query_text = "";
var $link_id = 0;
var $query_id = 0;
var $affected_rows = 0;

/**
 * constructor
 * @param string $server   database server
 * @param string $user     database username
 * @param string $pass     database password
 * @param string $database database name
 * @param string $pre      database prefix
 * 
 */
function __construct($server, $user, $pass, $database, $pre=''){
	$this->server=$server;
	$this->user=$user;
	$this->pass=$pass;
	$this->database=$database;
	$this->pre=$pre;
}#-#__constructor()

/**
 * connect and select database using vars above
 * @return null
 */
function connect() {
	$this->link_id = mysqli_connect($this->server, $this->user, $this->pass, $this->database);

	if (mysqli_connect_errno($this->link_id)) {
		$this->oops("Could not connect to server: <b>$this->server</b>.");
	}

	// unset the data so it can't be dumped
	$this->server='';
	$this->user='';
	$this->pass='';
	$this->database='';
}#-#connect()


#-#############################################
# desc: close the connection
function close() {
	if(!mysqli_close($this->link_id)){
		$this->oops("Connection close failed.");
	}
}#-#close()


/**
 * escapes characters to be mysql ready
 * @param  string $string unescaped string
 * @return string         escaped string
 */
function escape($string) {
	$string = trim($string);
	$string = stripslashes($string);
	return mysqli_real_escape_string($this->link_id, $string);
}#-#escape()


#-#############################################
# Desc: executes SQL query to an open connection
# Param: (MySQL query) to execute
# returns: (query_id) for fetching results etc
function query($sql) {
	$this->query_text = $sql;
	// do query
	$this->query_id = mysqli_query($this->link_id, $sql);
	
	if (!$this->query_id) {
		$this->oops("<b>MySQL Query fail:</b> $sql");
		return 0;
	}
	
	$this->affected_rows = mysqli_affected_rows($this->link_id);

	return $this->query_id;
}#-#query()

function num_rows($query_id=-1){
	// retrieve row
	if (is_object($query_id))
	{
		$classname = get_class($query_id);
		
		if ($classname=='mysqli_result')
		{
			$this->query_id = $query_id;
			$rows = mysqli_num_rows($this->query_id);
			return $rows;
		} else {
			$this->oops("Invalid query_id: <b>$this->query_id</b>. Records could not be fetched.");
		}
	}
}

#-#############################################
# desc: fetches and returns results one line at a time
# param: query_id for mysql run. if none specified, last used
# return: (array) fetched record(s)
function fetch_array($query_id=-1) {
	// retrieve row
	$classname = get_class($query_id);

	if ($classname=='mysqli_result') {
		$this->query_id = $query_id;
	}

	if (isset($this->query_id)) {
		$record = mysqli_fetch_assoc($this->query_id);
	}else{
		$this->oops("Invalid query_id: <b>$this->query_id</b>. Records could not be fetched.");
	}
	
	return $record;
}#-#fetch_array()

#-#############################################
# desc: fetches and returns results one line at a time
# param: query_id for mysql run. if none specified, last used
# return: (object) fetched record(s)
function fetch_object($query_id=-1) {
	// retrieve row
	$classname = get_class($query_id);

	if ($classname=='mysqli_result') {
		$this->query_id = $query_id;
	}

	if (isset($this->query_id)) {
		$record = mysqli_fetch_object($this->query_id);
	}else{
		$this->oops("Invalid query_id: <b>$this->query_id</b>. Records could not be fetched.");
	}
	
	return $record;
}#-#fetch_row()



#-#############################################
# desc: fetches and returns results one line at a time
# param: query_id for mysql run. if none specified, last used
# return: (array) fetched record(s)
function fetch_row($query_id=-1) {
	// retrieve row
	$classname = get_class($query_id);

	if ($classname=='mysqli_result') {
		$this->query_id = $query_id;
	}

	if (isset($this->query_id)) {
		$record = mysqli_fetch_row($this->query_id);
	}else{
		$this->oops("Invalid query_id: <b>$this->query_id</b>. Records could not be fetched.");
	}
	
	return $record;
}#-#fetch_row()


#-#############################################
# desc: returns all the results (not one row)
# param: (MySQL query) the query to run on server
# returns: assoc array of ALL fetched results
function fetch_all_array($sql) {
	$query_id = $this->query($sql);
	$out = array();

	while ($row = $this->fetch_array($query_id)){
		$out[] = $row;
	}

	$this->free_result($query_id);
	return $out;
}#-#fetch_all_array()


#-#############################################
# desc: frees the resultset
# param: query_id for mysql run. if none specified, last used
function free_result($query_id=-1) {
	$classname = get_class($query_id);

	if ($classname=='mysqli_result') {
		$this->query_id = $query_id;
	}
	
	if($this->query_id!=0 && !mysqli_free_result($this->query_id)) {
		$this->oops("Result ID: <b>$this->query_id</b> could not be freed.");
	}
}#-#free_result()


#-#############################################
# desc: does a query, fetches the first row only, frees resultset
# param: (MySQL query) the query to run on server
# returns: array of fetched results
function query_first($query_string) {
	$query_id = $this->query($query_string);
	$out = $this->fetch_array($query_id);
	$this->free_result($query_id);
	return $out;
}#-#query_first()


#-#############################################
# desc: does an update query with an array
# param: table (no prefix), assoc array with data (doesn't need escaped), where condition
# returns: (query_id) for fetching results etc
function query_update($table, $data, $where='1') {
	$q="UPDATE `".$this->pre.$table."` SET ";

	foreach($data as $key=>$val) {
		if($key!="action" and $key!="table" and $key!="where" and $key!="password1" and $key!="password2" and $key!="blacklist" and $key!="blacklist1" and $key!="blacklist2" and $key!="var1" and $key!="var2" and $key!="spam"){
			// if($key=="password"){ $val = md5($val); }
			if($val===''){ $q .= "`$key` = '', "; }
            if(strtolower($val)=='null'){ $q.= "`$key` = NULL, "; }
			elseif(strtolower($val)=='now()') $q.= "`$key` = NOW(), ";
   	     	elseif(preg_match("/^increment\((\-?\d+)\)$/i",$val,$m)) $q.= "`$key` = `$key` + $m[1], "; 
			else $q.= "`$key`='".$this->escape($val)."', ";
		}
	}

	$q = rtrim($q, ', ') . ' WHERE '.$where.';';

	return $this->query($q);
}#-#query_update()


#-#############################################
# desc: does an insert query with an array
# param: table (no prefix), assoc array with data
# returns: id of inserted record, false if error
function query_insert($table, $data) {
	$q="INSERT INTO `".$this->pre.$table."` ";
	$v=''; $n='';

	foreach($data as $key=>$val) {
		if($key!="action" and $key!="table" and $key!="where" and $key!="password1" and $key!="password2" and $key!="blacklist" and $key!="blacklist1" and $key!="blacklist2" and $key!="var1" and $key!="var2" and $key!="spam"){
			$n .= $key.',';
			// if($key=="password"){ $val = md5($val); }
			if(strtolower($val)=='null'){ $v .= 'NULL,'; }
			else if(strtolower($val)=='now()'){ $v .= 'NOW(),'; }
			else { $v .= "'".$this->escape($val)."', "; }
		}
	}

	$q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";

	if($this->query($q)){
		//$this->free_result();
		return mysqli_insert_id($this->link_id);
	}
	else return false;
}#-#query_insert()

function insert_id(){
	return mysqli_insert_id();
}


#-#############################################
# desc: does an delete query with an array
# param: table (no prefix), assoc array with data
function query_delete($table, $data) {
	$q="DELETE FROM `".$this->pre.$table."` ";
	$where = " where ";
	foreach($data as $key=>$val) {
		if($key!="action" and $key!="table"){
			$where .= $key.'="'.$val.'" and ';
		}
	}

	$q .= rtrim($where, ' and ');
	
	return $this->query($q);
}#-#query_delete()


#-#############################################
# desc: throw an error message
# param: [optional] any custom error to display
function oops($msg='') {
	if(is_object($this->link_id)){
		$this->error=mysqli_error($this->link_id);
		$this->errno=mysqli_errno($this->link_id);
	}
	else{
		$this->error=mysqli_error();
		$this->errno=mysqli_errno();
	}
	?>
		<table align="center" border="1" cellspacing="0" style="background:white;color:black;width:80%;">
		<tr><th colspan=2>Database Error</th></tr>
		<tr><td align="right" valign="top">Message:</td><td><?php echo $msg; ?></td></tr>
		<?php if(!empty($this->error)) echo '<tr><td align="right" valign="top" nowrap>MySQL Error:</td><td>'.$this->error.'</td></tr>'; ?>
		<tr><td align="right">Date:</td><td><?php echo date("l, F j, Y \a\\t g:i:s A"); ?></td></tr>
		<?php if(!empty($_SERVER['REQUEST_URI'])) echo '<tr><td align="right">Script:</td><td><a href="'.$_SERVER['REQUEST_URI'].'">'.$_SERVER['REQUEST_URI'].'</a></td></tr>'; ?>
		<?php if(!empty($_SERVER['HTTP_REFERER'])) echo '<tr><td align="right">Referer:</td><td><a href="'.$_SERVER['HTTP_REFERER'].'">'.$_SERVER['HTTP_REFERER'].'</a></td></tr>'; ?>
		</table>
	<?php
}#-#oops()

#-#############################################
# Upgraded Functionality by Wisnu hafid start here


#-#############################################
# desc: auto insert with POST method 
function auto_save() {
	$output = "";
	$table = $_POST["table"];
	if($_POST["action"]=="insert"){
		$output = $this->query_insert($table, $_POST);
	}else if($_POST["action"]=="update"){
		$output = $this->query_update($table, $_POST, $_POST["where"]);
	}else if($_POST["action"]=="delete"){
		$output = $this->query_delete($table, $_POST);
	}
	return $output;
}#-#auto_save()

#-#############################################
# desc: get single fields value by its name, for example get a category name from id
# param: table name (no prefix), field name, where field name, where value
function get_fields($table, $name, $where, $value){
	$sql = 'SELECT '.$name.' AS value FROM '.$this->pre.$table.' WHERE '.$where.'="'.$value.'"';
	$result = $this->query($sql);
	$data = $this->fetch_array($result);
	$output = $data["value"];
	return $output;
}#-#get_fields()

#-#############################################
# desc: get all or selected data from database to array
# param: table name (no prefix), string fieldname separated by comma or asterik (*) for all field select, 
# string where syntax or array with keys as fieldname,
# string field and order by mode
# string limit start and nuber of row

function mysqli_field_name($result, $field_offset)
{
    $properties = mysqli_fetch_field_direct($result, $field_offset);
    return is_object($properties) ? $properties->name : null;
}

function get_data_from_sql($sql){
	$ddata = NULL;
	$result = $this->query($sql);
	$num = $this->num_rows($result);

	if ($num>0){
		$ddata = array();
		$lf = mysqli_num_fields($result);
		$w = 0;
		while ($odata = $this->fetch_array($result)){
			for($i=0;$i<$lf;$i++){
				$ddata[$w][$this->mysqli_field_name($result,$i)] = $odata[$this->mysqli_field_name($result,$i)];
			}
			$w++;
		}
	}
	return $ddata;
}#-#get_data_from_sql()

function get_data($table, $select = "", $where = "", $orderby = "", $limit = ""){
	if ($select==""){
		$select = "*";
	}
	$sql = "SELECT ".$select." FROM ".$this->pre.$table;
	if (is_array($where)){
		$wheres = array();
		foreach($where as $key=>$val) {
			$wheres[] = $key.'="'.$val.'"';
		}
		$wh = implode(' AND ',$wheres);
		$sql .= " WHERE ".$wh;
	} else if ($where!=""){
		$sql .= " WHERE ".$where;
	}
	if ($orderby!=""){
		$sql .= " ORDER BY ".$orderby;
	} else if ($orderby==""){
		//$sql .= " ORDER BY id DESC";
	}
	if ($limit!=""){
		$sql .= " LIMIT ".$limit;
	}
	
	return $this->get_data_from_sql($sql);

}#-#get_data()



}//CLASS Database
###################################################################################################
