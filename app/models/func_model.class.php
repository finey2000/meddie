<?php
if(!defined('BASEPATH')) exit('Indirect Script Access not allowed');
/**
This class handles our core data. Loads words/phrases and their categories
@author Christian Ntong
*/
class Func_Model extends Lobby

{

private $category_data;
private $keyword_data;

private $lang_data;
private $prefix_data;

private $user_keywords = array(); //carries data about all marked functions of the user

public function __construct() 
{
$this->save_keyword_data();
$this->Load_User_Functions();

}	

/*
 * saves new word data into the database
 */
public function save_new_keyword($word_data){
    $names = '';
    $values = '';
    foreach($word_data as $word_title => $word){
        //check if keyword already exists
        if(strtolower($word_title) == 'name'){
            $word = strtolower($word);
            $existing_word_id = $this->get_keyword_id($word);
            if($existing_word_id){
                //update keyword if it already exists and return the function.
                $this->update_keyword($existing_word_id,$word_data);
                return;
            }
        }
        //add commas to name if a name already existed
        if($names) $names .= ',';
        //convert multibyte word titles
        //var_dump(mb_check_encoding($word));
        $word = mb_convert_encoding($word, 'ASCII');
        //attach word title
        $names .= $word_title;
        if($values) $values .= ',';
        $values .= '"'.$word.'"';
    }
$query = "INSERT INTO keyword ($names) VALUES ($values)";
$this->dataman->Query_Db_In($query);
return $this->dataman->lastInsertId();
}

/**
 * updates information about a specified keyword
 * @param integer $id
 * @param array $word_data
 * @return boolean
 */
private function update_keyword($id,$word_data){    
    $values = '';
    foreach($word_data as $word_title => $word){
        //convert word to relevant encoding
        $word = mb_convert_encoding($word, 'ASCII');
        //add commas to name if a name already existed
        if($values) $values .= ',';
        $values .= "$word_title = '$word'";
    }    
    $query = "UPDATE keyword set $values WHERE id = $id";
    $this->dataman->Query_Db_In($query);
    return true;
}

private function Get_Category_Data(){
$query = "SELECT * FROM category";
$this->category_data = $this->dataman->Get_Data_Rows($query);
}

/**
 * DEPRECATED
 * loads prefix data from the db
 */
private function Load_Prefix_Data()
{
	$query = "SELECT * FROM prefix";
	$this->prefix_data = $this->dataman->Get_Data_Rows($query);
}

/**
 * DEPRICATED
 * loads language data from the db
 */
private function Load_Lang_Data(){
	$query = "SELECT * FROM language";
	$this->lang_data = $this->dataman->Get_Data_Rows($query);
}

/**
 * DEPRECATED
 * returns the name of a language id
 * @param int $lang_id
 * @return string $lang_name
 */
public function Get_Lang_Name($lang_ids)
{
	if(!$this->lang_data) $this->Load_Lang_Data();
	//fetch language ids and names
	$all_lang_ids = array_column($this->lang_data,'lang_id');
	$all_lang_names = array_column($this->lang_data,'lang_name');
	//if we received an array of ids
	if(is_array($lang_ids))
	{
		$lang_names = array();
		foreach($lang_ids as $lang_key => $lang_id)
		{
			$found_key = array_search($lang_id,$all_lang_ids);
			//return error if name does not exist in db
			if(!is_integer(intval($found_key))) return false;
			//compile ids into array with same index value
			$lang_names[$lang_key] = $all_lang_namess[$found_key];
		}
		return $lang_names;
	}
	//else $lang_names is an individual string
	$found_key = array_search($lang_ids,$all_lang_ids);
	return $all_lang_names[$found_key];
}

/**
 * DEPRECATED
 * returns all the languages in the database
 * both with name and id
 */
public function get_available_langs()
{
	if(!$this->lang_data) $this->Load_Lang_Data();
	$lang_ids = array_column($this->lang_data,'lang_id');
	$lang_names = array_column($this->lang_data,'lang_name');
	return array_combine($lang_ids,$lang_names);
}

/**
 * DEPRECATED
 * @param unknown $lang_names
 * @return boolean|multitype:unknown |unknown
 */
public function get_lang_id($lang_names)
{
	//load language data if not loaded
	if(!$this->lang_data) $this->Load_Lang_Data();
	//fetch language ids and names
	$all_lang_ids = array_column($this->lang_data,'lang_id');
	$all_lang_names = array_column($this->lang_data,'lang_name');
	//if we received an array of names
	if(is_array($lang_names))
	{
		$lang_ids = array();
		foreach($lang_names as $lang_key => $lang_name)
		{
			$found_key = array_search($lang_name,$all_lang_names);			
			//return error if name does not exist in db
			if(!is_integer(intval($found_key))) return false;
			//compile ids into array with same index value
			$lang_ids[$lang_key] = $all_lang_ids[$found_key];
		}
		return $lang_ids;
	}
	//else $lang_names is an individual string
	$found_key = array_search($lang_names,$all_lang_names);
	return $all_lang_ids[$found_key];
}



/**
 * DEPRECATED
 * returns the language id of a given category id
 * @param unknown $category
 */
public function Get_Category_Lang_Id($category_id)
{
	if(is_numeric($category_id))
	{
		if(!$this->category_data) $this->Get_Category_Data();
		$lang_ids = array_column($this->category_data,'language');
		$category_ids = array_column($this->category_data,'id');
		$lang_id = $lang_ids[array_search($category_id,$category_ids)];
		if(is_numeric($lang_id)) return $lang_id;
		return false;//else return false
	}
	return false;
}


private function save_keyword_data($data = null){

if(!is_null($data)){
$this->keyword_data = $data;
if($this->users_model->user) $this->users_model->user->save_data('func_data',array()); //save function info no more on user data table
return;
}else{

//load function data from user account
	//if($this->users_model->user) $this->keyword_data = $this->users_model->user->get_data('func_data');
	//if not available, load from db
	if(!$this->keyword_data) $this->get_keyword_data();
}

}

public function Clear_Function_data()
{
	$this->save_keyword_data(array());
}

private function Load_User_Functions()
{
	//load user's saved functions from user account
	if($this->users_model->user) {
		$user_functions = $this->users_model->user->get_data('user_functions');
		if($user_functions) $this->user_keywords = $user_functions;
	}
	
	
}

public function Count_User_Keywords()
{
	if(!$this->user_keywords) return 0;
	else return count($this->user_keywords);
}

public function Count_User_Unmarked_Keywords()
{
	$all_funcs = $this->Count_Available_Keywords();
	if(!$this->user_keywords) return $all_funcs;
	else return ($all_funcs - count($this->user_keywords));	
}

/**
 * returns the ids of all user marked functions
 */
public function get_all_marked_keywords()
{
	return $this->user_keywords;
}

/**
 * returns the ids of all user unmarked functions
 */
public function get_all_unmarked_keywords()
{
	
	$all_keywords = array_column($this->keyword_data,'id');
	return array_diff($all_keywords,$this->user_keywords);
}

private function Save_User_Functions()
{
	if($this->users_model->user) $this->users_model->user->save_data('user_functions',$this->user_keywords);
}

/**
 * marks or unmarks a function for the logged in user
 * @param unknown $func_id
 */
public function Mark_User_Function($func_id)
{
	$all_function_ids = array_column($this->keyword_data,'id');
	//if function id does not exist return fals
	if(!in_array($func_id,$all_function_ids)) return false;
	//if function id is already marked, unmark
	if(in_array($func_id,$this->user_keywords)){
		unset($this->user_keywords[array_search($func_id, $this->user_keywords)]);
		sort($this->user_keywords);
	}
	else{
		$this->user_keywords[] = $func_id;
	}
    $this->Save_User_Functions();
	return true;
}

/**
 * checks if a given function id has been marked by user
 * @param integer $func_id
 * @return boolean
 */
public function Marked_Function($func_id)
{
	return in_array($func_id,$this->user_keywords);
}

private function get_keyword_data($category = 0){	
if(!$category){
$query = "SELECT * FROM keyword;";
}else{
$query = "SELECT * FROM keyword WHERE category = $category;";}
$this->save_keyword_data($this->dataman->Get_Data_Rows($query));

}

/**
 * Makes sure that keyword data being used is loaded from database and not just from
 * user saved keywords
 * @return boolean
 */
public function load_all_keyword_data()
{
    $this->get_keyword_data();
    return true;
}

/**
 * gets the function ids for specified languages according to specified categories
 * @param number $category
 */
public function get_keyword_ids($category){
		$this->get_keyword_data($category);
        return array_column($this->keyword_data,'id');
}

public function Count_Available_Keywords(){
$query = "SELECT COUNT(*) FROM keyword;";
$data = $this->dataman->Get_Data_Rows($query);
return $data[0]['COUNT(*)'];
}

/**
 * counts and returns total functions in a language
 */
public function Count_Available_Lang_Functions($lang_id){
	$query = "SELECT COUNT(*) FROM keyword WHERE language = $lang_id;";
	$data = $this->dataman->Get_Data_Rows($query);
	return $data[0]['COUNT(*)'];
}

/**
 * counts and returns total functions in a category
 */
public function Count_Available_Category_Keywords($cat_id){
	$query = "SELECT COUNT(*) FROM keyword WHERE category = $cat_id;";
	$data = $this->dataman->Get_Data_Rows($query);
	return $data[0]['COUNT(*)'];
}

/** 
 * gets all the function in a specified category 
 * */
public function Get_Category_Functions($cat_id,$column = null){

if(empty($this->keyword_data)){
$this->get_keyword_data();
}
$data = $this->keyword_data;
$i = 0;
$funcs = array();

foreach($data as $function){
if($function['category'] == $cat_id){
	//return column else return ids
	if(!$column) $funcs[$i] = $function['id'];	
	elseif(isset($function[$column])){
		$funcs[$i] = $function[$column];		
		}else return false;

$i++;
}
}
return $funcs;
}


/**
 * returns the category id of a specified function
 * @param unknown $func_id
 * @return integer
 */
public function Get_Function_Category_Id($func_id){
if(empty($this->keyword_data)){
$this->get_keyword_data();
}
$data = $this->keyword_data;
foreach($data as $function){
$current_id = $function['id'];
if($current_id == $func_id){
return $function['category'];
}//end if
}//end foreach

}


public function Get_Function_Description($func_id){
return $this->Get_Function_Value($func_id,'description');
}

public function get_word_examples($id){
    return $this->Get_Function_Value($id, 'examples');
}

public function Get_Function_Name($func_id){
return $this->Get_Function_Value($func_id,'name');
}

/**
 * returns the prefix name of a function if any exists
 * @param unknown $func_id
 */
public function Get_Function_Prefix($func_id)
{
	$prefix_id = $this->Get_Function_Value($func_id,'prefix');
	if(!$prefix_id) return false;
	if(empty($this->prefix_data)){
		$this->Load_Prefix_Data();		
	}
	$prefix_ids = array_column($this->prefix_data,'prefix_id');
	$prefix_names = array_column($this->prefix_data,'prefix_name');
	if(!in_array($prefix_id,$prefix_ids)) return false;
	$prefix_key = array_search($prefix_id,$prefix_ids);
	return $prefix_names[$prefix_key];
}

public function Get_Function_Url($func_id){
return $this->Get_Function_Value($func_id,'url');
}

//returns the language id of given function id
public function Get_Function_Lang_Id($func_id){
	return $this->Get_Function_Value($func_id,'language');
}

public function Get_Function_Syntax($func_id){
return $this->Get_Function_Value($func_id,'syntax');
}

private function Get_Function_Value($func_id,$value_name){
$func_ids = array_column($this->keyword_data,'id');
$values = array_column($this->keyword_data,$value_name);
for($i=0;$i<count($func_ids);$i++){
if($func_ids[$i] == $func_id){
$value = $values[$i];
return $value;
}

}

}

/**
 * returns the id of a provided keyword
 * @param type $keyword
 */
private function get_keyword_id($keyword)
{
    $func_ids = array_column($this->keyword_data,'id');
$keywords = array_column($this->keyword_data,'name');
if(!in_array(strtolower($keyword),$keywords)) return false;
$id = array_search(strtolower($keyword),$keywords);
return $func_ids[$id];
}


public function Get_Category_Ids(){
if(!$this->category_data) $this->Get_Category_Data();
return array_column($this->category_data,'id');
}

/**
 * DEPRECATED
 * @return multitype:
 */
public function get_category_languages()
{
	if(!$this->category_data) $this->Get_Category_Data();
	$cat_ids = array_column($this->category_data,'id');
	$cat_lang_ids = array_column($this->category_data,'language');
	return array_combine($cat_ids, $cat_lang_ids);
}

public function Get_Category_Name($category_id){
if(empty($this->category_data)){
$this->Get_Category_Data();
}
$data = $this->category_data;
foreach($data as $category){
$current_id = $category['id'];
if($current_id == $category_id){
return $category['name'];
}//end if
}//end foreach

}//end function

}

/*
 * End of func_man controller
 */