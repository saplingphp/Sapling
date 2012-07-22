<?php

/**
 * Class that defines autoloader functions to be registered with spl_autoload_register().
 */
class AutoLoader {
	
	/**
	 * Autoloads the given user class.
	 * 
	 * @param string $class
	 */
	static public function autoloadUserClass($class) {
		return static::autoloadClass(DIR_ROOT_USER_CLASSES, $class);
	}

	/**
	 * Autoloads the given system class.
	 * 
	 * @param string $class
	 */
	static public function autoloadSystemClass($class) {
		return static::autoloadClass(DIR_ROOT_SYSTEM_CLASSES, $class);
	}
	
	/**
	 * Main autoloader function. If $class is "A_B", then it attemps to load it by requiring
	 * the file located at $base_dir/a/b.php if it exists.
	 * 
	 * @param string $base_dir
	 * @param string $class
	 */
	static protected function autoloadClass($base_dir, $class) {
		$path = $base_dir . '/' . strtr(strtolower($class), array('_' => '/')) . '.php';
		if (file_exists($path))	require($path);
	}
	
	/**
	 * Registers autoloaders defined in this class.
	 */
	static public function register() {
		spl_autoload_register(__CLASS__ . "::autoloadUserClass");
		spl_autoload_register(__CLASS__ . "::autoloadSystemClass");
	}
}