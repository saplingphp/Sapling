<?php

/*************************************************************************************/
/****************************** CONSTANT DEFINITIONS *********************************/
/*************************************************************************************/

	define('URI_ROOT',					dirname(dirname($_SERVER['SCRIPT_NAME'])));
	define('URI_ROOT_CSS',				URI_ROOT . '/media/style');
	define('URI_ROOT_JS',				URI_ROOT . '/media/js');
	define('DIR_ROOT',					dirname(__DIR__));
	define('DIR_ROOT_VIEWS',			DIR_ROOT . '/views');
	define('DIR_ROOT_CONTROLLERS',      DIR_ROOT . '/controllers');
	define('DIR_ROOT_SYSTEM_CLASSES', 	DIR_ROOT . '/system/classes');
	define('DIR_ROOT_USER_CLASSES', 	DIR_ROOT . '/classes');

/*************************************************************************************/
/****************************** REGISTER AUTOLOADERS *********************************/
/*************************************************************************************/
	
	require(DIR_ROOT_SYSTEM_CLASSES . '/autoloader.php');
	AutoLoader::register();

/*************************************************************************************/
/****************************** HTML ESCAPE SHORTCUT *********************************/
/*************************************************************************************/

	function e($string) {
		return htmlspecialchars($string);
	}
	
/*************************************************************************************/
/***************************** INCLUDE BOOTSTRAP FILE ********************************/
/*************************************************************************************/
	
	require(DIR_ROOT . '/bootstrap.php');
	
	
	
	
	
	
