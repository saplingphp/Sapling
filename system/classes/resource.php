<?php

/**
 * A resource is something able to return some content as a string.
 *
 */
abstract class Resource {
	/**
	 * Returns the content of this resource.
	 * 
	 * @return string
	 */
	abstract public function content();
}