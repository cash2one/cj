<?php
/**
 * startup_env
 *
 * $Author$
 * $Id$
 */

class startup_env {

	/**
	 * _env
	 *
	 * @var array
	 */
	private static $_env = array();

	/**
	 * _instance
	 *
	 * @var object
	 */
	protected static $_instance = null;

	/**
	 * &get_instance
	 * 获取一个Config类的实例
	 *
	 * @return object
	 */
	public static function &get_instance() {

		if (!self::$_instance) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * __construct
	 *
	 * @return void
	 */
	protected function __construct() {

		$this->_set('apps', array_map('basename', glob(ROOT_PATH.'/apps/*', GLOB_ONLYDIR)));
		$this->_set('app_name', basename(APP_PATH));
		$this->_set('root_path', ROOT_PATH);
		$this->_set('app_path', APP_PATH);
		$this->_set('timestamp', $_SERVER['REQUEST_TIME']);
		$this->_set('int_max', 2147483647);
		// scheme
		if (isset($_SERVER['REQUEST_SCHEME'])) {
			$this->_set('scheme', $_SERVER['REQUEST_SCHEME']);
		} else {
			$this->_set('scheme', isset($_SERVER['HTTPS']) ? 'https' : 'http');
		}

		// boardurl
		if (empty($_SERVER['REQUEST_METHOD'])) {
			$this->_set('boardurl', strtr($_SERVER['HOSTNAME'].$_SERVER['SCRIPT_FILENAME'], '*', '#'));
		} else {
			$this->_set('boardurl', strtr($this->_get('scheme').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], '*', '#'));
		}
	}

	/**
	 * set
	 * 设置环境变量
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @return void
	 */
	public static function set($key, $value = null) {

		$env = self::get_instance();
		$env->_set($key, $value);
	}

	/**
	 * setBenchmark
	 * 设置时间
	 *
	 * @param  mixed $key
	 * @param  mixed $time
	 * @return void
	 */
	public static function set_benchmark($key, $time = null) {

		$env = self::get_instance();
		$benchmark = $env->_get('benchmark');

		if (!$time) {
			$time = microtime(true);
		}

		if (!$benchmark[$key]) {
			$benchmark[$key] = $time;
		}

		$env->_set('benchmark', $benchmark);
	}

	/**
	 * _set
	 * 设置环境变量
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @return void
	 */
	protected function _set($key, $value) {

		if (!$key) {
			return false;
		}

		if (is_array($key)) {
			self::$_env = array_merge(self::$_env, $key);
		} else {
			self::$_env[$key] = $value;
		}

		return true;
	}

	/**
	 * get
	 * 获取环境变量
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public static function get($key = null) {

		$env = self::get_instance();
		return $env->_get($key);
	}

	/**
	 * _get
	 * 获取环境变量
	 *
	 * @param  string $key
	 * @return mixed
	 */
	protected function _get($key = null) {

		if (!$key) {
			return self::$_env;
		}

		return isset(self::$_env[$key]) ? self::$_env[$key] : '';
	}

}
