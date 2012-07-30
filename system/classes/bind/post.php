<?php

class Bind_POST extends Bind {
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
		return isset($post[$this->name]) ? $post[$this->name] : null;
	}
	
	public function store($value, $uri, $get, $post, $any) {
		if (isset($value)) $post[$this->name] = $value;
		return array($uri, $get, $post, $any);
	}
}