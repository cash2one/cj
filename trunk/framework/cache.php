<?php
/**
 * cache
 *
 * $Author$
 * $Id$
 *
 */

class cache {
	private $_conf;
	private static $_instances = array();
	// 缓存页面数据
	private static $_data = array();
	private $_backend;
	private $_status = array();

	/**
	 * __construct
	 *
	 * @param mixed $group
	 * @return void
	 */
	public function __construct($group) {

		$this->_conf = config::get(startup_env::get('cfg_name') . '.cache.' . $group);
	}

	/**
	 * get_instance
	 *
	 * @param mixed $group 组名
	 * @return void
	 */
	public static function get_instance($group) {

		if (! array_key_exists($group, self::$_instances)) {
			self::$_instances[$group] = new cache($group);
		}

		$res = self::$_instances[$group];
		return $res;
	}

	/**
	 * put
	 *
	 * @param mixed $key
	 * @param mixed $value 数据
	 * @param mixed $group 组名
	 * @return void
	 */
	public function put($key, $value, $group = null) {

		if ($group !== null) {
			$cache = & self::get_instance($group);
		} else {
			$cache = & $this;
		}

		$res = $cache->_get_handle()->put($key, $value);
		// 检查缓存大小
		if (count(self::$_data) > config::get(startup_env::get('cfg_name') . '.cache.page_cache_num')) {
			// 移除最早的数据
			array_shift(self::$_data);
		}

		// 添加缓存
		self::$_data[$key] = $value;

		return $res;
	}

	/**
	 * get
	 *
	 * @param mixed $key
	 * @param mixed $group
	 * @return void
	 */
	public function get($key, $group = null) {

		// 检查是否已经缓存
		if (array_key_exists($key, self::$_data)) {
			return self::$_data[$key];
		}

		if ($group !== null) {
			$cache = self::get_instance($group);
		} else {
			$cache = $this;
		}

		$res = $cache->_get_handle()->get($key);

		// 检查缓存大小
		if (count(self::$_data) > config::get(startup_env::get('cfg_name') . '.cache.page_cache_num')) {
			// 移除最早缓存
			array_shift(self::$_data);
		}

		// 缓存数据
		self::$_data[$key] = $res;

		return $res;
	}

	/**
	 * mput 一次缓存多个记录
	 *
	 * @param array $datas 缓存数据
	 * @param mixed $group 组名
	 * @return void
	 */
	public function mput($datas, $group = null) {

		if (! is_array($datas)) {
			return false;
		}

		if ($group !== null) {
			$cache = & self::get_instance($group);
		} else {
			$cache = & $this;
		}

		foreach ($datas as $key => $value) {
			$cache->_get_handle()->put($key, $value);

			// 检查缓存大小
			if (count(self::$_data) > config::get(startup_env::get('cfg_name') . '.cache.page_cache_num')) {
				// 移除最早的数据
				array_shift(self::$_data);
			}

			self::$_data[$key] = $value;
		}

		return true;
	}

	/**
	 * 一次获取多个缓存
	 *
	 * @param array $keys
	 * @param string $group
	 * @return array
	 */
	public function mget($keys, $group = null) {

		if (! $keys) {
			// 命中和失效的缓存都为数组
			return array(array(), array());
		}

		$result = $hits = $misses = $need_fetch_keys = array();

		foreach ($keys as $k => $key) {
			if (array_key_exists($key, self::$_data)) {
				$hits[$key] = self::$_data[$key];
			} else {
				$misses[$key] = $k;
				$need_fetch_keys[$key] = $key;
			}
		}

		// 内存中没有命中的数据
		if ($need_fetch_keys) {
			if ($group !== null) {
				$cache = & self::get_instance($group);
			} else {
				$cache = & $this;
			}

			$datas = $cache->_get_handle()->mget($need_fetch_keys);
			if (is_array($datas)) {
				$hits = array_merge($hits, $datas);
				foreach ($datas as $key => $data) {
					unset($misses[$key]);

					// 检查缓存大小
					if (count(self::$_data) > config::get(startup_env::get('cfg_name') . '.cache.page_cache_num')) {
						// 移除最早的数据
						array_shift(self::$_data);
					}

					self::$_data[$key] = $data;
				}
			}
		}

		$result = array($hits, $misses);
		/**
		 * 返回数组中$result[0]是命中的缓存， $result[1]是没有命中的, 且返回值是
		 * 以 array(hashKey => value)的形式, 方便后端检查那个键没有命中
		 */
		return $result;
	}

	/**
	 * remove
	 *
	 * @param mixed $key
	 * @param mixed $group
	 * @return void
	 */
	public function remove($key, $group = null) {

		if ($group !== null) {
			$cache = & self::get_instance($group);
		} else {
			$cache = & $this;
		}

		// 移除缓存
		unset(self::$_data[$key]);
		$res = $cache->_get_handle()->remove($key);

		return $res;
	}

	/**
	 * 清理缓存(仅供测试用例使用)
	 *
	 * @param mixed $group
	 * @return mixed
	 */
	public function clear_all($group = null) {

		if ($group !== null) {
			$cache = & self::get_instance($group);
		} else {
			$cache = & $this;
		}

		$res = $cache->_get_handle()->clear_all();
		$cache->_status = array();
		// 清空所有缓存
		self::$_data = array();
		return $res;
	}

	/**
	 * 获取配制信息(仅供测试用例使用)
	 */
	public function get_config() {

		return $this->_conf;
	}

	/**
	 * generate_id
	 * 产生缓存ID
	 *
	 * @param mixed $key
	 * @param mixed $group
	 * @return void
	 */
	public function generate_id($key, $group = null) {

		if (is_array($key)) {
			ksort($key);
		} else {
			$key = array('92cc3d581fc30d9f4b4737d2592656e4' => $key);
		}

		$res = md5($group . '_' . http_build_query($key));
		return $res;
	}

	/**
	 * _get_handle
	 *
	 * @return object
	 */
	private function _get_handle() {

		if (! $this->_backend) {
			$this->_backend = new $this->_conf['class']($this, $this->_conf['options']);
		}

		return $this->_backend;
	}

	/**
	 * 用于测试时记录调试信息
	 * 用法：在get/put等方法里手动添加相应的赋值语句
	 *
	 * @param mixed $group
	 * @return mixed
	 */
	public function get_status($group = null) {

		if ($group !== null) {
			$cache = & self::get_instance($group);
		} else {
			$cache = & $this;
		}

		return $cache->_status;
	}

		// 获取超时时间
	public function get_ttl() {

		return $this->_conf['ttl'];
	}
}
