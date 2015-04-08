<?php 
//define main application folder
$app_folder = 'app/';
//define base path
$basepath = '';
define('BASEPATH',$basepath);
//include configuration settings from config file. $config = array()
include_once $app_folder.'config/config.php';
//add app folder and basepath settings to $config array from included config file
$config['app_folder'] = $app_folder;
$config['basepath'] = $basepath;
//global objects will be saved in this array
$gobjects = array();
//load config object and pass config settings to it
include_once $app_folder.'core/config.class.php';
$gobjects['config'] = new Config($config);
//include and load our central connecting class: Lobby
include_once $app_folder.'core/lobby.class.php';
new Lobby();
//loaded central Lobby class above loads our Loader class by default 
//and includes it into the $gobjects array
$loader = $gobjects['load'];
//load Our Initializer object
$loader->core('init','init');
//Now, Let the games begin
$gobjects['init']->begin();

?>