<?php 
if(!defined('BASEPATH')) exit('Indirect Script Access not allowed');

/*
 * Please include your access credentials(credentials.php) file here to enable app connect to your database
 * I choose to gitignore my credentials so I can keep my database credentials private
 */
include('credentials.php');
$config = array();
$config['site_url'] = $site_url;
$config['db_host'] = $db_host;
$config['db_user'] = $db_user;
$config['db_pswd'] = $db_pswd;
$config['db_name'] = $db_name;
$config['assets_url'] = $config['site_url'].'/assets';
$config['default_libraries'] = array('dataman'=>'dataman','utility'=>'utility'); //array('class_name'=>'set_name')
$config['default_models'] = array('users_model'=>'users_model','quiz_model'=>'quiz_model','func_model'=>'func_model'); //array('class_name'=>'set_name')
$config['default_helpers'] = array(); //an array of helper files to include, these files contain helper function array('file_name')
$config['default_controller'] = array('quiz','index'); //array('class_name','method_name')
$config['default_method'] = 'index'; //this value is used if a controller is called and no method is specified
/*this is the name of the requested directory
that the server rewrites to the application, this value must be set in the htaccess file
EXAMPLE .htaccess file
<IfModule mod_rewrite.c>
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?dir=$1 [L,QSA]
</IfModule>
*/
$config['server_rewrite_dir_name'] = 'dir'; 
$config['class_file_ext'] = 'class.php'; //extension name of class file
$config['helper_file_ext'] = 'helper.php'; //extension name of helper file
$config['view_file_ext'] = 'tpl.php'; //extension name of view file
$config['ucfirst_classnames'] = true; //sets whether to ucfirst classnames
$config['environment'] = 'production'; //development or production
$config['default_css_file'] = $config['site_url'].'/assets/css/styles.css';


/*
 * End of script - config.php
 */