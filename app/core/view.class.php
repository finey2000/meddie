<?php 
/**
 * This class will manipulate and possible display our view files
 */
class View extends Lobby{
	
	private $view_footer = '';
	
	public function __construct()
	{
		ob_start();
	}
	
	public function add_footer($footer_string)
	{
		if(!empty($footer_string)){
			$this->view_footer = $this->view_footer."$footer_string\n";
		}
	}
	
	public function display_page($template, Array $fields = NULL,$return = FALSE)
	{
		$file = $this->config->app_folder."views/$template.".$this->config->view_file_ext;
		if(!file_exists($file)){
			trigger_error("Template: $file not found for page parsing on ".__FILE__.' and line: '.__LINE__);
			return false;
		}
		if(is_null($fields)) $fields = array();
	 $page = $this->parse_page($file, $fields);
     if($return) return $page;
     else echo $page;
	}
	
	private function parse_page($page,$fields)
	{
		//extract fields
		//get page into a string
		//remove php opening tags
		//add necessary code to return the parsed file
		//evaluate as php code
		ob_start();
		extract($fields);
		include $page;
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	
	/**
	 * uses the given page fragment to create a complete page
	 * by prepending and appending header and footer files from the template file
	 */
	public function create_page($page_body, $title='', Array $css_files = array(), Array $js_files = array(),$return = false)
	{
		if(!$css_files) {
                    $css_files = array($this->config->default_css_file);		
                }
		$header_contents = array('page_title'=>$title,'css_files'=>$css_files,'js_files'=>$js_files);
		$page_header = $this->display_page('page_header',$header_contents,true);
		$page_footer = $this->display_page('page_footer',array(),true);
		if($return) {
                    return $page_header.$page_body.$page_footer;
                }
		else {
                    echo $page_header.$page_body.$page_footer;
                }
	}
	
	public function __destruct()
	{
		$total_contents = ob_get_contents();
		ob_end_clean();		
		
		//add errors to footer
		$errors = $this->error->get_errors();
		if($errors) $total_contents = "$total_contents <h3> Application Errors:</h3> $errors";
		
		// add view footer to total output		
		if($this->view_footer){
			$total_contents = "$total_contents \n".$this->view_footer;
			//change new lines to br tags
			//$total_contents = nl2br($total_contents);
		}		
		echo $total_contents;
	}
	
}

/**
 * end of view class
 */