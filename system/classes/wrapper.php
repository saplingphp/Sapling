<?php

/**
 * A wrapper is a piece of code that surrounds the execution of a resource. It gets the content
 * returned by the resource, perhaps transforms it in some way, and returns it.
 * 
 * A wrapper may also decide not to execute the resource at all, for example if some condition isn't met,
 * and throw an exception or return some other content.
 *
 */
abstract class Wrapper {
	
	/********************************************************************************************************/
	/*********************************************  STATIC  *************************************************/
	/********************************************************************************************************/
	
	/**
	 * Creates and returns a wrapper using the given function as implementation.
	 * 
	 * @param callable $func
	 */
	static public function create($func)  {
		return new Wrapper_Callable($func);
	}
	
	/********************************************************************************************************/
	/********************************************  INSTANCE  ************************************************/
	/********************************************************************************************************/
	
	/**
	 * Gets the content returned by the resource, perhaps transforms it in some way, and returns it.
	 * 
	 * @param Resource $resource
	 */
	abstract public function wrap(Resource $resource);
}