<?php 
if(!defined('BASEPATH')) exit('Indirect Script Access not allowed');
/**
 * this class initializes and begins the app
 */

class Init extends Lobby {
	
	/**
	 * begins the application
	 * loads default classes
	 * process http request
	 * and hands over control to the requested controller
	 */
	public function begin()
	{
		//load defaults
		$this->load->load_defaults();
								
		
		//grab default controller details
		$def_controller = $this->config->default_controller;
		if(!is_array($def_controller) or (count($def_controller) != 2)) exit('Please define default controller and methods'); //default controller must be array with two elements
        //load default controller here
        $default_class= $def_controller[0];
        $default_method = $def_controller[1];
        //get class and method requested by the page        
        list($class_name,$class_method,$arguments) = $this->get_url_action();
        //convert dashes to undersores in class and method names        
        if(strpos($class_name,'-')) $class_name = str_replace('-','_',$class_name);
       if(strpos($class_method,'-'))  $class_method = str_replace('-','_',$class_method);
        //if class name does not exist, use class name as method name of default controller
        //prepare and load controller class
        if($class_name != 'default' && $class_name != 'index') $load_class = strtolower($class_name);
        else $load_class = $default_class;
        //prepare loaded controller method for invocation
        if($class_method != 'default') $load_method = strtolower($class_method);
        else $load_method = $this->config->default_method;
        //if the requested class name does not exists, use requested class as a method of default class,
        if(! $this->load->controller_exists($load_class)) {
        	$load_method = $class_name;
        	$load_class = $default_class;
        }
        
        //try to load requested class and method , else show 404 page
        try{

        $this->load->controller($load_class,$load_class);
        

        //load method if it exists, else, throw error
        if(method_exists($this->$load_class,$load_method))  call_user_func_array(array($this->$load_class,$load_method), $arguments); 
        else throw new Exception("The Requested Page: '$load_method' could not be found");
        } catch (Exception $error){
        	$this->error->show_404($error);
        }
        
       
	}
		
	
	
	/**
	 * this function analyses the url request
	 * and returns an array of requested classes, methods and possibly arguments
	 * @return array
	 */
	private function get_url_action()
	{
            if (!defined('STDIN')) {
		$uri = $this->_detect_uri();
            }else{
                global $argv;
                if(isset($argv[1])) $uri = $argv[1];
                else $uri = '/';
            }
		if($uri == '/') return array('default','default',array());		            
            	$dirs = explode('/',$uri);
		$class_name = $dirs[0];
		//extract arguments
		if(count($dirs) > 2) $arguments = array_slice($dirs, 2);
        else $arguments = array();
		if(!isset($dirs[1])) return array($class_name,'default',$arguments);
		else return array($class_name,$dirs[1],$arguments);
	
	}
	
	/**
	 * Detects the URI
	 *
	 * This function will detect the URI automatically and fix the query string
	 * if necessary.
	 *@author codeignitor
	 * @access	private
	 * @return	string
	 */
	private function _detect_uri()
	{
		if ( ! isset($_SERVER['REQUEST_URI']) OR ! isset($_SERVER['SCRIPT_NAME']))
		{
			return '';
		}
	
		$uri = $_SERVER['REQUEST_URI'];
		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
		{
			$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		}
		elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
		{
			$uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}
	
	
		// This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
		// URI is found, and also fixes the QUERY_STRING server var and $_GET array.
		if (strncmp($uri, '?/', 2) === 0)
		{
			$uri = substr($uri, 2);
		}
		$parts = preg_split('#\?#i', $uri, 2);
		$uri = $parts[0];
		if (isset($parts[1]))
		{
			$_SERVER['QUERY_STRING'] = $parts[1];
			parse_str($_SERVER['QUERY_STRING'], $_GET);
		}
		else
		{
			$_SERVER['QUERY_STRING'] = '';
			$_GET = array();
		}
	
		if ($uri == '/' || empty($uri))
		{
			return '/';
		}
	
		$uri = parse_url($uri, PHP_URL_PATH);
	
		// Do some final cleaning of the URI and return it
		return str_replace(array('//', '../'), '/', trim($uri, '/'));
	}
	
}

/* 
 * End of script init.class.php
 */