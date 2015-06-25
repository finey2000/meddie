<?php 
if(!defined('BASEPATH')) exit('Indirect Script Access not allowed');

/**
 * This class manages and displays errors
 * @author Admins
 *
 */
class Error extends Lobby
{
	public $err_msg;
	public $err_no;
	public $err_file;
	public $err_line;
	public $err_context;
	private $err_data;
	private $error_messages = '';
	
	public function __construct()
	{
		set_exception_handler(array($this,'handle_exception'));
		set_error_handler(array($this,'handle_error'));
		if($this->config->environment == 'production'){
                    ini_set('display_errors',0);
                    ini_set('log_errors',1);
                 //   ini_set('error_log','error.log');
                }

	}
	
	public function show_404(Exception $error_obj = null)
	{
		if(is_null($error_obj)) $error_msg = '';
		else $error_msg = $error_obj->getMessage();		
		$this->view->display_page('error',array('error_msg'=>$error_msg));
	}
	
	public function handle_exception(Exception $e)
	{
		if($this->config->environment == 'development') $this->display_exception($e);
		elseif($this->config->environment == 'production') $this->log_exception($e);		
	}
	
	private function extract_error_data()
	{
		if(is_null($this->error_data)) return;
		$error_data = $this->err_data;
		$this->err_no = $error_data[0];
		$this->err_msg = $error_data[1];
		$this->err_file = $error_data[2];
		$this->err_line = $error_data[3];
		$this->err_context = $error_data[4];
	}
	
	public function handle_error()
	{
		$this->err_data = func_get_args();
		$this->extract_error_data();
		if($this->config->environment == 'development') $this->display_error();
		elseif($this->config->environment == 'production') $this->log_error();
	}
	
        /**
         * receives any data and saves it as a string into data log file
         * @param type $data
         */
        public function log_data($data){
            if(!is_string($data) && !is_numeric($data)){
                ob_start();
                var_dump($data);
                $data = ob_get_contents();
                ob_end_clean();
            }           
           file_put_contents('data_log.log',$data." \n",FILE_APPEND);
            return true;
        }
        
	/**
	 * use this method when in development environment
	 */
	private function display_error()
	{
		//add to error messages
		$this->error_messages = $this->error_messages."\n".$this->err_msg.' FILE: '.$this->err_file.' LINE: '.$this->err_line;
	}
	
	/**
	 * use this method when in production mode
	 */
	private function log_error()
	{
		$this->error_messages = $this->error_messages."\n".$this->err_msg.' FILE: '.$this->err_file.' LINE: '.$this->err_line;
              //  file_put_contents('error.log',$this->error_messages." \n",FILE_APPEND);
	}
	
	private function display_exception(Exception $e)
	{
		$error_msg = $e->getMessage();
		echo 'exception';
		$error_string = $this->view->display_page('error',array('error_msg'=>$error_msg),true);
		$this->view->add_footer($error_string);
	}
	
	private function log_exception(Exception $e)
	{
		
	}
	
	public function get_errors()
	{
		if($this->error_messages){
			return $this->error_messages;
		}
     return false; //else
	}
	
}

/*
 * end of script error.classphp
 */