<?php

/**
 * A collection of controllers. Any function call on the collection is forwarded to every controller in the collection.
 *
 */
class ControllerCollection {
	/**
	 * @var array Item in this collection
	 */
	protected $controllers;
	
	/**
	 * Constructor.
	 * 
	 * @param array $controllers
	 */
	public function __construct(array $controllers) {
		$this->controllers = $controllers;
	}
	
	/**
	 * Forwards call to every controller in the collection.
	 * 
	 * @param string $name
	 * @param array $arguments
	 * @return ControllerCollection
	 */
	public function __call($name, $arguments) {
		foreach($this->controllers as $controller)
			call_user_func_array(array($controller, $name), $arguments);
		return $this;
	}
}