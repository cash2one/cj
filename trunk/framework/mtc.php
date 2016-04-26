<?php
/**
 * 缓存类, 一般用在缓存整个数组, 而不是单个键值
 * $Author$
 * $Id$
 */

class mtc {
	// 缓存路径
	protected $_cache_paths;
	protected static $_instances = null;

	/**
	 * get_instance
	 *
	 * @param  mixed $group 组名
	 * @return void
	 */
	public static function get_instance() {

		if (!self::$_instances) {
			self::$_instances = new self();
		}

		return self::$_instances;
	}

	public function get_domain() {

		$domain = startup_env::get('domain');
		if (empty($domain)) {
			$request = controller_request::get_instance();
			$host = $request->server('HTTP_HOST');
			$hostarr = explode('.', $host);
			$domain = rawurlencode($hostarr[0]);
		}

		return $domain;
	}

	public function get_key($key, $group = '') {

		if ('ucenter' == $group) {
			return "uc.{$key}";
		}

		$keys = explode('.', $key);
		if (1 == count($keys)) {
			array_unshift($keys, 'common');
		}

		if ('plugin' == $keys[0]) {
			array_shift($keys);
		}

		$key = implode('.', $keys);
		$key = ucfirst($key);

		return $this->get_domain() . ".{$key}";
	}

	/**
	 * 读取缓存
	 *
	 * @param string $key 缓存名称
	 * @param string $group 该缓存所属分组
	 * @param boolean $force_rw 是否强制读取并更新
	 */
	public function get($key, $group = null, $force_rw = false) {

		// 如果强制读取并更新
		if ($force_rw) {
			return $this->_get_from_service($key, $group);
		}

		// 从缓存中读取记录
		$c = cache::get_instance('mtc.' . $group);
		$config = $c->get_config();
		if ('cache_redis' == $config['class']) {
			$true_key = $this->get_key($key, $group);
			$value = $c->get($true_key, 'mtc.' . $group);
		} else {
			$value = $c->get($key, 'mtc.' . $group);
		}

		// 如果缓存中存在记录
		if ($value) {
			if ('cache_redis' == $config['class']) {
				return $value;
			} else {
				return $value['data'];
			}
		}

		// 从缓存文件中读取
		if ('cache_redis' != $config['class']) {
			$file = $this->_get_file_path($key, $group);
			if (file_exists($file)) {
				$cache = array();
				@include $file;
				$c->put($key, $cache);
				return $cache['data'];
			}
		}

		return $this->_get_from_service($key, $group);
	}

	/**
	 * 写入临时缓存
	 *
	 * @param string $key 键值
	 * @param array $value 值
	 * @param string $group 所属分组
	 * @param boolean $force_w 是否强制更新
	 */
	public function put($key, $value, $group = null, $force_w = false) {

		$c = cache::get_instance('mtc.' . $group);
		$config = $c->get_config();
		// 启用了 redis 缓存
		if ('cache_redis' == $config['class']) {
			$key = $this->get_key($key, $group);
			if ('uc.' != substr($key, 0, 3)) {
				$c->put($key, $value);
			}
		} else {
			$cfg = array('data' => $value, 'ttl' => 0);
			$c->put($key, $cfg);

			if ($force_w) {
				$this->_write($key, $value, $group);
			}
		}
	}

	/**
	 * 删除临时缓存
	 *
	 * @param string $key 键值
	 * @param string $group 所属组
	 */
	public function remove($key, $group) {

		$c = cache::get_instance('mtc.' . $group);
		$config = $c->get_config();
		if ('cache_redis' == $config['class']) {
			$key = $this->get_key($key, $group);
			$c->remove($key);
		} else {
			$file = $this->_get_file_path($key, $group);
			@unlink($file);
		}

		return true;
	}

	/**
	 * 根据配置清理所有临时缓存
	 *
	 * @param string $group 所属组
	 */
	public function clear_all($group) {

		// 获取配置
		$config = config::get(startup_env::get('cfg_name') . '.cache.mtc.' . $group);
		if (empty($config) || ! is_array($config)) {
			return true;
		}

		foreach ($config['keys'] as $key) {
			$this->remove($key, $group);
		}

		return true;
	}

	/**
	 * 从 service 中读取
	 *
	 * @param string $key 缓存名称
	 * @param string $group 所属分组
	 */
	protected function _get_from_service($key, $group = null) {

		// 获取配置
		$config = config::get(startup_env::get('cfg_name') . '.cache.mtc.' . $group);
		// 读取配置
		$serv = &$config['service'];
		$value = $serv->fetch($key, $group);
		if (false === $value) {
			return false;
		}

		$this->put($key, $value, $group, true);
		return $value;
	}

	/**
	 * 获取缓存文件路径
	 *
	 * @param string $key
	 * @param string $group
	 */
	protected function _get_file_path($key, $group = null) {

		$kg = md5($key . $group);
		if (! isset($this->_cache_paths[$kg])) {
			$dir = hexdec($kg{0} . $kg{1} . $kg{2}) % 1000;
			$path = config::get(startup_env::get('cfg_name') . '.cache.mtc.path');
			$this->_cache_paths[$kg] = APP_PATH . $path . '/' . $dir . '/' . $key . '.php';
		}

		return $this->_cache_paths[$kg];
	}

	/**
	 * 写缓存文件
	 *
	 * @param string $key 键值
	 * @param array $value 值
	 * @param string $group 所属分组
	 * @param int $ttl
	 */
	protected function _write($key, $value, $group = null, $ttl = 0) {

		$data = array('data' => $value, 'ttl' => intval($ttl));
		$cache_file = $this->_get_file_path($key, $group);
		rmkdir(dirname($cache_file));
		$cachedata = "\$cache = " . var_export($data, true) . ";\n";
		// 写缓存文件
		if ($fp = @fopen($cache_file, 'wb')) {
			fwrite($fp, "<?php\n//cache file, DO NOT modify me!" . "\n//Created: " . date("M j, Y, G:i") . "\n//Identify: " . md5($cache_file . $cachedata) . "\n\n$cachedata");
			fclose($fp);
		} else {
			exit("Can not write to cache files, please check directory {$cache_file} .");
		}

		return true;
	}

}
