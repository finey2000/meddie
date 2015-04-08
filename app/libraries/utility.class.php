<?php
class Utility extends Lobby

//////////////////////////////////////////////////////////
//
// This class is a library of generally useful methods  //
//
////////////////////////////////////////////////////////

{

 private $site_url;
 public $form_errors = array();

public function __construct(){
	$this->site_url = $this->config->site_url;
	$this->set_site();
	$this->define_form_error();
        //define array aolumn
        $this->define_array_column();
}
 public function show_site($uri){
 	return "$this->site_url/$uri";
 }
 
 public function define_array_column()
 {
     if(function_exists('array_column')) return;
     $array_helper_file = '../../includes/general/array_helper.php';
     if(!is_file($array_helper_file)) return;
include_once $array_helper_file;
 }
 
public function Sanitize_Data($input)
{
	if(is_array($input)){
		foreach($input as $key => $value){
			$clean_data[$key] = htmlspecialchars($value);
		}
		return $clean_data;
	}
	if(is_string($input)) return htmlspecialchars($input);

}

private function set_site()
{

	if(!function_exists('show_site'))
	{
		function show_site($uri = '')
		{
			global $gobjects;
			$site_url = $gobjects['config']->site_url;
			return $site_url."/$uri";
		}
	}
}

public function set_form_error($error_message){

	$this->form_errors[] = $error_message;
	
}

private function define_form_error()
{
	if(!function_exists('form_error'))
	{
		function form_error()
		{
			$errors = $GLOBALS['gobjects']['utility']->form_errors;
			if(!$errors) return;
			echo "<ul class='notify'>";
			foreach($errors as $error)
			{
				echo "<li>$error </li>";
			}
			echo "</ul>";
		}
	}
}



/*
public	function Show_Date($date) 
	{
		$stf = 0;
		$cur_time = time();
		if ($date >= $cur_time){
		return date('F/j/Y', $date);
		}
		$diff = $cur_time - $date;
		$phrase = array('second','minute','hour','day','week','month','year','decade');
		$length = array(1,60,3600,86400,604800,2630880,31570560,315705600);		
		for($i =sizeof($length)-1; ($i >=0)&&(($no =  $diff/$length[$i])<=1); $i--);		
		if($phrase[$i] != 'second' && $phrase[$i] !='minute' && $phrase[$i] != 'hour'){
		return date('F/j/Y', $date);
		}else{
		if($i < 0) $i=0; $_time = $cur_time  -($diff%$length[$i]);
		$no = floor($no); if($no <> 1) $phrase[$i] .='s';		
		$value=sprintf("%d %s ",$no,$phrase[$i]);
		return $value.' ago ';
		}
	}*/

public function Show_Date($date)
{

    if(empty($date)) {
        return "No date provided";
    }
   
    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths         = array("60","60","24","7","4.35","12","10");
   
    $now             = time();
    $unix_date         = $date;
   
       // check validity of date
    if(empty($unix_date)) {   
        return "Bad date";
    }

    // is it future date or past date
    if($now > $unix_date) {   
        $difference     = $now - $unix_date;
        $tense         = "ago";
       
    } else {
        $difference     = $unix_date - $now;
        $tense         = "from now";
    }
   
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }
   
    $difference = round($difference);
	
      if($periods[$j] != 'second' && $periods[$j] !='minute' && $periods[$j] != 'hour'){
	return date('F/j/Y', $date);
		}
		
    if($difference != 1) {
        $periods[$j].= "s";
    }

    return "$difference $periods[$j] $tense";
}

public function Show_Full_Date($date)
{
return date('F/j/Y', $date);
}
public function Validate_Email($email)
{
if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
return true;
}
return false;
}

public function Validate_Phone($phone)
{
if (!is_numeric($phone) || strlen($phone) < 10){
return false;
}else{ return true;}
}

public function Validate_Username($username)
{
//returns true if username contains alpha numeric characters only.
if(ctype_alnum($username)){
return true;}

return false;
}

public function Add_Days_To_Today($days){
$date = time();
$date = strtotime("+".$days." days", $date);
return  $date;
}


public function Add_Hours_To_Date($hours,$date)
{
$new_date = strtotime ("+".$hours." hours", $date);
return $new_date;
}


public function Validate_Form($form,$excludes = FALSE)
{
//this function returns an error message if it finds empty fields in a form apart from excludes

foreach($form as $field => $value){

if($excludes !== false){
foreach($excludes as $x_field){
if($x_field == $field){break;}
}
if($x_field == $field){continue;}
}

//fields with numeric value are not considered to be empty
if(empty($value) && (intval($value))){
//found an empty field
$error = "Your form fields were incomplete, please fill and resubmit the form again";
return $error;
}
}
return true;
}

public function Validate_Uploaded_Photo($photo)
{
if($photo['name'] != "") {
$type = substr($photo['name'], strrpos($photo['name'], '.') + 1);
if($type != "GIF" && $type != "JPG" && $type != "jpeg" && $type != "jpg" && $type != "gif") {
return false;}
if($photo['size'] == 0 || $photo['size'] > 1024000)
			{
			return false;}
return true;			
}else{
return false;
}
}

public function Image_Uploader($picture,$image_dir,$new_name,$max_width,$max_height)
{
$type = substr($picture['name'], strrpos($picture['name'], '.') + 1);
		// INITIALISE VARIABLE WITH CURRENT TIME
		$time = time();
		// SET VARIABLE WITH NAME OF FILE BY GRABBING EVERYTHING BEFORE THE DOT (.)
$name = substr($picture['name'], 0, strrpos($picture['name'], '.'));		
$fullsizeName	= $new_name.'.'.$type;
		// CREATE A PHP IMAGE OBJECT FROM THE UPLOADED FILE BASED ON IMAGE TYPE
		if($type == "gif" || $type == "GIF"){
			$imgObj = imagecreatefromgif($picture['tmp_name']);}
		else{
			$imgObj = imagecreatefromjpeg($picture['tmp_name']);}
		// GET THE WIDTH AND HEIGHT OF THE UPLOADED FILE
		$width = imageSX($imgObj);
		$height = imageSY($imgObj);
			// PROPORTIONAlLY RESIZE THE IMAGE IF WIDTH GREATER THAN MAX WIDTH
		if($width > $height){
		if($width > $max_width) {
		 $height = $height * ($max_width / $width);
		 $width = $max_width;	
		}
		}else{
		if($height > $max_height) {
		 $width = $width * ($max_height / $height);
		 $height = $max_height;	
		}
		}
		
		
		// CREATE THE NEW IMAGE OBJECTS
		$newImage = imagecreatetruecolor($width, $height);
		// COPY THE OLD IMAGE OBJECT ATTRIBUTES TO THE NEW ONES
		imagecopyresampled($newImage, $imgObj, 0, 0, 0, 0, $width, $height, imageSX($imgObj), imageSY($imgObj));		
		// MOVE IMAGES TO RELEVANT DESTINATION BASED ON TYPE
		if($type == "GIF") {
			imagegif($newImage, $image_dir.$fullsizeName);
		} else {
			imagejpeg($newImage, $image_dir.$fullsizeName);
		}                      
		imagedestroy($imgObj);
		imagedestroy($newImage);
		return $fullsizeName;
}


public function Barcode_Maker($text)
{

// Including all required classes
require_once(INCLUDES_FOLDER.'services/barcode/class/BCGFontFile.php');
require_once(INCLUDES_FOLDER.'services/barcode/class/BCGColor.php');
require_once(INCLUDES_FOLDER.'services/barcode/class/BCGDrawing.php');

// Including the barcode technology
require_once(INCLUDES_FOLDER.'services/barcode/class/BCGcode39.barcode.php');

// Loading Font
//$font = new BCGFontFile('./font/Arial.ttf', 18);

// Don't forget to sanitize user inputs
//$text = isset($_GET['text']) ? $_GET['text'] : 'HELLO';

// The arguments are R, G, B for color.
$color_black = new BCGColor(0, 0, 0);
$color_white = new BCGColor(255, 255, 255);

$drawException = null;
try {
	$code = new BCGcode39();
	$code->setScale(2); // Resolution
	$code->setThickness(30); // Thickness
	$code->setForegroundColor($color_black); // Color of bars
	$code->setBackgroundColor($color_white); // Color of spaces
	$code->setFont(0); // Font (or 0)
	$code->parse($text); // Text
} catch(Exception $exception) {
	$drawException = $exception;
}

/* Here is the list of the arguments
1 - Filename (empty : display on screen)
2 - Background color */
$drawing = new BCGDrawing('', $color_white);
if($drawException) {
	$drawing->drawException($drawException);
} else {
	$drawing->setBarcode($code);
	$drawing->draw();
}

// Header that says it is an image (remove it if you save the barcode to a file)
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="barcode.png"');

// Draw (or save) the image into PNG format.
$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
}


public function Is_Mobile()
{
//this function validates a user's browser to see its mobile

if(isset($_SESSION['is_mobile'])){
return $_SESSION['is_mobile'];
}

if($this->Real_Mobile()){
$this->Set_Mobile();
return true;}else{
return false;}

}

public function Set_Desktop()
{
$_SESSION['is_mobile'] = false;
}

public function Set_Mobile()
{
$_SESSION['is_mobile'] = true;
}

public function Real_Mobile()
{
$real_mobile = $this->mobile_device_detect(true,false,true,true,true,true,true);
return $real_mobile;
}

private function mobile_device_detect($iphone=true,$ipad=true,$android=true,$opera=true,$blackberry=true,$palm=true,$windows=true){
$mobileredirect=false;
$desktopredirect=false;
  $mobile_browser   = false; // set mobile browser as false till we can prove otherwise
  $user_agent       = $_SERVER['HTTP_USER_AGENT']; // get the user agent value - this should be cleaned to ensure no nefarious input gets executed
  $accept           = $_SERVER['HTTP_ACCEPT']; // get the content accept value - this should be cleaned to ensure no nefarious input gets executed

  switch(true){ // using a switch against the following statements which could return true is more efficient than the previous method of using if statements

    case (preg_match('/ipad/i',$user_agent)); // we find the word ipad in the user agent
      $mobile_browser = $ipad; // mobile browser is either true or false depending on the setting of ipad when calling the function
      $status = 'Apple iPad';
      if(substr($ipad,0,4)=='http'){ // does the value of ipad resemble a url
        $mobileredirect = $ipad; // set the mobile redirect url to the url value stored in the ipad value
      } // ends the if for ipad being a url
    break; // break out and skip the rest if we've had a match on the ipad // this goes before the iphone to catch it else it would return on the iphone instead

    case (preg_match('/ipod/i',$user_agent)||preg_match('/iphone/i',$user_agent)); // we find the words iphone or ipod in the user agent
      $mobile_browser = $iphone; // mobile browser is either true or false depending on the setting of iphone when calling the function
      $status = 'Apple';
      if(substr($iphone,0,4)=='http'){ // does the value of iphone resemble a url
        $mobileredirect = $iphone; // set the mobile redirect url to the url value stored in the iphone value
      } // ends the if for iphone being a url
    break; // break out and skip the rest if we've had a match on the iphone or ipod

    case (preg_match('/android/i',$user_agent));  // we find android in the user agent
      $mobile_browser = $android; // mobile browser is either true or false depending on the setting of android when calling the function
      $status = 'Android';
      if(substr($android,0,4)=='http'){ // does the value of android resemble a url
        $mobileredirect = $android; // set the mobile redirect url to the url value stored in the android value
      } // ends the if for android being a url
    break; // break out and skip the rest if we've had a match on android

    case (preg_match('/opera mini/i',$user_agent)); // we find opera mini in the user agent
      $mobile_browser = $opera; // mobile browser is either true or false depending on the setting of opera when calling the function
      $status = 'Opera';
      if(substr($opera,0,4)=='http'){ // does the value of opera resemble a rul
        $mobileredirect = $opera; // set the mobile redirect url to the url value stored in the opera value
      } // ends the if for opera being a url 
    break; // break out and skip the rest if we've had a match on opera

    case (preg_match('/blackberry/i',$user_agent)); // we find blackberry in the user agent
      $mobile_browser = $blackberry; // mobile browser is either true or false depending on the setting of blackberry when calling the function
      $status = 'Blackberry';
      if(substr($blackberry,0,4)=='http'){ // does the value of blackberry resemble a rul
        $mobileredirect = $blackberry; // set the mobile redirect url to the url value stored in the blackberry value
      } // ends the if for blackberry being a url 
    break; // break out and skip the rest if we've had a match on blackberry

    case (preg_match('/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i',$user_agent)); // we find palm os in the user agent - the i at the end makes it case insensitive
      $mobile_browser = $palm; // mobile browser is either true or false depending on the setting of palm when calling the function
      $status = 'Palm';
      if(substr($palm,0,4)=='http'){ // does the value of palm resemble a rul
        $mobileredirect = $palm; // set the mobile redirect url to the url value stored in the palm value
      } // ends the if for palm being a url 
    break; // break out and skip the rest if we've had a match on palm os

    case (preg_match('/(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)/i',$user_agent)); // we find windows mobile in the user agent - the i at the end makes it case insensitive
      $mobile_browser = $windows; // mobile browser is either true or false depending on the setting of windows when calling the function
      $status = 'Windows Smartphone';
      if(substr($windows,0,4)=='http'){ // does the value of windows resemble a rul
        $mobileredirect = $windows; // set the mobile redirect url to the url value stored in the windows value
      } // ends the if for windows being a url 
    break; // break out and skip the rest if we've had a match on windows

    case (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i',$user_agent)); // check if any of the values listed create a match on the user agent - these are some of the most common terms used in agents to identify them as being mobile devices - the i at the end makes it case insensitive
      $mobile_browser = true; // set mobile browser to true
      $status = 'Mobile matched on piped preg_match';
    break; // break out and skip the rest if we've preg_match on the user agent returned true 

    case ((strpos($accept,'text/vnd.wap.wml')>0)||(strpos($accept,'application/vnd.wap.xhtml+xml')>0)); // is the device showing signs of support for text/vnd.wap.wml or application/vnd.wap.xhtml+xml
      $mobile_browser = true; // set mobile browser to true
      $status = 'Mobile matched on content accept header';
    break; // break out and skip the rest if we've had a match on the content accept headers

    case (isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE'])); // is the device giving us a HTTP_X_WAP_PROFILE or HTTP_PROFILE header - only mobile devices would do this
      $mobile_browser = true; // set mobile browser to true
      $status = 'Mobile matched on profile headers being set';
    break; // break out and skip the final step if we've had a return true on the mobile specfic headers

    case (in_array(strtolower(substr($user_agent,0,4)),array('1207'=>'1207','3gso'=>'3gso','4thp'=>'4thp','501i'=>'501i','502i'=>'502i','503i'=>'503i','504i'=>'504i','505i'=>'505i','506i'=>'506i','6310'=>'6310','6590'=>'6590','770s'=>'770s','802s'=>'802s','a wa'=>'a wa','acer'=>'acer','acs-'=>'acs-','airn'=>'airn','alav'=>'alav','asus'=>'asus','attw'=>'attw','au-m'=>'au-m','aur '=>'aur ','aus '=>'aus ','abac'=>'abac','acoo'=>'acoo','aiko'=>'aiko','alco'=>'alco','alca'=>'alca','amoi'=>'amoi','anex'=>'anex','anny'=>'anny','anyw'=>'anyw','aptu'=>'aptu','arch'=>'arch','argo'=>'argo','bell'=>'bell','bird'=>'bird','bw-n'=>'bw-n','bw-u'=>'bw-u','beck'=>'beck','benq'=>'benq','bilb'=>'bilb','blac'=>'blac','c55/'=>'c55/','cdm-'=>'cdm-','chtm'=>'chtm','capi'=>'capi','cond'=>'cond','craw'=>'craw','dall'=>'dall','dbte'=>'dbte','dc-s'=>'dc-s','dica'=>'dica','ds-d'=>'ds-d','ds12'=>'ds12','dait'=>'dait','devi'=>'devi','dmob'=>'dmob','doco'=>'doco','dopo'=>'dopo','el49'=>'el49','erk0'=>'erk0','esl8'=>'esl8','ez40'=>'ez40','ez60'=>'ez60','ez70'=>'ez70','ezos'=>'ezos','ezze'=>'ezze','elai'=>'elai','emul'=>'emul','eric'=>'eric','ezwa'=>'ezwa','fake'=>'fake','fly-'=>'fly-','fly_'=>'fly_','g-mo'=>'g-mo','g1 u'=>'g1 u','g560'=>'g560','gf-5'=>'gf-5','grun'=>'grun','gene'=>'gene','go.w'=>'go.w','good'=>'good','grad'=>'grad','hcit'=>'hcit','hd-m'=>'hd-m','hd-p'=>'hd-p','hd-t'=>'hd-t','hei-'=>'hei-','hp i'=>'hp i','hpip'=>'hpip','hs-c'=>'hs-c','htc '=>'htc ','htc-'=>'htc-','htca'=>'htca','htcg'=>'htcg','htcp'=>'htcp','htcs'=>'htcs','htct'=>'htct','htc_'=>'htc_','haie'=>'haie','hita'=>'hita','huaw'=>'huaw','hutc'=>'hutc','i-20'=>'i-20','i-go'=>'i-go','i-ma'=>'i-ma','i230'=>'i230','iac'=>'iac','iac-'=>'iac-','iac/'=>'iac/','ig01'=>'ig01','im1k'=>'im1k','inno'=>'inno','iris'=>'iris','jata'=>'jata','java'=>'java','kddi'=>'kddi','kgt'=>'kgt','kgt/'=>'kgt/','kpt '=>'kpt ','kwc-'=>'kwc-','klon'=>'klon','lexi'=>'lexi','lg g'=>'lg g','lg-a'=>'lg-a','lg-b'=>'lg-b','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-f'=>'lg-f','lg-g'=>'lg-g','lg-k'=>'lg-k','lg-l'=>'lg-l','lg-m'=>'lg-m','lg-o'=>'lg-o','lg-p'=>'lg-p','lg-s'=>'lg-s','lg-t'=>'lg-t','lg-u'=>'lg-u','lg-w'=>'lg-w','lg/k'=>'lg/k','lg/l'=>'lg/l','lg/u'=>'lg/u','lg50'=>'lg50','lg54'=>'lg54','lge-'=>'lge-','lge/'=>'lge/','lynx'=>'lynx','leno'=>'leno','m1-w'=>'m1-w','m3ga'=>'m3ga','m50/'=>'m50/','maui'=>'maui','mc01'=>'mc01','mc21'=>'mc21','mcca'=>'mcca','medi'=>'medi','meri'=>'meri','mio8'=>'mio8','mioa'=>'mioa','mo01'=>'mo01','mo02'=>'mo02','mode'=>'mode','modo'=>'modo','mot '=>'mot ','mot-'=>'mot-','mt50'=>'mt50','mtp1'=>'mtp1','mtv '=>'mtv ','mate'=>'mate','maxo'=>'maxo','merc'=>'merc','mits'=>'mits','mobi'=>'mobi','motv'=>'motv','mozz'=>'mozz','n100'=>'n100','n101'=>'n101','n102'=>'n102','n202'=>'n202','n203'=>'n203','n300'=>'n300','n302'=>'n302','n500'=>'n500','n502'=>'n502','n505'=>'n505','n700'=>'n700','n701'=>'n701','n710'=>'n710','nec-'=>'nec-','nem-'=>'nem-','newg'=>'newg','neon'=>'neon','netf'=>'netf','noki'=>'noki','nzph'=>'nzph','o2 x'=>'o2 x','o2-x'=>'o2-x','opwv'=>'opwv','owg1'=>'owg1','opti'=>'opti','oran'=>'oran','p800'=>'p800','pand'=>'pand','pg-1'=>'pg-1','pg-2'=>'pg-2','pg-3'=>'pg-3','pg-6'=>'pg-6','pg-8'=>'pg-8','pg-c'=>'pg-c','pg13'=>'pg13','phil'=>'phil','pn-2'=>'pn-2','pt-g'=>'pt-g','palm'=>'palm','pana'=>'pana','pire'=>'pire','pock'=>'pock','pose'=>'pose','psio'=>'psio','qa-a'=>'qa-a','qc-2'=>'qc-2','qc-3'=>'qc-3','qc-5'=>'qc-5','qc-7'=>'qc-7','qc07'=>'qc07','qc12'=>'qc12','qc21'=>'qc21','qc32'=>'qc32','qc60'=>'qc60','qci-'=>'qci-','qwap'=>'qwap','qtek'=>'qtek','r380'=>'r380','r600'=>'r600','raks'=>'raks','rim9'=>'rim9','rove'=>'rove','s55/'=>'s55/','sage'=>'sage','sams'=>'sams','sc01'=>'sc01','sch-'=>'sch-','scp-'=>'scp-','sdk/'=>'sdk/','se47'=>'se47','sec-'=>'sec-','sec0'=>'sec0','sec1'=>'sec1','semc'=>'semc','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','sk-0'=>'sk-0','sl45'=>'sl45','slid'=>'slid','smb3'=>'smb3','smt5'=>'smt5','sp01'=>'sp01','sph-'=>'sph-','spv '=>'spv ','spv-'=>'spv-','sy01'=>'sy01','samm'=>'samm','sany'=>'sany','sava'=>'sava','scoo'=>'scoo','send'=>'send','siem'=>'siem','smar'=>'smar','smit'=>'smit','soft'=>'soft','sony'=>'sony','t-mo'=>'t-mo','t218'=>'t218','t250'=>'t250','t600'=>'t600','t610'=>'t610','t618'=>'t618','tcl-'=>'tcl-','tdg-'=>'tdg-','telm'=>'telm','tim-'=>'tim-','ts70'=>'ts70','tsm-'=>'tsm-','tsm3'=>'tsm3','tsm5'=>'tsm5','tx-9'=>'tx-9','tagt'=>'tagt','talk'=>'talk','teli'=>'teli','topl'=>'topl','hiba'=>'hiba','up.b'=>'up.b','upg1'=>'upg1','utst'=>'utst','v400'=>'v400','v750'=>'v750','veri'=>'veri','vk-v'=>'vk-v','vk40'=>'vk40','vk50'=>'vk50','vk52'=>'vk52','vk53'=>'vk53','vm40'=>'vm40','vx98'=>'vx98','virg'=>'virg','vite'=>'vite','voda'=>'voda','vulc'=>'vulc','w3c '=>'w3c ','w3c-'=>'w3c-','wapj'=>'wapj','wapp'=>'wapp','wapu'=>'wapu','wapm'=>'wapm','wig '=>'wig ','wapi'=>'wapi','wapr'=>'wapr','wapv'=>'wapv','wapy'=>'wapy','wapa'=>'wapa','waps'=>'waps','wapt'=>'wapt','winc'=>'winc','winw'=>'winw','wonu'=>'wonu','x700'=>'x700','xda2'=>'xda2','xdag'=>'xdag','yas-'=>'yas-','your'=>'your','zte-'=>'zte-','zeto'=>'zeto','acs-'=>'acs-','alav'=>'alav','alca'=>'alca','amoi'=>'amoi','aste'=>'aste','audi'=>'audi','avan'=>'avan','benq'=>'benq','bird'=>'bird','blac'=>'blac','blaz'=>'blaz','brew'=>'brew','brvw'=>'brvw','bumb'=>'bumb','ccwa'=>'ccwa','cell'=>'cell','cldc'=>'cldc','cmd-'=>'cmd-','dang'=>'dang','doco'=>'doco','eml2'=>'eml2','eric'=>'eric','fetc'=>'fetc','hipt'=>'hipt','http'=>'http','ibro'=>'ibro','idea'=>'idea','ikom'=>'ikom','inno'=>'inno','ipaq'=>'ipaq','jbro'=>'jbro','jemu'=>'jemu','java'=>'java','jigs'=>'jigs','kddi'=>'kddi','keji'=>'keji','kyoc'=>'kyoc','kyok'=>'kyok','leno'=>'leno','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-g'=>'lg-g','lge-'=>'lge-','libw'=>'libw','m-cr'=>'m-cr','maui'=>'maui','maxo'=>'maxo','midp'=>'midp','mits'=>'mits','mmef'=>'mmef','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','mwbp'=>'mwbp','mywa'=>'mywa','nec-'=>'nec-','newt'=>'newt','nok6'=>'nok6','noki'=>'noki','o2im'=>'o2im','opwv'=>'opwv','palm'=>'palm','pana'=>'pana','pant'=>'pant','pdxg'=>'pdxg','phil'=>'phil','play'=>'play','pluc'=>'pluc','port'=>'port','prox'=>'prox','qtek'=>'qtek','qwap'=>'qwap','rozo'=>'rozo','sage'=>'sage','sama'=>'sama','sams'=>'sams','sany'=>'sany','sch-'=>'sch-','sec-'=>'sec-','send'=>'send','seri'=>'seri','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','siem'=>'siem','smal'=>'smal','smar'=>'smar','sony'=>'sony','sph-'=>'sph-','symb'=>'symb','t-mo'=>'t-mo','teli'=>'teli','tim-'=>'tim-','tosh'=>'tosh','treo'=>'treo','tsm-'=>'tsm-','upg1'=>'upg1','upsi'=>'upsi','vk-v'=>'vk-v','voda'=>'voda','vx52'=>'vx52','vx53'=>'vx53','vx60'=>'vx60','vx61'=>'vx61','vx70'=>'vx70','vx80'=>'vx80','vx81'=>'vx81','vx83'=>'vx83','vx85'=>'vx85','wap-'=>'wap-','wapa'=>'wapa','wapi'=>'wapi','wapp'=>'wapp','wapr'=>'wapr','webc'=>'webc','whit'=>'whit','winw'=>'winw','wmlb'=>'wmlb','xda-'=>'xda-',))); // check against a list of trimmed user agents to see if we find a match
      $mobile_browser = true; // set mobile browser to true
      $status = 'Mobile matched on in_array';
    break; // break even though it's the last statement in the switch so there's nothing to break away from but it seems better to include it than exclude it

    default;
      $mobile_browser = false; // set mobile browser to false
      $status = 'Desktop / full capability browser';
    break; // break even though it's the last statement in the switch so there's nothing to break away from but it seems better to include it than exclude it

  } // ends the switch 


  if($mobile_browser==true){
return true;
  }else{ 
		return false;
	}

} // ends function mobile_device_detect


public function Set_Login_Redirect($page,$queries)
{
if($queries !== false){
$qs = "?";
foreach($queries as $query => $value){
$qs .= "$query=$value&";

}
}else{
$qs = "";
}

$_SESSION['LoginRedirect'] = $page.$qs;
}

public function Get_Login_Redirect()
{
if(isset($_SESSION['LoginRedirect'])){
return $_SESSION['LoginRedirect'];}
return false;
}

public function Cancel_Login_Redirect()
{
if(isset($_SESSION['LoginRedirect'])){
unset($_SESSION['LoginRedirect']);}
return true;}


public function Set_Logout_Redirect($page)
{
$_SESSION['LogoutRedirect'] = $page;
}

public function Get_Logout_Redirect()
{
if(isset($_SESSION['LogoutRedirect'])){
return $_SESSION['LogoutRedirect'];}
return false;
}

public function Cancel_Logout_Redirect()
{
if(isset($_SESSION['LogoutRedirect'])){
unset($_SESSION['LogoutRedirect']);}
return true;}

public function Generate_Hash($password) {
if (defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
        $salt = '$2y$11$' . substr(md5(uniqid(rand(), true)), 0, 22);
        return crypt($password, $salt);
    }
}


public function Verify_Password($password, $hashedPassword) {
    $awe = crypt($password, $hashedPassword);
	if($awe == $hashedPassword){
	return true;}else{
	return false;}
	
}

/**
 * an alias of Verify_Password()
 * @param unknown $password
 * @param unknown $hashedPassword
 */
public function password_matches($password,$hashedPassword)
{
	return $this->Verify_Password($password,$hashedPassword);
}

public function Generate_Random_Code($digits)
{
$code = substr(number_format(time() * rand(),0,'',''),0,$digits);
return $code;
}

public function Generate_Random_Code2($digits)
{
$uniq_id = uniqid(rand(), true);
$code = substr($uniq_id,0,$digits);
return $code;
}

public function Current_Page_Url() {
$isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
$port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
$port = ($port) ? ':'.$_SERVER["SERVER_PORT"] : '';
$url = ($isHTTPS ? 'https://' : 'http://').$_SERVER["SERVER_NAME"].$port.$_SERVER["REQUEST_URI"];
return $url;
}

public function Paginator($pages,$link,$current_page)
{

if(is_float($pages)){
$pages = $this->Round_Up_Float($pages);
}
$template = $this->template->Get_Template('default/emp/settings/paginator.tpl.php');
$page_block = $this->template->Get_Block($template,'page_block');
$page_comp = "";

for($i=1;$i<=$pages;$i++){
if($i == $current_page){
$url = "#";
}else{
$url = $link."page=$i";}
$tag['page_url'] = $url;
$tag['page_number'] = $i;
$page_comp .= $this->template->Replace_Tags($page_block,$tag);
}
$content = $this->template->Replace_Block($template,$page_block,$page_comp);
return $content;
}

public function Round_Up_Float($float)
{
//rounds up a floating value to a whole number
$nos = explode(".",$float);
$decimal = $nos[1];
$decimal = substr($decimal,0,1); //removes infinite digits
$decimal = $decimal/10;
$decimal = substr($decimal,0,3); //removes infinite digits
$difference = 1-$decimal;
$value = $nos[0]+$difference+$decimal;
return $value;
}

}?>
