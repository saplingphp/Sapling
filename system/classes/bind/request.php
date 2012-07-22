<?php

class Bind_REQUEST extends Bind {
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
	
	public function fetch($uri, $get, $post, $cookie, $request) {
		return isset($request[$this->name]) ? $request[$this->name] : null;
	}
	
	public function store($value, $uri, $get, $post, $cookie, $request) {
		$request[$this->name] = $value;
		return array($uri, $get, $post, $cookie, $request);
	}
}