<?php

class User_Model extends Users_Model{
	
	private $user_id;
	private $username;
	private $email;
	private $password;
	private $user_identifier;
	private $login_token;
	private $login_timeout;
	private $user_data = array();
	public $save_all_data = true; //defines if user's data should be saved into db at the death of this object
	
	public function __construct($user){
		$this->user_id = $user->user_id;
		$this->username = $user->username;
		$this->email = $user->email;
		$this->password = $user->password;	
		$this->user_identifier = $user->identifier;
		$this->login_token = $user->token;
		$this->login_timeout = $user->timeout;	
		$this->load_data();
	}
	
	/**
	 * Loads any available userdata
	 */
	private function load_data()
	{
		$user_data = (unserialize($this->get_user_data($this)));
		if($user_data) $this->user_data = $user_data;
	}
	
	/**
	 * receives specific user data that will be saved when user object is about to be 
	 * destroyed
	 * @param string $name
	 * @param string $value
	 */
	
	public function save_data($name,$value)
	{
		$this->user_data[$name] = $value;
		
	}
	
	public function get_data($name)
	{
		if(!isset($this->user_data[$name])) return FALSE;
		return $this->user_data[$name];
	}
	
	public function get_identifier(){
		return $this->user_identifier;
	}
	
	public function get_login_token(){
		return $this->login_token;
	}
	
	public function get_login_timeout(){
		return $this->login_timeout;
	}

	public function get_email(){
		return $this->email;
	}
	
	public function get_username(){
		return $this->username;
	}
	
	public function get_password(){
		return $this->password;
	} 
	
	/**
	 * changes user password
	 * Only parent object is allowed to change this password
	 */
	public function change_password(Users_Model $parent, $password)
	{
		$this->password = $password;
	}
	
	public function get_id()
	{
		return $this->user_id;
	}
	
	public function __destruct()
	{
		//save any available userdata
		if($this->user_data && $this->save_all_data)
		{
			$this->save_user_data($this,serialize($this->user_data));
		}
	}
	
}


?>