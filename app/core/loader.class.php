<?php 
if(!defined('BASEPATH')) exit('Indirect Script Access not allowed');
/**
 * This class will be used to load the major objects used in our application
 */
class Loader extends Lobby{
	
	
	private $lobby;
		
	public function __construct()
	{
		$this->lobby = $GLOBALS['gobjects']['lobby'];
	}
	
	/// LOAD MODULES ///
	
	/**
	 * loads default classes
	 */
	public function load_defaults()
	{
		//load view and error class 
		$this->core('error', 'error');
		$this->core('view', 'view');
		
		
		//load libraries		
		//grab default libraries as set in config file
		$default_libraries = $this->config->default_libraries;
		//default classes must be array
		if(!$default_libraries or !(is_array($default_libraries))) return;
		//load each specified name
		foreach($default_libraries as $library_name => $set_name)
		{
            $this->library($library_name, $set_name);
		}
		
		//load default models
		
		//grab default models as set in config file
		$default_models = $this->config->default_models;
		//default classes must be array
		if(!$default_models or !(is_array($default_models))) return;
		//load each specified name
		foreach($default_models as $model_name => $set_name)
		{
			$this->load_model($model_name, $set_name);
		}
	}
	
	
	
	/**
	 * loads a class in the library folder
	 */
	public function library($class_name,$set_name)
	{
	$this->load($class_name,$set_name,'libraries');
	}
	
	
	/**
	 * loads a class in the core folder
	 */
	public function core($class_name,$set_name)
	{
	$this->load($class_name,$set_name,'core');
	}
	
	/**
	 * loads a class in the models folder
	 */
	public function load_model($class_name,$set_name = '')
	{
		if(!$set_name) $set_name = $class_name;
		$this->load($class_name,$set_name,'models');
	}
	
	/**
	 * loads a class in the controllers folder
	 */
	public function controller($class_name,$set_name='')
	{
		if(!$set_name) $set_name = $class_name;
		$this->load($class_name,$set_name,'controllers');
	}
	
	/**
	 * checks if a specified controller class has been loaded
	 */
	public function controller_exists($class_name)
	{				
		if(! $this->app_include("$class_name.".$this->config->class_file_ext,'controllers')) return false;
		if($this->config->ucfirst_classnames) $class_name = ucfirst($class_name);
		return class_exists($class_name);		
	}
	
	/**
	 * includes a class in the models folder without creating a new object
	 * @param string $class_names
	 */
	public function include_model($class_name)
	{
		$this->app_include("$class_name.".$this->config->class_file_ext,'models');
	}
	
	/**
	 * checks if a specified model class file has been loaded
	 */
	public function model_exists($class_name)
	{
		if(! $this->app_include("$class_name.".$this->config->class_file_ext,'models')) return false;
		if($this->config->ucfirst_classnames) $class_name = ucfirst($class_name);
		return class_exists($class_name);			
	}
	
	/**
	 * checks to see if a specified object has been loaded into the gobjects array
	 */
	public function gobject_loaded($object)
	{
		global $gobjects;
		return array_key_exists($object, $gobjects);
	}
	
	/**
	 * checks if a specified library class file has been loaded
	 */
	public function library_exists($class_name)
	{
		$this->app_include("$class_name.".$this->config->class_file_ext,'libraries');
		if($this->config->ucfirst_classnames) $class_name = ucfirst($class_name);
		return class_exists($class_name);
	}
	
	/**
	 * includes a class in the library folder without creating a new object
	 * @param string $class_name
	 */
	public function include_library($class_name)
	{
		$this->app_include("$class_name.".$this->config->class_file_ext,'libraries');
	}
	
	/**
	 * includes a helper file in the helper folder 
	 * @param string $filename_name
	 */
	public function include_helper($filename)
	{
		$this->app_include("$filename.".$this->config->helper_file_ext,'helpers');
	}
	
	/**
	 * includes a required application file
	 * @param string $class_name
	 */
	private function app_include($filename,$folder)
	{
		$app_file = $this->config->app_folder."$folder/$filename";
		if(file_exists($app_file)) { 
			include_once $app_file;
			return true;
		}
		else return false;	  
	}
	
	/**
	 * handles all app class loads and sets it in global objects via the parent lobby class
	 * @param string $class_name
	 * @param string $set_name
	 * @param string $folder_name
	 */
	private function load($class_name,$set_name,$folder_name)
	{
		if(! include_once($this->config->app_folder."$folder_name/$class_name.".$this->config->class_file_ext))
			throw new Exception(error_get_last()['message'].' In File: '.error_get_last()['file']);
		//uppercase first class name if specified in config file
		if($this->config->ucfirst_classnames) $class_name = ucfirst($class_name);
		if(! class_exists($class_name,false)) throw new Exception("Controller $class_name could not be loaded");		
		$this->lobby->$set_name = new $class_name;
	}
	


}
/**
 * end of loader class
 * 
 */