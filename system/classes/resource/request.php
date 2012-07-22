<?php

/**
 * Resource whose content is what a controller returns for a given set of parameters.
 *
 */
class Resource_Request extends Resource {
	/**
	 * @var array Parameters.
	 */
	protected $params;
	
	/**
	 * @var Controller
	 */
	protected $controller;
	
	/**
	 * Constructor.
	 * 
	 * @param Controller $controller
	 * @param array $params
	 */
	public function __construct(Controller $controller, array $params) {
		$this->controller = $controller;
		$this->params = $params;
	}
	
	/* (non-PHPdoc)
	 * @see Resource::content()
	 */
	public function content() {
		return call_user_func_array(array($this->controller, 'raw'), $this->params);
	}
}