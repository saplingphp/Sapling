<?php

/**
 * Instances of this class represent a binding between a key in a super-global array and a resource arguement.
 * 
 */
abstract class Bind {
	
	/********************************************************************************************************/
	/*********************************************  STATIC  *************************************************/
	/********************************************************************************************************/
	
	/**
	 * Factory method for Bind_URI.
	 * 
	 * @param string $name
	 * @return Bind
	 */
	static public function URI($name) {
		return new Bind_URI($name);
	}
	
	/**
	 * Factory method for Bind_GET.
	 * 
	 * @param string $name
	 * @return Bind
	 */
	static public function GET($name) {
		return new Bind_GET($name);
	}
	
	/**
	 * Factory method for Bind_POST.
	 * 
	 * @param string $name
	 * @return Bind
	 */
	static public function POST($name) {
		return new Bind_POST($name);
	}

	/**
	 * Factory method for Bind_ANY.
	 * 
	 * @param string $name
	 * @return Bind
	 */
	static public function ANY($name) {
		return new Bind_ANY($name);
	}
	
	/********************************************************************************************************/
	/********************************************  INSTANCE  ************************************************/
	/********************************************************************************************************/
	
	/**
	 * Fetches the value from the super-global arrays.
	 * 
	 * @param array $uri
	 * @param array $get
	 * @param array $post
	 * @param array $any
	 * @return string
	 */
	abstract public function fetch($uri, $get, $post, $any);
	
	/**
	 * Stores the value into the super-global arrays.
	 * 
	 * @param string $value
	 * @param array $uri
	 * @param array $get
	 * @param array $post
	 * @param array $any
	 */
	abstract public function store($value, $uri, $get, $post, $any);
}