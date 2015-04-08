<?php 
if(!defined('BASEPATH')) exit('Indirect Script Access not allowed');
/**
 * This is class is the controller class for users
 */

class User extends Lobby
{
	public function __construct(){
		parent::__construct();
		
		//if(!$this->load->gobject_loaded('users_model') && $this->load->model_exists('users_model'))
		//$this->load->load_model('users_model');
	}
	
	public function login()
	{
		if(isset($_POST['username']) && isset($_POST['pswd'])){
			
			//process form here
			$username = $_POST['username'];
			$password = $_POST['pswd'];
			$user = $this->users_model->get_user($username,$password);
			if($user){
				$this->users_model->set_user($user,true);
				//redirect
				header('Location: '.show_site());//redirect to main page
				return;
			}
			else{
				$this->utility->set_form_error('Sorry: Username or Password is Incorect. Please try again');
			}
		}
		//display login page
		$page_body = $this->view->display_page('login_form',array('form_action'=>show_site('user/login'),'new_account_src'=>show_site('user/create_new')),true);
		echo $this->view->create_page($page_body,'User Login',array(),array(),true);
		return;
	}
	
	public function index()
	{
		$user = $this->users_model->user;
		if(!$user) header('Location: '.$this->utility->show_site('user/login'));
		$data['username'] = $user->get_username();
		$data['change_pswd_url'] = show_site('user/change_password');
		$data['logout_url'] = show_site('user/logout');
		$data['delete_user_url'] = show_site('user/delete_account');
		$data['game_url'] = show_site();
		$page_body = $this->view->display_page('user',$data,TRUE);
		echo $this->view->create_page($page_body,'Account',array(),array(),true);
		return;
	} 
	
	/**
	 * logsout and delete the user's account
	 */
	public function delete_account()
	{
		$user = $this->users_model->user;
		if(!$user) header('Location: '.$this->utility->show_site('user/login'));
		$this->users_model->delete_user();
		header('Location: '.$this->utility->show_site('user/login'));
		return;
	}
	
	public function change_password()
	{
		$user = $this->users_model->user;
		if(!$user) header('Location: '.$this->utility->show_site('user/login'));
		
		if( ! $_POST) return $this->change_password_helper(); // if form values are not posted
		
		//process form values here
		$validate = $this->utility->Validate_Form($_POST,false);
		if($validate !== TRUE) return $this->change_password_helper($validate);
		$form = $this->utility->Sanitize_Data($_POST);
		if( ! $this->utility->password_matches($form['pswd1'],$user->get_password())) return $this->change_password_helper('Sorry: Old Password is Incorrect');
		if($form['pswd2'] != $form['pswd3']) return $this->change_password_helper('Sorry: Your New Password Must Match');
		$this->users_model->change_user_password($form['pswd2']);
		header('Location: '.show_site('user'));
		return;
	}
	
	private function change_password_helper($form_error = null)
	{
		if($form_error) $this->utility->set_form_error($form_error); //set form error
                $body = $this->view->display_page('change-password',array('form_action'=>show_site('user/change-password')),true);                    
                $this->view->create_page($body,'Change Password');
		return;
	}
	
	public function create_new()
	{
		if( ! $_POST) return $this->new_user_helper(); // if form values are not posted

			//process form values here
			$validate = $this->utility->Validate_Form($_POST,false);
			if($validate !== TRUE) return $this->new_user_helper($validate);

				$form = $this->utility->Sanitize_Data($_POST);
				//check for valid email
				if( ! $this->utility->Validate_Email($form['email'])) return $this->new_user_helper('Sorry: Invalid Email Provided');
					//validate username			
                   if( ! $this->utility->Validate_Username($form['username'])) return $this->new_user_helper('Sorry: Incorrect user provided');                         
				      	//check if user exists
                if($this->users_model->we_have_user($form['username'])) return $this->new_user_helper('Sorry: This User already exists');
                //validate password
                if($form['pswd'] != $form['pswd2']) return $this->new_user_helper('Sorry: Your Passwords must match');
                //create user
                $this->users_model->create_user($form['username'],$form['pswd'],$form['email']);
                //login user
                $this->users_model->set_user($this->users_model->get_user($form['username'],$form['pswd']),true);
                //redirect
                header('Location: '.show_site('user'));
                


	}
	
	/**
	 * helps create new function above
	 */
	private function new_user_helper($form_error = null){            
		if($form_error) $this->utility->set_form_error($form_error); //set form error
                    $view_data['form_action'] = show_site('user/create-new');
                    $body = $this->view->display_page('new-user',$view_data,true);                    
                    $this->view->create_page($body,'Meddie Quiz: New User');		
		return;
	}
	
	public function logout(){
		if(!$this->users_model->user) header('Location: '.$this->utility->show_site('user/login'));//if user does not exist, redirect
		$this->users_model->logout_user();//logout user
		header('Location: '.$this->utility->show_site('user/login')); //redirect
		return;
	}
}

/**
 * end of user controller class
 */