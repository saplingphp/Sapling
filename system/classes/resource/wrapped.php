<?php

/**
 * A resource wrapped in a wrapper.
 *
 */
class Resource_Wrapped extends Resource {
	/**
	 * @var Wrapper
	 */
	protected $wrapper;
	
	/**
	 * @var Resource
	 */
	protected $resource;
	
	/**
	 * Constructor.
	 * 
	 * @param Wrapper $wrapper
	 * @param Resource $resource
	 */
	public function __construct(Wrapper $wrapper, Resource $resource) {
		$this->wrapper = $wrapper;
		$this->resource = $resource;
	}
	
	/**
	 * Returns the resource content wrapped in the associated wrapper.
	 */
	public function content() {
		return $this->wrapper->wrap($this->resource);
	}
}