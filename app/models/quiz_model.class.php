<?php
if(!defined('BASEPATH')) exit('Indirect Script Access not allowed');
/**
 * core Model for quiz game
 * @author Christian Ntong 
 */
class Quiz_Model extends Lobby

{

private $quiz_data;
private $current_question;
private $quiz_status = array();

public function __construct() 
{
$this->Load_Quiz_Data();
$this->load_quiz_status();
}	

private function Load_Quiz_Data(){
if($this->users_model->user) $this->quiz_data = $this->users_model->user->get_data('quiz_data') or array();

}

public function Save_Last_Viewed_Page($page_name){
	//($this->quiz_data);
$this->Save_Quiz_Data('last_page_viewed',$page_name);
}

public function Get_Last_Viewed_Page(){
	
return $this->quiz_data['last_page_viewed'];
}

private function Save_Quiz_Data($name,$value){

$this->quiz_data[$name] = $value;
//save quiz data into user account
if($this->users_model->user) $this->users_model->user->save_data('quiz_data',$this->quiz_data);

}

public function get_quiz_subjects()
{
    return $this->Get_Quiz_Data('subjects');
}

private function Get_Quiz_Data($name){
return $this->quiz_data[$name];
}

public function Setup_New_Quiz($mode,$requested_qs,$source_questions,$multiple_ts,$autospeak,$autonew,$category = null){
	//if requested source question is from language/category
	if($source_questions == 1 && is_numeric($category)){
//retrieve available questions according to selected  category
$subjects = $this->func_model->get_keyword_ids($category);
	}
	elseif($source_questions == 2){
		//use all marked questions for the quiz
		$subjects = $this->func_model->get_all_unmarked_keywords();
	}	
	elseif($source_questions == 3){
		//use all unmarked questions for the quiz
		$subjects = $this->func_model->get_all_marked_keywords();
	}
$available_qs = count($subjects);
if($available_qs >= $requested_qs){
$test_qs = $requested_qs;}else{
$test_qs = $available_qs;}
if($multiple_ts) $multiple_trials = true;
else $multiple_trials = false;
//save quiz data
//$this->Save_Quiz_Data('lang_category',$langid_categories);
//$this->Save_Quiz_Data('category',$category);
$this->Save_Quiz_Data('multiple_trials',$multiple_trials);
$this->Save_Quiz_Data('game_begun',true);
$this->Save_Quiz_Data('mode',$mode); //multi mode or single mode
$this->Save_Quiz_Data('requested_qs',$requested_qs); //number questions requested by user
$this->Save_Quiz_Data('available_qs',$available_qs); //actual number of available questions
$this->Save_Quiz_Data('subjects',$subjects); //array of the ids of our quiz subjects
$this->Save_Quiz_Data('test_qs',$test_qs); //number of questions that will be used in this quiz
$this->Save_Quiz_Data('current_q_no',0); //number of current question
$this->Save_Quiz_Data('current_q_id',0); //id of current question
$this->Save_Quiz_Data('current_q',array()); //array containing info about the current question being asked
$this->Save_Quiz_Data('prev_qs',array()); //an array containing the ids and grades of all previous questions
$this->Save_Quiz_Data('prev_qs_ids',array()); //a multi array containing just the ids of all previous questions
$this->Save_Quiz_Data('prev_q',array()); //an array containing info about the last question askes
$this->Save_Quiz_Data('prev_q_id',0); //id of previous question asked
$this->Save_Quiz_Data('prev_q_no',0); //number of previous question asked
$this->Save_Quiz_Data('multiple_answers',array());//contains an array of multiple answers saved from the current asked questions
$this->Save_Quiz_Data('autospeak',$autospeak); // set whether to auto speak words after disclosure
$this->Save_Quiz_Data('autonew',$autonew); // set whether to auto display a new question after the answer of a previous question has been displayed
}

    /**
     * Helps modify quiz settings while game is in session
     * @param type $mode
     * @param type $multiple_trials
     * @param type $autospeak
     * @param type $autonew
     */
    public function updateQuizSettings($mode,$multiple_ts,$autospeak,$autonew,$addLatest=0){
        if($multiple_ts) $multiple_trials = true;
        else $multiple_trials = false;        
        $this->Save_Quiz_Data('mode',$mode);
        $this->Save_Quiz_Data('multiple_trials',$multiple_trials);        
        $this->Save_Quiz_Data('autospeak',$autospeak);
        $this->Save_Quiz_Data('autonew',$autonew);
        if($addLatest){
            //add latest words to current quiz
            $current = $this->Get_Quiz_Data('subjects');
            $latest = $this->getLatestWords();
            $latestCurrent = array_unique(array_merge($current,$latest));
            sort($latestCurrent);
            $realLatest = array_diff($latestCurrent,$current);
                        if($realLatest){
                            $this->Save_Quiz_Data('subjects',$latestCurrent);
                            $this->Save_Quiz_Data('test_qs',$this->Get_Quiz_Data('test_qs')+count($realLatest));
                            $this->Save_Quiz_Data('requested_qs',$this->Get_Quiz_Data('requested_qs')+count($realLatest));
                            $this->Save_Quiz_Data('available_qs',$this->Get_Quiz_Data('available_qs')+count($realLatest));            
                        }
                             
        }
        return true;
    }

/**
Checks if the quiz is over
 * by counting the total number of subjects available
@return boolean
*/
public function Quiz_Over(){
$subjects = $this->Get_Quiz_Data('subjects');
if(count($subjects)) return false;
else return true;
}

private function set_quiz_status($name,$value)
{
    $this->quiz_status[$name] = $value;
}

/**
 sets an array of information about the quiz's current status
 this function will be loaded on startup
 */
private function load_quiz_status()
{
    $answered_questions = $this->Count_Total_Answered_Questions();
    $all_questions = $this->Count_Total_Test_Questions();
   $this->set_quiz_status('game_over', $this->Quiz_Over());
   $this->set_quiz_status('multi_mode', $this->Is_Multi_Mode());
   $this->set_quiz_status('autospeak', $this->autospeakEnabled());  
   $this->set_quiz_status('autonew', $this->autonewEnabled());     
   $this->set_quiz_status('allow_multiple_trials', $this->Allows_Multiple_Trials());
   $this->set_quiz_status('answered_questions', $answered_questions);
   $this->set_quiz_status('total_questions', $all_questions);
   $this->set_quiz_status('questions_remaining', ($all_questions - $answered_questions));
}

/**
 * returns an array of information about the quiz's current status
 */
public function get_quiz_status()
{
   // $this->error->log_data(implode('::',$this->quiz_status));
	return $this->quiz_status;
}

public function Reset_Game(){
	$this->func_model->Clear_Function_data();
	$this->Save_Quiz_Data('game_begun',false);
	$this->Save_Last_Viewed_Page('');
}

/*
 * checks whether current quiz mode allows multiple trials as specified by user
 * @return boolean
 */
public function Allows_Multiple_Trials()
{
	return $this->quiz_data['multiple_trials'];
}

private function Get_Previous_Questions(){
return $this->quiz_data['prev_qs'];
}

private function Get_Previous_Question_ids(){
return $this->quiz_data['prev_qs_ids'];
}

/**
 * randomly selects and returns new questions as specified by $number
 */
public function get_new_questions(Array $request_ids)
{
    if(!$request_ids) return array();

//convert selected ids to full quiz data

$question_data_getter = function($id){
	$answer = $this->func_model->Get_Function_Name($id);
	$question = $this->func_model->Get_Function_Description($id);
        $examples = $this->func_model->get_word_examples($id);
	$return_value = array(
            'id'=>$id,
            'question'=>$question,
            'answer'=>$answer,
            'marked'=>$this->func_model->marked_function($id),
            'examples'=>$examples
                );
	//compile options if quiz is in multi mode
	if($this->Is_Multi_Mode()){
		//get correct answer
		$question_id = $id;
		//get 3 other answers in same category
		$cat_id = $this->func_model->Get_Function_Category_Id($question_id);
		$cat_functions = $this->func_model->Get_Category_Functions($cat_id,'name');
		//pick an answer different from $answer
		$multi_answer[0] = $answer;
		$cat_functions = array_diff($cat_functions,$multi_answer);
		$random_key = array_rand($cat_functions);
		$multi_answer[1] = $cat_functions[$random_key];
		$cat_functions = array_diff($cat_functions,$multi_answer);
		$random_key = array_rand($cat_functions);
		$multi_answer[2] = $cat_functions[$random_key];
		$cat_functions = array_diff($cat_functions,$multi_answer);
		$random_key = array_rand($cat_functions);
		$multi_answer[3] = $cat_functions[$random_key];
		shuffle($multi_answer);
		$return_value['options'] = $multi_answer;
	}

	return $return_value;
};

$new_questions = array();
foreach($request_ids as $id){
	$new_questions[] = $question_data_getter($id);
}

return $new_questions;
/*
 * format
 * $quiz_data = array('new_questions'=>array(array('id'=>35,'question'=>'What is your name','answer'=>'john','options'=>array('okon','john','bassey','chris'))));
 */

}

/**
 * DEPRECATED
 */
private function Prepare_Question(){
$available_questions = $this->Get_Quiz_Data('subjects');
//pick one question in random
$next_question_key = array_rand($available_questions);
$this->current_question = $available_questions[$next_question_key];
//remove next question from current available and save
unset($available_questions[$next_question_key]);
$re_indexed = array_values($available_questions);
$this->Save_Quiz_Data('subjects',$re_indexed);
$this->Save_Quiz_Data('current_q_id',$this->current_question);
$this->Save_Quiz_Data('current_q_no',$this->Get_Current_Question_No()+1);
}

/**
 * DEPRECATED
 * @return type
 */
public function Get_Current_Question(){
//check for any saved question
if($this->quiz_data['current_q']){
return $this->quiz_data['current_q'];}
//prepare new question
$this->Prepare_Question();
$question_id = $this->current_question;
$question = $this->func_model->Get_Function_Description($question_id);
$this->Save_Quiz_Data('current_q',$question);
return $question;
}

/**
 * returns answer to the current question
 * @return string
 */
public function Get_Current_Question_Answer()
{
	return $this->func_model->Get_Function_Name($this->quiz_data['current_q_id']);
}

public function Get_Current_Question_Answer_Prefix()
{
	return $this->func_model->Get_Function_Prefix($this->current_question);
}

public function Get_Current_Question_No(){
return $this->Get_Quiz_Data('current_q_no');
}

public function Get_Current_Question_Language()
{
	return $this->func_model->Get_Lang_Name($this->func_model->Get_Function_Lang_Id($this->Get_Quiz_Data('current_q_id')));
}

public function Count_Total_Available_Questions(){
return $this->func_model->Count_Available_Keywords();
}

public function Count_Total_Test_Questions(){
return $this->Get_Quiz_Data('test_qs');
}

public function Count_Total_Answered_Questions(){
return count($this->Get_Quiz_Data('prev_qs'));
}

public function Count_Total_Answered_Correct_Questions(){
$prev_qs = $this->quiz_data['prev_qs'];
$count = 0;
foreach($prev_qs as $prev_q){
if($prev_q['evaluation']){
++$count;
}
}//end foreach
return $count;
}

public function Count_Total_Answered_Wrong_Questions(){
$prev_qs = $this->quiz_data['prev_qs'];
$count = 0;
foreach($prev_qs as $prev_q){
if(!$prev_q['evaluation']){
++$count;
}
}//end foreach
return $count;
}

public function Sort_Results_By_Category(){
	//get previous questions asked
$results = $this->quiz_data['prev_qs'];
//take out category column into a separate array
$categories = array_column($results,'category');
//find and count unique values
$unique_cats = array_count_values($categories);
$s_results = array();
$i = 0;
//place into a sorted results array
foreach($unique_cats as $category => $total_count){
$s_results[$i]['category'] = $this->func_model->Get_Category_Name($category);
$s_results[$i]['total_count'] = $total_count;
$s_results[$i]['wrong_count'] = 0;
$s_results[$i]['right_count'] = 0;


//add other data into sorted results
foreach($results as $result){
if($result['category'] == $category){
if($result['evaluation']){
++$s_results[$i]['right_count'];
}else{
++$s_results[$i]['wrong_count'];
}//end if
}//end if
}//end foreach 2
//calculate percentage score
$s_results[$i]['percentage_score'] = round(($s_results[$i]['right_count']/$s_results[$i]['total_count'])*100);
$i++;
}//end foreach

return $s_results;

}

public function Is_Multi_Mode(){
//checks if quiz is in multi options mode
$mode = $this->Get_Quiz_Data('mode');
if($mode == 1){
return true;}
return false;
}

/**
 * returns if autospeak is enabled in quiz or not
 * @return boolean
 */
public function autospeakEnabled(){
//checks if quiz is in multi options mode
if($this->Get_Quiz_Data('autospeak') == 1) return true;
return false; //else
}

/**
 * returns if autonew is enabled in quiz or not
 * @return boolean
 */
public function autonewEnabled(){
//checks if quiz is in multi options mode
if($this->Get_Quiz_Data('autonew') == 1) return true;
return false; //else
}

/**
 * returns if current user has begun the game
 * @return boolean
 */
public function in_game()
{
	if($this->Get_Quiz_Data('game_begun')) return true;
	else return false;
	
}

/**
 * If multiple answers were saved in the session for the current user
 * retrieve it
 * @return boolean
 */
private function Get_Saved_Answers(){
if(isset($this->quiz_data['multiple_answers'])){
return $this->quiz_data['multiple_answers'];
}else{
return false;}
}

private function Remove_Quiz_Data($name){
unset($this->quiz_data[$name]);
$_SESSION['quiz_data'] = $this->quiz_data;
}

/**
 * return answer of current question with 3 other multiple answers
 * @return type
 */
public function Get_Multiple_Answers(){
//get and return already save answers
$saved_answers = $this->Get_Saved_Answers();
if($saved_answers){
return $saved_answers;
}
//get correct answer
$question_id = $this->current_question;
//get 3 other answers in same category
$cat_id = $this->func_model->Get_Function_Category_Id($question_id);
$cat_functions = $this->func_model->Get_Category_Functions($cat_id);

$answer[0] = $question_id;
$question_key = array_search($question_id,$cat_functions);
unset($cat_functions[$question_key]);

$random_key = array_rand($cat_functions);
$answer[1] = $cat_functions[$random_key];
unset($cat_functions[$random_key]);

$random_key = array_rand($cat_functions);
$answer[2] = $cat_functions[$random_key];
unset($cat_functions[$random_key]);

$random_key = array_rand($cat_functions);
$answer[3] = $cat_functions[$random_key];
unset($cat_functions[$random_key]);

//shuffle
shuffle($answer);
//get real answers, save and return
foreach($answer as $key => $func_id){
$real_answer[$key] = $this->func_model->Get_Function_Name($func_id);
}
$this->Save_Quiz_Data('multiple_answers',$real_answer);
return $real_answer;
}

public function sync_answers(Array $answers)
{
	//$all_ready_questions = $this->Get_Quiz_Data('subjects');
        if(!$answers) return false;
     //   $remove_subjects = array();
        $prev_questions_ids = $this->Get_Previous_Question_ids();
	foreach($answers as $answer)
	{            
            $subject_id = $answer->id;
            //if this subject has already been saved earlier, dont bother saving again
               if(in_array($subject_id,$prev_questions_ids)) continue;
              $this->save_answer($subject_id, $answer->grade);
              $this->remove_subject($answer->id);
             // $remove_subjects[] = $answer->id;
	}
        /*
        $subjects = array_diff($all_ready_questions,$remove_subjects);
        sort($subjects);
        $this->Save_Quiz_Data('subjects', $subjects);
         * */
         
        //update quiz status        
     $answered_questions = $this->Count_Total_Answered_Questions();
    $all_questions = $this->Count_Total_Test_Questions();
   $this->set_quiz_status('answered_questions', $answered_questions);
   $this->set_quiz_status('questions_remaining', ($all_questions - $answered_questions));
   //set if game is over
   $this->set_quiz_status('game_over', $this->Quiz_Over());
	return true;
}

private function remove_subject($id)
{
    $subjects = $this->Get_Quiz_Data('subjects');
    unset($subjects[array_search($id, $subjects)]);
    sort($subjects);
    $this->Save_Quiz_Data('subjects', $subjects);
}

private function save_answer($question_id,$grade)
{
    
	if($grade){
		$evaluation = true;
                
	}else{
		$evaluation = false;
	}
	$category = $this->func_model->Get_Function_Category_Id($question_id);
        $prev_ques = $this->Get_Previous_Questions();
        $prev_ques_ids = $this->Get_Previous_Question_ids();
        $prev_ques[] = array('function'=>$question_id,'evaluation'=>$evaluation,'category'=>$category);     
        $prev_ques_ids[] = $question_id;
        $this->Save_Quiz_Data('prev_qs',$prev_ques);	
        $this->Save_Quiz_Data('prev_qs_ids', $prev_ques_ids);
	return true;
}



/**
 * DEPRECATED
 * @param unknown $answer
 * @return boolean
 */
public function Process_Answer($answer){
$current_question_id = $this->quiz_data['current_q_id'];
$prev_ques = $this->Get_Previous_Questions();
//get prefix
$prefix = $this->func_model->Get_Function_Prefix($current_question_id);
$func_name = $this->func_model->Get_Function_Name($current_question_id);
//evaluate answer and save
if(!$prefix) $prefix = '';
if($answer == $func_name or $answer == $prefix.$func_name){
$evaluation = true;
}else{
$evaluation = false;
}

$category = $this->func_model->Get_Function_Category_Id($current_question_id);
$lang_id = $this->func_model->Get_Category_Lang_Id($category);
$prev_ques[] = array('function'=>$current_question_id,'evaluation'=>$evaluation,'category'=>$category,'language'=>$lang_id);
//save answer
$this->Save_Quiz_Data('last_answer',$answer);
//move current data to previous data
$this->Save_Quiz_Data('prev_q',$this->quiz_data['current_q']);
$this->Save_Quiz_Data('prev_qs',$prev_ques);
$this->Save_Quiz_Data('prev_q_id',$current_question_id);
$this->Save_Quiz_Data('prev_q_no',$this->quiz_data['current_q_no']);
//reset current data
$this->Save_Quiz_Data('current_q_id',0);
$this->Save_Quiz_Data('current_q',0);
$this->Save_Quiz_Data('multiple_answers',0);


return true;
}

public function Get_Last_Answer(){
return $this->quiz_data['last_answer'];
}

public function Get_Last_Question(){
return $this->quiz_data['prev_q'];
}

public function Get_Last_True_Answer(){
//return $this->func_model->Get_Function_Name($this->quiz_data['prev_q_id']);
$question_id = $this->quiz_data['prev_q_id'];
$prefix = $this->func_model->Get_Function_Prefix($question_id);
$func_name = $this->func_model->Get_Function_Name($question_id);
if(!$prefix) $prefix = '';
return $prefix.$func_name;
}

public function Get_Last_True_Answer_Id(){
	return $this->quiz_data['prev_q_id'];
}

public function Get_Last_Answer_Url(){
return $this->func_model->Get_Function_Url($this->quiz_data['prev_q_id']);
}

public function Get_Last_Answer_Syntax(){
return $this->func_model->Get_Function_Syntax($this->quiz_data['prev_q_id']);
}

public function Get_Last_Answer_Lang(){
	return $this->func_model->Get_Lang_Name($this->func_model->Get_Function_Lang_Id($this->Get_Quiz_Data('prev_q_id')));
}

/**
checks if the last answer was right or wrong
*/
public function Last_Answer_Correct(){
$prev_question = end($this->quiz_data['prev_qs']);
return $prev_question['evaluation'];
}

/**
 * compiles the languages and their different categories into an array
 * @return array
 */
public function compile_categories() {
//get all categories

	$category_ids = $this->func_model->Get_Category_Ids();
	$compiled_categories = array();
	foreach($category_ids as $cat_id)
	{
		$compiled_categories[] = array('name'=>$this->func_model->Get_Category_Name($cat_id),'id'=>$cat_id,'count'=>$this->func_model->Count_Available_Category_Keywords($cat_id));
	}
	return $compiled_categories;
}

/**
 * marks or unmarks a function in a user's account
 * @param unknown $func_id
 */
public function Mark_Function($func_id)
{
	return $this->func_model->Mark_User_Function($func_id);
}



        /**
         * imports latest word data from 
         * a csv file
         */
public function import_new_quiz_data()
{
    $all_keywords = file('assets/data/new_keywords.csv');
//remove the headers
$titles = str_getcsv(array_shift($all_keywords));
$info = 0;
$wordIds = array();
//load all keyword data from the db
$this->func_model->load_all_keyword_data();
foreach($all_keywords as $keyword){

$keyword_data = str_getcsv($keyword);
$compiled_keyword_data = [];
foreach($titles as $title_key => $title)
{
$compiled_keyword_data[$title] = trim($this->dataman->Escape_String_For_Db($keyword_data[$title_key])); 

}
    $wordId = $this->func_model->save_new_keyword($compiled_keyword_data);
    if(is_numeric($wordId)) $wordIds[] = $wordId;
    $info++;
}
//log import info
$startWord = $wordIds[0];
$endWord = end($wordIds);
$today = date('Y-m-d H:i:s');
$this->dataman->Query_Db_In("INSERT INTO medara.import_info (date,start_id,end_id)VALUES('$today',$startWord,$endWord)");
return $info;
}

/**
 * returns the number of the latest words that were added to the word database
 */
public function countLatestWords(){
    $sql = 'SELECT count(id) 
            FROM medara.keyword as keyword 
            WHERE keyword.id >= (SELECT start_id FROM medara.import_info ORDER BY id desc LIMIT 1) 
            AND keyword.id <= (SELECT end_id FROM medara.import_info ORDER BY id desc LIMIT 1)';
    return $this->dataman->fetchValue($sql);
}

/**
 * returns a count of new words that have been added to the db since the last time the user updated the quiz questions
 */
public function getQuizLatestWordCount(){
    $lastWordId = $this->dataman->fetchValue('SELECT end_id FROM medara.import_info ORDER BY id desc LIMIT 1');
    $current = $this->Get_Quiz_Data('subjects');
    if(in_array($lastWordId,$current)) return 0;
    else return $this->countLatestWords();
}

/**
 * returns the latest words that were added to the word database
 */
public function getLatestWords(){
    $sql = 'SELECT id 
            FROM medara.keyword as keyword 
            WHERE keyword.id >= (SELECT start_id FROM medara.import_info ORDER BY id desc LIMIT 1) 
            AND keyword.id <= (SELECT end_id FROM medara.import_info ORDER BY id desc LIMIT 1)';
    return $this->dataman->fetchValues($sql);
}

}//end of class

/**
 * ENd of script quiz_model.class.php
 */