<?php
/**
 *
 * cache_memory
 *
 * $Author$
 * $Id$
 *
 */

class cache_memory extends cache_abstract {

	private static $_cache = array();
	private static $_limit = 100;

	public function __construct($cache, $options) {
		parent::__construct($cache, $options);
		if (isset($options['limit'])) {
			self::$_limit = $options['limit'];
		}
	}

	public function get($key) {
		if (!isset(self::$_cache[$key])) {
			return null;
		}
		$value = self::$_cache[$key];
		if ($value && $value['time'] <= time()) {
			return $value['data'];
		}

		return null;
	}

	public function put($key, $value, $ttl=0) {
		if (count(self::$_cache) == self::$_limit) {
			if (array_key_exists($key, self::$_cache)) {
				unset(self::$_cache[$key]);
			} elseif (self::$_cache[$key]) {
				array_shift(self::$_cache[$key]);
			}
		}

		self::$_cache[$key] = array('data' => $value, 'time' => time() + $ttl);
		return true;
	}

	public function remove($key) {
		unset(self::$_cache[$key]);
		return true;
	}

	public function clear_all() {
		self::$_cache = array();
	}

}
