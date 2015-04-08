<?php

if(!defined('BASEPATH')) exit('Indirect Script Access not allowed');

class Dataman Extends Lobby

//////////////////////////////////////////////////////////
//
// This class serves as our application's data manager  //
//
// FUNCTIONS INCLUDE:
//
// - Create/Maintain database connection
// - Query database
// - Retrieve data from database
// - Update database
// - Format database data
// - Create/Manage data Cache
// - Retrieve data from data cache
//
//
//
//  CACHE DESIGN
//
//  Our cache will be a 2-dimensional array that will carry
//  information of each table row acquired from our database
//  GENERAL FORMAT:
//  cache['table_name']['unique_id']
//  
//  CACHE STRUCTURE
//  $cache_id = cache['user']['user_id]
//  cache['user']['user_id]
//  cache['user']['user_id]
//
////////////////////////////////////////////////////////

{

public $cache;
public $db_query_count;
private $db_connection;
public $db_connected = false;
public $affected_rows = 0;

public function __construct() 
{
$this->Connect_To_Db();
$this->Init_Cache(); //initialize data-cache
$this->db_query_count = 0;
}	


private function Connect_To_Db()
{
//connect to db
$dbhost = $this->config->db_host;
$dbuser = $this->config->db_user;
$dbpassword = $this->config->db_pswd;
$dbname = $this->config->db_name;
$this->db_connection = mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname) or die ("couldn’t connect to server");
$this->db_connected = true;
}

private function Get_Affected_Rows()
{
$this->affected_rows = mysqli_affected_rows($this->db_connection);
}

public function Query_Db($query)
{
$result = mysqli_query($this->db_connection,$query) or die(mysqli_error($this->db_connection));
$this->Get_Affected_Rows();
$this->db_query_count++;
if(!empty($result)){
return $result;}
}

public function Query_Db_In($query)
{
mysqli_query($this->db_connection,$query) or die(mysqli_error($this->db_connection).' THE QUERY IS: '.$query);
$this->Get_Affected_Rows();
$this->db_query_count++;
}

public function Escape_String_For_Db($string){
return mysqli_real_escape_string($this->db_connection,$string);
}

public function Get_Single_Db_Data($query)
{
$result = mysqli_query($this->db_connection,$query) or die(mysqli_error($this->db_connection).' THE QUERY IS: '.$query);
$this->db_query_count++;
$result2 = mysqli_fetch_array($result);
if(empty($result2)){return false;}
$value = $result2[key($result2)];
return $value;
}

public function Get_Single_Db_Data_Ch($query)
{
//this query can be cached
$result = mysql_query($this->db_connection,$query) or die($query);
$this->db_query_count++;
$result2 = mysqli_fetch_array($result);
if(empty($result2)){return false;}
$value = $result2[key($result2)];
return $value;
}

public function Get_Data_Rows_Ch($query,$cache_id=false){
//this query can be cached

//get data from cache
$cache_data = $this->Get_Cache_Data($cache_id);
if(!$cache_data){
//if cache data is not available, query database
$result = mysqli_query($this->db_connection,$query) or die(mysqli_error($this->db_connection));
$this->db_query_count++;
$n = 0;
$result3 = array();
while ($result2 = mysqli_fetch_array($result))
{
foreach($result2 as $key => $value)		
{
$result3[$n][$key] = $value;
}	
$n++;
}
//if cache_id is set then save data into cache
if($cache_id != false){
$this->Save_Into_Cache($cache_id,$result3);
}
//return data from database
return $result3;
}

//ELSE return cache data
return $cache_data;

}

public function Get_Data_Rows($query){
$result = mysqli_query($this->db_connection,$query) or die(mysqli_error($this->db_connection));
$this->db_query_count++;
$n = 0;
$result3 = array();
while ($result2 = mysqli_fetch_array($result))
{
		foreach($result2 as $key => $value)		
        {
        $result3[$n][$key] = $value;
        }	
        $n++;
}
//print_r($result3);
return $result3;
}

public function Compile_Single_Data_Rows($data)
{
$results = array();
$rows = count($data);
for($i=0; $i<$rows; $i++)
{
foreach($data[$i] as $key => $value){
$results[$i] = $value;}
} 
return $results;
}

public function Compile_Single_Row_Columns($data)
{
$values = array();
$data = $data[0];
foreach($data as $key => $final){
$values[$key] = $final;}
return $values;
}


//////////////////////
//
// CACHE MANAGEMENT
//
////////////////////

private function Init_Cache()
{
//initializes the cache

if(!isset($_SESSION['data-cache'])){
//if no data cache has been set then start an empty cache
$_SESSION['data-cache'] = "";
}
$this->cache = $_SESSION['data-cache'];
}

private function Update_Cache()
{
//updates the cache
$_SESSION['data-cache'] = $this->cache;
}

public function Remove_From_Cache($cache_id)
{
if(array_key_exists($cache_id,$this->cache)){
//remove from cache
unset($this->cache[$cache_id]);
$this->Update_Cache();
}

}

private function In_Cache($cache_id)
{
//returns true if data is in cache
if(isset($this->cache[$cache_id])){
return true;}
return false;
}

private function Save_Into_Cache($cache_id,$data)
{
$this->cache[$cache_id] = $data; //add into cache
$this->Update_Cache(); //push cache_data into session data
}

private function Get_Cache_Data($cache_id)
{
if(!$cache_id){
return false;} //return false if cache_id is unset
if(!$this->In_Cache($cache_id)){
return false;} //return false if data is not in cache
return $this->cache[$cache_id];//return cache data
}


}?>
