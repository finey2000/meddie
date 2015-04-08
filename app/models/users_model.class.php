<?php 
if(!defined('BASEPATH')) exit('Indirect Script Access not allowed');
/**
 * users model class
 * 
 * 
 * @author Christian Ntong
 *
 */
class Users_Model extends Lobby {
	
	protected $users;	
	private $user_cookie_name = 'user_auth';
	private $db_user_table = 'user';
	private $db_user_data_tbl = 'quiz_data';
	public $user = false; //this is the logged in user object
	public function __construct()
	{
		parent::__construct();
		$this->load->include_model('user_model');
		$this->load_user();

	}
	
	/**
	 * returns a user object if $username and $password is correct
	 * @param string $username
	 * @param string $password
	 * @return object|boolean
	 */
	public function get_user($username,$password)
	{
		$username = $this->dataman->Escape_String_For_Db($username);
		$password = $this->dataman->Escape_String_For_Db($password);
		$user = $this->get_user_by_condition(array('username'=>$username));	
		if(!$user) return false; //return false if user is not found.	
		if($this->utility->password_matches($password,$user->get_password())) return $user;
		return false;
	}
	
	/**
	 * sets user data into the database and returns new user object
	 * @param integer $user_id
	 * @param array $data
	 */
	public function update_user_data($user_id,$data){
		
		$data_query = '';
		foreach($data as $field => $value){
			if(!empty($data_query)) $data_query .= ", ";
			$data_query .= "$field = '$value'";
			}
      $full_query = "UPDATE $this->db_user_table SET $data_query WHERE user_id = $user_id";		
      //query database
      $this->dataman->Query_Db_In($full_query);
		return $this->get_user_by_id($user_id);
	}
	
	/**
	 * saves user data in a data table
	 * @return boolean
	 */
	public function save_user_data(User_Model $user,$data)
	{
		$user_id = $user->get_id();
		//escape data
		$data = $this->dataman->Escape_String_For_Db($data);
		$query1 = "SELECT user_id FROM $this->db_user_data_tbl WHERE user_id = $user_id;";
		$query2 = "INSERT INTO $this->db_user_data_tbl (user_id,user_data) VALUES ('$user_id','$data');";
		$query3 = "UPDATE $this->db_user_data_tbl SET user_data = '$data' WHERE user_id = $user_id;";
		$this->dataman->Query_Db_In($query1);
		if($this->dataman->affected_rows) $this->dataman->Query_Db_In($query3);
		else $this->dataman->Query_Db_In($query2);
		return true;
	}
	
	public function get_user_data(User_Model $user)
	{
		$user_id = $user->get_id();
		$query1 = "SELECT user_data FROM $this->db_user_data_tbl WHERE user_id = $user_id;";
		return $this->dataman->Get_Single_Db_Data($query1);
	}	
	
	public function get_user_by_id($id){
    return $this->get_user_by_condition(array('user_id'=>$id));
	}
	
	public function get_user_by_identifier_token($identifier,$token){
		return $this->get_user_by_condition(array('identifier'=>$identifier,'token'=>$token));
		
	}
	
	
	/**
	 * checks to see if username is available
	 * @param string $email
	 * @return boolean
	 */
	public function we_have_user($username){
		$username = $this->dataman->Escape_String_For_Db($username);
		$query = "SELECT user_id FROM $this->db_user_table WHERE username = '$username'";
		$this->dataman->Query_Db_In($query);
		if($this->dataman->affected_rows <= 0) return false;
		return true;
	}
	

	
	public function create_user($username,$password,$email){		
		$password = $this->utility->Generate_Hash($this->dataman->Escape_String_For_Db($password));
		$query = "INSERT INTO $this->db_user_table (username,email,password)VALUES('$username','$email','$password')";
		$this->dataman->Query_Db_In($query);		
	}
	
	
	
	/**
	 * gets a user object by specified condition
	 * @param array $condition
	 */
	protected function get_user_by_condition(Array $condition){
		$condition_query = '';
		foreach($condition as $field => $value){
			if(!empty($condition_query)) $condition_query .= " AND ";
			$condition_query .= "$field = '$value'";
		}
		$query =  "SELECT * FROM $this->db_user_table WHERE $condition_query;";
		$result = $this->dataman->Get_Data_Rows($query);
		if(count($result) <= 0) return false;
		$user = new ArrayObject($result[0],ArrayObject::ARRAY_AS_PROPS);
		return new User_Model($user);		
	}
	
	private function load_db_users(){
		
		$this->users = $this->dataman->Get_Data_Rows("SELECT * FROM $this->db_user_table;");
	}
	
	/**
	 * loads user data from browser cookie
	 * @return void|boolean
	 */
	public function load_user(){					
		if(!isset($_COOKIE[$this->user_cookie_name])) return false;		
		else $cookie = $_COOKIE[$this->user_cookie_name];
		list($identifier,$token) = explode(':',$cookie);		
		$this->user = $this->get_user_by_identifier_token($identifier,$token);
		return true;			
	}
	
	/**
	 * logs out a user and deletes the user's login cookie
	 * @return boolean
	 */
	public function logout_user(){
		if(!$this->user) return true;
		$identifier = $this->user->get_identifier();
		$token = $this->user->get_login_token();
		$timeout = time();//now
		setcookie($this->user_cookie_name,"$identifier:$token",$timeout,'/');
		$this->user = false;
		return true;
	}
	
	/**
	 * Logs out and deletes the user's account
	 * @return boolean
	 */
	
	public function delete_user()
	{
		if( ! $this->user) return false;
		$user_id = $this->user->get_id();
		$this->user->save_all_data = false;
		$this->logout_user();
		$query1 = "DELETE FROM $this->db_user_table WHERE user_id = $user_id";
		$query2 = "DELETE FROM $this->db_user_data_tbl WHERE user_id = $user_id"; //query to delete any available user data
		$this->dataman->Query_Db_In($query2);
		$this->dataman->Query_Db_In($query1);
		return true;
	}
	
	public function change_user_password($new_password)
	{
		if( ! $this->user) return false;
		$new_password = $this->utility->Generate_Hash($new_password);
		$query = "UPDATE $this->db_user_table SET password = '$new_password' WHERE user_id = ".$this->user->get_id();
		$this->dataman->Query_Db_In($query);
		$this->user->change_password($this,$new_password);
		return true;
	}
	
	/**
	 * sets a user cookie for persistent login
	 * @param User_Model $user user object
	 */
	public function set_user(User_Model $user,$logged_in = false){
		//if login timeout is not available, generate new identifier, token, timeout and set cookie
		if($logged_in){
				
			$timeout = strtotime('14 days',time());
			$salt = 'user';
			$identifier = md5($user->get_email().$salt);
			$token = md5(uniqid(rand(),true));
			$user = $this->update_user_data($user->get_id(),array('identifier'=>$identifier,'token'=>$token,'timeout'=>$timeout));
			setcookie($this->user_cookie_name,"$identifier:$token",$timeout,'/');
			$this->user = $user;
		}
	
		return $user;
	}

	
}//end of class
	
/* End of script
 * application/models/users_model.php
 */