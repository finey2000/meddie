<?php
class Session_Manager extends Lobby
{
/**
This class is in charge of managing sessions
*/

public $life_time;
private $session_data = '';
private $cookie_lifetime = 604800;
private $session_expire = false;

public function __construct()
{

	/*
session_set_cookie_params($this->cookie_lifetime);
session_set_save_handler(array($this,'open'),
                         array($this,'close'),
						 array($this,'read'),
						 array($this,'write'),
						 array($this,'destroy'),
						 array($this,'gc')); 
session_register_shutdown();	
*/
session_start();

//get current session id using session_id()
//get session id saved in cookie
//if session id does not match cookie session id then assign
//cookie session id to current session
//if cookie session id is not set then assign current session id
//to cookie and save cookie lifetime for two weeks
}

public function open($save_path,$sess_name)
{
//get session's lifetime
$this->life_time = get_cfg_var('session.gc_maxlifetime');
if(!$this->dataman->db_connected) return false;
return true;
}

public function close()
{
$this->gc(ini_get('session.gc_maxlifetime'));
}

public function read($session_id)
{
//fetch session data
if($this->session_expire)
{
$query = "SELECT data FROM session WHERE name = '$session_id' AND expires > ".time();
}else{
     $query = "SELECT data FROM session WHERE name = '$session_id'";
	 }
$this->session_data = $this->dataman->Get_Single_Db_Data($query);
if(!$this->session_data) return '';
return $this->session_data;
}

public function write($session_id,$session_data)
{
$this->session_data = $session_data;
$session_data = $this->dataman->Escape_String_For_Db($session_data);
//set new session expire time
$new_exp = time()+$this->life_time;
//check if session_id exists
$query = "SELECT name FROM session WHERE name = '$session_id'";
//var_dump($this->dataman->Get_Single_Db_Data($query));
if($this->dataman->Get_Single_Db_Data($query))
{
$query2 = "UPDATE session SET data = '$session_data' WHERE name = '$session_id'";
}
else{
$query2 = "INSERT into session (name,expires,data) VALUES ('$session_id','$new_exp','$session_data')";
     }
$this->dataman->Query_db_In($query2);
if($this->dataman->affected_rows) return true;
//an error occurred
return false;
}

public function destroy($session_id)
{
$query = "DELETE FROM session WHERE name = '$session_id'";
$this->dataman->Query_Db_In($query);
if($this->dataman->affected_rows) return true;
//else return false
return false;
}

public function gc($sess_max_life_time)
{
if($this->session_expire)
{
//deletes old sessions
$query = "DELETE FROM session WHERE expires < ".time();
$this->dataman->Query_Db_In($query);
//return affected rows
return $this->dataman->affected_rows;
}
return 0;
}




}




