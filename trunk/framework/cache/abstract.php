<?php
/**
 *
 * cache
 *
 * $Author$
 * $Id$
 *
 */

class cache_abstract {

	protected $front;
	protected $options;

	public function __construct($cache, $options) {
		$this->frontend = $cache;
		$this->options = $options;
	}

}
