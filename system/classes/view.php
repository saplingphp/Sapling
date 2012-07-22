<?php
/**
 * Template engine that uses PHP files as templates.
 *
 * Each instance of this class is a combination of a PHP script (the template) and an array
 * of key-value pairs. When the ->render() method is invoked, the key-value pairs are
 * turned into variables and the template is evaluated. The output of the script is returned
 * as a string.
 *
 * Template paths are relative to the root of the templates directory.
 *
 * Usage : $html = View::create('my/template')->set('myvar1', 'test')->set('myvar2', 'test')->render();
 *
 */
class View {
	
	/********************************************************************************************************/
	/*********************************************  STATIC  *************************************************/
	/********************************************************************************************************/	
	
	/**
	 * @var View View that is currently in use to render the requested page.
	 */
	static protected $page;
	
	/**
	 * When called with no parameters, returns the view that is currently in use to render the requested page.
	 * When called with one parameter, sets the view to be used to render the requested page.
	 * 
	 * @param View $page
	 * @return View
	 */
	static public function page($page = null) {
		if (func_num_args() > 0)
			static::$page = View::create($page);
		return static::$page;
	}
	
	/**
	 * Chainable factory method.
	 * 
	 * @return View
	 */
	static public function create($path) {
		return new static($path);
	}
	
	/********************************************************************************************************/
	/********************************************  INSTANCE  ************************************************/
	/********************************************************************************************************/		
	
	/**
	 * @var array Absolute path of the template file.
	 */
	protected $path;		
	
	/**
	 * @var array Template variables
	 */
	protected $vars = array();
	
	/**
	 * Constructor.
	 * 
	 * @param string $path Path of the template file relative to the root of the templates directory.
	 */
	protected function __construct($path) {
		$this->path = DIR_ROOT_VIEWS . "/" . $path . ".php";
	}
	 
	/**
	 * Sets value of a variable.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return View
	 */	
	public function set($name, $value) {
		$this->vars[$name] = $value;
		return $this;
	}
	
	/**
	 * Pushes the given value at the end of the given array.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return View
	 */	
	public function push($name, $value) {
		$this->vars[$name][] = $value;
		return $this;
	}
	
	/**
	 * Renders template.
	 * 
	 * @return string
	 */		
	public function render() {
		// Assign variables :
		extract($this->vars);
		
		// Render template : 
		ob_start();
		include($this->path);		
		return ob_get_clean();
	}
}