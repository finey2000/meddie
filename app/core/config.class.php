<?php 
if(!defined('BASEPATH')) exit('Indirect Script Access not allowed');

/**
 * This class holds configuration data for our application
 */
class Config {
	
	public $app_folder;
	public $basepath;
	public $site_url;
	public $assets_url;
	public $db_host;
	public $db_user;
	public $db_pswd;
	public $db_name;
	public $default_libraries;
	public $default_models;
	public $default_controller;
	public $default_method;
	public $server_rewrite_dir_name;
	public $class_file_ext;
	public $view_file_ext;
	public $ucfirst_classnames;
	public $default_helpers;
	public $environment;
	public $helper_file_ext;
	public $default_css_file;
	
public function __construct(Array $config_data)
{
	foreach($config_data as $name => $config_value)
	{
		//if property exists, then set it.
		if(property_exists($this,$name)) $this->$name  = $config_value;
	}
}
	
}

/* End of script config.class.php
 * 
 */