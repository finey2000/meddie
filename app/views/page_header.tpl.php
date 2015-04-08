<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php if($css_files){
	//include css files
foreach($css_files as $css_file){
?>
<link rel="stylesheet" type="text/css" href="<?php echo $css_file?>"/>
<?php }}?>

<?php if($js_files){
	//include js files
foreach($js_files as $js_file){
?>
<script src="<?php echo $css_file?>"> </script>
<?php }}?>

<title><?php echo $page_title?></title>
</head>
<body>
    