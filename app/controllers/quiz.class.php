<?php 
if(!defined('BASEPATH')) exit('Indirect Script Access not allowed');

/**
 * This is the default controller class that controls our quiz game
 * @author Admins
 *
 */
class Quiz extends Lobby
{
	public function __construct()
	{
		parent::__construct();						
		
	}
	
	/**
	 * this page displays quiz settings
	 */
	public function index()
	{		
		$redirect = $this->redirect_to_last_page();
		if($redirect) return;
		
		$home_page = function(){
		//reset previous game
		$this->quiz_model->Reset_Game();
		//save last viewed page
		$this->quiz_model->Save_Last_Viewed_Page('home_page');
		$view_data['form_action'] = show_site();
		$view_data['marked_functions'] = $this->func_model->Count_User_Keywords();
		$view_data['unmarked_functions'] = $this->func_model->Count_User_Unmarked_Keywords();
		$view_data['all_value'] = $this->quiz_model->Count_Total_Available_Questions();
		$view_data['categories'] = $this->quiz_model->compile_categories();
		$body = $this->view->display_page('home_page',$view_data,true);
		$this->view->create_page($body,'Welcome To Medara');
		return;
		};
		
		//if setup form has been posted to this script
		if(isset($_POST['cmd']) && $_POST['cmd'] == 'startGame'){
				//setup game
				$result = $this->setup_game($_POST);
				//if setup parameters is not valid
				if(is_string($result) || !$result){
					//save error and display setup form
					$this->Save_Error_Msg($result);
					$home_page();
					return;
				}		
				//redirect to game page
				$this->quiz_model->Save_Last_Viewed_Page('game');
				header('Location: '.show_site('game'));
				return;
			
		}else {
			//display home page
			$home_page();
		}
		
	}
	
        /**
         * initiates the importation of new data into the database
         */
        public function import_data()
        {
            $count = $this->quiz_model->import_new_quiz_data();
            if($count)
                {
                echo "$count new keywords added to database";
            }
        }
        
	/**
	 * resets the game
	 */
	public function reset_game()
	{
		$this->quiz_model->reset_game();
		header('location: '.show_site());
		return;
	}
	
	private function validate_user()
	{
		if(!$this->users_model->user) {
			header('Location: '.$this->utility->show_site('user/login'));
			return false;
		}
		return true;
	}
	
	/**
	 * checks a user's login
	 * if last viewed page is saved, it redirects to last page else returns false
	 * @return void|boolean
	 */
	private function redirect_to_last_page()
	{
       if(! $this->validate_user()) return true; //if user is not logged in, redirect to login page
		
		//check if there was a last saved page
		$last_page = $this->quiz_model->Get_Last_Viewed_Page();
		if($last_page == 'game'){
			header('Location: '.$this->utility->show_site('game'));
			return true;
		}
		
		
		if($last_page == 'current-result'){
			header('Location: '.$this->utility->show_site('current-result'));
			return true;
		}
		
		if($last_page == 'final-result'){
			header('Location: '.$this->utility->show_site('final-result'));
			return true;
		}
		
		return false;
	}
	
	/**
	 * Display game here
	 */
	public function game()
	{
		if(! $this->validate_user()) return;
                //redirect to homepage if user is logged in but does not have a game session
                if(!$this->quiz_model->in_game())
		{	                                          
		 header('Location: '.$this->utility->show_site());
                 return;
		 }
                                    //redirect to results page if quiz is over
                                    if($this->quiz_model->Quiz_Over())
                                    {	                                          
                                     header('Location: '.$this->utility->show_site('final-result'));
                                     return;
                                     }
	//load and return game here
    $this->quiz_model->Save_Last_Viewed_Page('game');
    $view_data['reset_link'] = $this->utility->show_site('reset-game');
    $view_data['current_result'] = show_site('current-result');
    $username = $this->users_model->user->get_username();
    $view_data['user_name'] = $username;
    $view_data['user_link'] = show_site('user');
    $view_data['about_page'] = show_site('about');
    $view_data['logout_link'] = show_site('user/logout');
    $body = $this->view->display_page('quiz_page',$view_data,true);
    $this->view->create_page($body,'Medara: Words/Phrases Quiz');
    return;
	}
	
	/**
	 * returns the html template that will be used for the quiz 
	 * as requested by js
	 */
	public function get_template()
	{
		if(! $this->validate_user()) return;
		echo $this->view->display_page('quiz_template_main',array(),true);
	}
	
        /**
         * returns an array of the available subjects related the current status of this quiz
         */
        public function get_subjects(){
            if(! $this->validate_user()) return;
            $subjects = $this->quiz_model->get_quiz_subjects();
            shuffle($subjects);
            echo json_encode($subjects);
        }
        
	/**
	 * receives posted quiz data, processes it and returns new questions
	 */
	public function sync_quiz_data()
	{
		if(! $this->validate_user()) return;
		//process posted data here
		if(isset($_POST['quiz_data'])){
			$error_message = 'Posted data is in unrecognized format';
			$posted_data = json_decode($_POST['quiz_data']);
			//validate received data
			if( !is_object($posted_data) || !isset($posted_data->answered_ques) || !isset($posted_data->request_ids))
				{
					echo $error_message;
					return;
				}
			$answered_questions = $posted_data->answered_ques;
			$request_ids = $posted_data->request_ids;
			if(!is_array($answered_questions) || !is_array($request_ids)){
				echo $error_message;
				return;								
			}
			//synchronise answered questions
			$this->quiz_model->sync_answers($answered_questions);
			//get new questions as specified by number
			$quiz_data = array();			
		        $quiz_data['new_questions'] = $this->quiz_model->get_new_questions($request_ids);
                        $quiz_data['quiz_status'] = $this->quiz_model->get_quiz_status();                       
		    
		    echo json_encode($quiz_data);
		}
		
		
	}
	
        public function toggle_mark($id)
        {
            if(is_numeric($id)){
            $this->quiz_model->mark_function($id);
            echo json_encode(TRUE);
            }else{
                echo json_encode(FALSE);
            }
        }
        
	/**
	 * Display current result page here
	 */
	public function current_result()
	{
		if(! $this->validate_user()) return; //user will be redirected if not authenticated   

		if($this->quiz_model->in_game())
		{
			//do the folowing if game is in session
			if($this->quiz_model->Quiz_Over())
			{
				//redirect to current result if quiz is not over
				header('Location: '.$this->utility->show_site('final-result'));
				return;
			}
		//display final results here	
		$this->quiz_model->Save_Last_Viewed_Page('current-result');
		//load view data here
		$view_data['total_questions'] = $this->quiz_model->Count_Total_Test_Questions();
		$view_data['total_correct_answers'] = $this->quiz_model->Count_Total_Answered_Correct_Questions();
                $view_data['total_wrong_answers'] = $view_data['total_questions'] - $view_data['total_correct_answers'];
                $view_data['total_percentage_score'] = round(($view_data['total_correct_answers']/$view_data['total_questions'])*100);
                $view_data['reset_link'] = show_site('reset-game');
                $view_data['game_link'] = show_site('game');
                $view_data['user_link'] = show_site('user');
                $view_data['user_name'] = $this->users_model->user->get_username();
                $view_data['logout_link'] = show_site('user/logout');
                $view_data['sorted_results'] = $this->quiz_model->Sort_Results_By_Category();		
		$body = $this->view->display_page('current_result',$view_data,TRUE);
                $this->view->create_page($body,'Current Result');
		}else{
			//else redirect to home page
			header('Location: '.$this->utility->show_site());
			return;
		}
		
	}
	
	/**
	 * Display final result page here
	 */
	public function final_result()
	{
		if(! $this->validate_user()) return; //user will be redirected if not authenticated
		if($this->quiz_model->in_game())
		{
			//do the folowing if game is in session
			if(! $this->quiz_model->Quiz_Over())
			{
				//redirect to current result if quiz is not over
				header('Location: '.$this->utility->show_site('current-result'));
				return;
			}
		//display final results here	
		$this->quiz_model->Save_Last_Viewed_Page('final-result');
		//load view data here
		$view_data['total_questions'] = $this->quiz_model->Count_Total_Test_Questions();
		$view_data['total_correct_answers'] = $this->quiz_model->Count_Total_Answered_Correct_Questions();
                $view_data['total_wrong_answers'] = $view_data['total_questions'] - $view_data['total_correct_answers'];
                $view_data['total_percentage_score'] = round(($view_data['total_correct_answers']/$view_data['total_questions'])*100);
                $view_data['reset_link'] = show_site('reset-game');
                $view_data['user_link'] = show_site('user');
                $view_data['user_name'] = $this->users_model->user->get_username();
                $view_data['logout_link'] = show_site('user/logout');
                $view_data['sorted_results'] = $this->quiz_model->Sort_Results_By_Category();		
		$body = $this->view->display_page('final_result',$view_data,TRUE);
                $this->view->create_page($body,'Final Result');
		}else{
			//else redirect to home page
			header('Location: '.$this->utility->show_site());
			return;
		}
		
	}
	
        /**
         * displays the about page
         */
	public function about()
        {
            $body = $this->view->display_page('about',[],TRUE);
            $this->view->create_page($body,'Meddie: About');
        }
        
/**
sets-up data for the quiz to begin
*/
private function setup_game($form){
//validate posted values
$error_msg = 'your form values were incorrect, Please fill and resubmit the form again';
$validate = $this->utility->Validate_Form($form);



if($validate !== true){
return $validate;
}

if(!is_numeric($form['mode'])  || !is_numeric($form['questions']) || !is_numeric($form['source'])){
	return $error_msg;
}

$source_questions = $form['source'];
$mode = $form['mode'];
$questions = $form['questions'];
//mode must inbetween 1 and 2
if($mode != 1 && $mode != 2){
	return $error_msg;
}
//source questions must inbetween 1 and 3
if($source_questions < 1 || $source_questions > 3){
	return $error_msg;
}
//if source questions is from categories
if($source_questions == 1) $category = $form['category'];
else $category = null;
$multiple_trials = $form['mtrials'];
$autospeak = intval($form['autospeak']);
$autonew = intval($form['autonew']);
$this->quiz_model->Setup_New_Quiz($mode,$questions,$source_questions,$multiple_trials,$autospeak,$autonew,$category);
return true;

}
	

}

/*
 * end of script quiz.class.php
 */