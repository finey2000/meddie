<?php
if(!defined('BASEPATH')) exit('Indirect Script Access not allowed');
/**
 * This is the central class that gives access to every global object in our application
 * @author Christian Ntong
 *
 */
class Lobby

{
	
public function __construct(){
	
	//if loader object is not set then set it
	
 	if(!isset($GLOBALS['gobjects']['load'])){
 		if(!isset($GLOBALS['gobjects']['lobby'])) $GLOBALS['gobjects']['lobby'] = $this;
 		$config = $GLOBALS['gobjects']['config'];
 		include_once $config->app_folder.'core/loader.class.php';
 		$GLOBALS['gobjects']['load'] = new Loader;
 	}
 	
 }

 public function __set($name,$value)
 {
 	$GLOBALS['gobjects'][$name] = $value;
 }
 
 public function __get($name)
 {
 	if(array_key_exists($name,$GLOBALS['gobjects'])) return $GLOBALS['gobjects'][$name];
 	return false;
 }
 
}
 
/*
 * End of script lobby.class.php
 */
