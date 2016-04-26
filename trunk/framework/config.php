<?php
/**
 * 配置文件
 *
 * $Author$
 * $Id$
 */

class config {

	/**
	 * _values
	 *
	 * @var array
	 */
	private $_values = array();

	/**
	 * _instance
	 *
	 * @var object
	 */
	protected static $_instance = null;

	/**
	 * _loaded
	 *
	 * @var array
	 */
	protected static $_loaded = array();

	/**
	 * &get_instance
	 * 获取一个config类的实例
	 *
	 * @return object
	 */
	public static function &get_instance() {

		if (!self::$_instance) {
			self::$_instance = new config();
		}

		return self::$_instance;
	}

	/**
	 * get
	 * 获取一个配置的值
	 *
	 * @param string $key 配置的key
	 * @param mixed $default_value key不存在时返回默认值
	 * @return mixed
	 */
	public static function get($key, $default_value = null) {

		$config =& config::get_instance();
		$value = $config->_get($key);
		if (!$value) {
			return $default_value;
		}

		return $value;
	}

	/**
	 * exists
	 * 检查一个配置是否存在
	 *
	 * @param  string $key 配置的key
	 * @return boolean
	 */
	public static function exists($key) {

		$config =& config::get_instance();
		return $config->_exists($key);
	}

	/**
	 * set
	 * 设置值
	 *
	 * @param  string $key 键
	 * @param  mixed $value 值
	 * @return void
	 */
	public static function set($key, $value) {

		$config =& config::get_instance();
		$config->_values[$key] = $value;
	}

	/**
	 * _get
	 * 获取一个配置的值
	 *
	 * @param string $key 配置的key
	 * @return mixed
	 */
	protected function _get($key) {

		if (isset($this->_values[$key])) {
			return $this->_values[$key];
		}

		$this->_load_key($key);
		$value = $this->_match($key);
		config::set($key, $value);

		return $value;
	}

	/**
	 * _match
	 * 匹配
	 *
	 * @param  string $key
	 * @return mixed
	 */
	protected function _match($key) {

		if (isset($this->_values[$key]) && $this->_values[$key]) {
			return $this->_values[$key];
		}

		$parts = explode('.', $key);
		if (!$parts) {
			return false;
		}

		$leave = array();
		$array = array();
		for ($i = 0; $i < count($parts); $i ++) {
			$part = array_pop($parts);
			array_unshift($leave, $part);

			$pattern = join('.', $parts);
			if (isset($this->_values[$pattern]) && ($array = $this->_values[$pattern])) {
				break;
			}
		}

		if (!$array || !$leave) {
			return false;
		}

		if (!is_array($array)) {
			return null;
		}

		$value = $array;
		foreach ($leave as $part) {

			if ($value && is_array($value) && isset($value[$part])) {
				$value = $value[$part];
			} else {
				$value = null;
				break;
			}
		}

		return $value;
	}

	/**
	 * _exists
	 * 检查一个配置是否存在
	 *
	 * @param  string $key 配置的key
	 * @return boolean
	 */
	protected function _exists($key) {

		$value = self::get($key);
		return $value !== null;
	}

	/**
	 * _load_key
	 * 根据key加载配置
	 *
	 * @param string $key
	 * @return void
	 */
	protected function _load_key($key) {

		$path = explode('.', $key);
		$apps = startup_env::get('apps');
		$run_mode = isset($_SERVER['RUN_MODE']) ? strtolower($_SERVER['RUN_MODE']) : '';
		$startup = &startup::factory();

		if (!$run_mode) {
			$run_mode = 'production';
		}

		switch($run_mode) {
			case 'production':
			case 'development':
			case 'test':
				$global_key = 'global_'.$run_mode;
				break;
			default:
				$global_key = 'global_production';
		}

		/** rootkey 表示文件路径+文件名组成的key */
		if ($path[0] == 'framework') {

			/** 以framework开头的，就去框架的framework的config目录下找 */
			$rootkey = $path[0].'.'.$path[1];
			$file = ROOT_PATH.'/framework/config/'.$path[1].'.php';

		} else if ($path[0] == 'global') {

			/** 以global开头的，就去整个框架的config目录下找 */
			$rootkey = $path[0].'.'.$path[1];
			$file = ROOT_PATH.'/config/'.$path[1].'.php';

		} else if($childapp = $startup->get_option('childapp')) { // 取当前子项目的配置，就去当前项目的config目录下找

			$rootkey = $path[0].'.'.$path[1];
			$cfg_path = APP_PATH .'/src/'.$path[0].'/config/';
			if ('__'.basename(APP_PATH).'__' == $path[0]) {
				$cfg_path = APP_PATH .'/config/';
			}

			/** 是否有不同运行环境的配置 */
			$file = $cfg_path.$run_mode.'/'.$path[1].'.php';
			if (!is_file($file)) {
				$file = $cfg_path.$path[1].'.php';
			}

			/** 加载子目录下的配置 */
			if (isset($path[1]) && isset($path[2])) {
				$sub_file = $cfg_path.$path[1].'/'.$path[2].'.php';
				if (is_file($sub_file)) {
					$sub_root_key = $path[0].'.'.$path[1].'.'.$path[2];
					$this->_load($sub_root_key, false, $sub_file);
				}
			}

			if (!is_file($file)) {
				$rootkey = $path[0];
				$file = $cfg_path.$global_key.'.php';
			}

		} else if (basename(APP_PATH) == $path[0]) {

			/** 取当前项目的配置，就去当前项目的config目录下找 */
			$rootkey = $path[0].'.'.$path[1];

			/** 是否有不同运行环境的配置 */
			$file = APP_PATH .'/src/config/'.$run_mode.'/'.$path[1].'.php';
			if (!is_file($file)) {
				$file = APP_PATH .'/src/config/'.$path[1].'.php';
			}

			/** 加载子目录下的配置 */
			if (isset($path[1]) && isset($path[2])) {
				$sub_file = APP_PATH .'/src/config/'.$path[1].'/'.$path[2].'.php';
				if (is_file($sub_file)) {
					$sub_root_key = $path[0].'.'.$path[1].'.'.$path[2];
					$this->_load($sub_root_key, false, $sub_file);
				}
			}

			if (!is_file($file)) {
				$rootkey = $path[0];
				$file = APP_PATH. '/src/config/'.$global_key.'.php';
			}
		} else if (in_array($path[0], $apps)) {

			/** 去非当前项目的配置文件 */
			$rootkey = $path[0].'.'.$path[1];
			$file = ROOT_PATH.'/apps/'.$path[0].'/config/'.$path[1].'.php';

			/** 加载子目录下的配置 */
			$sub_file = ROOT_PATH.'/apps/'.$path[0].'/config/'.$path[1].'/'.$path[2].'.php';
			if (is_file($sub_file)) {
				$sub_root_key = $path[0].'.'.$path[1].'.'.$path[2];
				$this->_load($sub_root_key, false, $sub_file);
			}

			if (!is_file($file)) {
				/** apps/Appname/config/global_xxx.php */
				$rootkey = $path[0];
				$file = ROOT_PATH.'/apps/'.$path[0].'/config/'.$global_key.'.php';
			}

		} else if (is_file(ROOT_PATH.'/config/'.$path[0].'.php')) {

			/** 如果还没找到，就去框架全局的配置目录找 */
			$file = ROOT_PATH.'/config/'.$path[0].'.php';
			$rootkey = $path[0];

		} else {

			/** 最后如果没找到，就去全局配置目录下找global_production/developer文件 */
			$file = ROOT_PATH.'/config/'.$global_key.'.php';
			$rootkey = 'global';
		}

		$this->_load($rootkey, false, $file);
	}

	/**
	 * _load
	 * 载入配置
	 *
	 * @param  string $rootkey 根key
	 * @param  mixed $conf 配置
	 * @param  string $file 定义配置文件
	 * throw new config_exception
	 * @return void
	 */
	protected function _load($rootkey, $conf = false, $file = null) {

		if ($conf === false) {
			if (in_array($file, self::$_loaded)) {
				return ;
			} else {
			   array_push(self::$_loaded, $file);
			}

			if (!is_file($file)) {
				$file = ROOT_PATH.'/config/global.php';
			}

			require_once($file);
		}

		if (is_array($conf)) {
			foreach ($conf as $key => $value) {
				config::set($rootkey.'.'.$key, $value);
			}
		}
	}

}
