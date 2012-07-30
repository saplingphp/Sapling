<?php

class Bind_URI extends Bind {
	/**
	 * @var string Key in the super-global array this object binds to.
	 */
	protected $name;
	
	/**
	 * Constructor.
	 * 
	 * @param string $name
	 */
	protected function __construct($name) {
		$this->name = $name;
	}
	
	public function fetch($uri, $get, $post, $any) {
		return isset($uri[$this->name]) ? $uri[$this->name] : null;
	}
	
	public function store($value, $uri, $get, $post, $any) {
		if (isset($value)) $uri[$this->name] = $value;
		return array($uri, $get, $post, $any);
	}
}