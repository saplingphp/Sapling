<?php

/**
 * Wrapper that uses a callable as wrapping function.
 *
 */
class Wrapper_Callable extends Wrapper {
	/**
	 * @var callable
	 */
	protected $func;
	
	/**
	 * Closure.
	 * 
	 * @param callable $callable
	 */
	public function __construct($func) {
		$this->func = $func;
	}

	/* (non-PHPdoc)
	 * @see system/classes/Wrapper::wrap()
	 */
	public function wrap(Resource $resource) {
		$function = $this->func;
		return $function($resource);
	}
}