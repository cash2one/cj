<?php
/**
 *　service
 *
 * $Author$
 * $Id$
 */

class service {

	/**
	 *  全局true
	 */
	const GLOBAL_TRUE = 1;

	/**
	 *  全局false
	 */
	const GLOBAL_FALSE = 2;

	/**
	 *  _instances
	 *  service对象集合
	 *
	 *  @var object
	 */
	static private $_instances = array();

	// 扩展方法类实例
	static private $_sub_instances = array();
	// 扩展方法集合
	protected static $_methods = array();

	static private $_d2s_map = array();
	static private $_s2d_map = array();
	// 数据数组
	protected $_params = array();
	// 数据类型
	const VAR_INT = 1;
	const VAR_STR = 2;
	const VAR_ARR = 4;
	// service class name
	protected $_s_classname = '';

	/**
	 * &factory
	 * 实例化一个service
	 *
	 * @param string $class service类名
	 * @param array $shard_key 分表/分库参数
	 * @return object
	 */
	public static function &factory($class, $shard_key = array()) {

		// 取 service 别名配置
		$config = config::get(startup_env::get('cfg_name').'.service.'.$class);
		// 如果存在别名对应的实际名称, 则
		if ($config['impl']) {
			$class = $config['impl'];
		}

		// 如果不存在, 则初始化
		if (!array_key_exists($class, self::$_instances)) {
			self::$_instances[$class] = new $class($shard_key);
		}

		return self::$_instances[$class];
	}

	/**
	 * convert_field
	 * 转换一个或多个key
	 *
	 * @param  string|array $key
	 * @param  boolean $dao2service
	 * @return string
	 */
	public static function convert_field($key, $dao2service = false) {

		if (is_array($key)) {
			foreach ($key as &$v) {
				$v = self::convert_field($v, $dao2service);
			}

			return $key;
		}

		if ($dao2service) {
			/** aaa_bbb => aaaBbb */
			if (!self::$_d2s_map[$key]) {
				$n_key = str_replace('_', ' ', $key);
				$n_key = ucwords('_'.$n_key);
				$n_key = str_replace(array('_',' '), '', $n_key);
				self::$_d2s_map[$key] = $n_key;
			}

			return self::$_d2s_map[$key];
		}

		/** aaaBbb => aaa_bbb */
		if (!self::$_s2d_map[$key]) {
			$n_key = trim(preg_replace('/([A-Z])/', '_\1', $key), '_');
			$n_key = strtolower($n_key);
			self::$_s2d_map[$key] = $n_key;
		}

		return self::$_s2d_map[$key];
	}

	/**
	 * convert_property  属性转换
	 *
	 * 主要是将数据字段和实体属性之间进行转换
	 *
	 * <code>
	 *  // u_id => uId
	 *  service::convert_property($data, true);
	 *
	 *  // uId => u_id
	 *  Baceservice::convert_property($data);
	 * </code>
	 *
	 * @param  array $data
	 * @param  $dao2service
	 * @return array
	 */
	public static function convert_property($data, $dao2service = false) {

		if (!is_array($data)) {
			return $data;
		}

		$keys = array();
		$n_data = array();
		foreach ($data as $key => $value) {
			if (!$keys[$key]) {
				$keys[$key] = self::convert_field($key, $dao2service);
			}

			$n_data[$keys[$key]] = $value;
		}

		return $n_data;
	}

	/**
	 * convert_array_property
	 *
	 * @param  mixed $data
	 * @param  mixed $dao2service
	 * @return void
	 */
	public static function convert_array_property($data, $dao2service = false) {

		if (!is_array($data) || !is_array(current($data))) {
			return array();
		}

		$keys = array();
		foreach ($data as &$value) {

			$tmp = array();
			foreach ($value as $k => $v) {
				if (!$keys[$k]) {
					$keys[$k] = self::convert_field($k, $dao2service);
				}
				$tmp[$keys[$k]] = $v;
			}
			$value = $tmp;
		}

		return $data;
	}

	/**
	 * filter_field
	 *
	 * @param array data 要过滤的数组
	 * @param array fields 过滤的字段
	 * @return array | false 如果成功array 如果要过滤的字段不存在false
	 */
	public static function filter_field($data, $fields) {
		$ret = array();
		if ($fields && is_array($data) && is_array($fields)) {
			foreach($fields as $key) {
				if (!isset($data[$key]) && $data[$key] !== NULL) {
					/** isset()在检察值为NULL的变量的时候会返回false */
					return false;
				} else {
					$ret[$key] = $data[$key];
				}
			}

			return $ret;
		}

		return $data;
	}

	/**
	 * 去除字段前缀/后缀
	 * @param array $data 数据数组
	 * @param string $prefix 前缀
	 * @param string $suffix 后缀
	 */
	public function trim_field($data, $prefix = '', $suffix = '') {
		if (empty($prefix) && empty($suffix)) {
			return $data;
		}

		$ret = array();
		foreach ($data as $k => $v) {
			/** 剔除前缀 */
			if (!empty($prefix)) {
				$k = preg_replace('/^'.addslashes($prefix).'/i', '', $k);
			}

			/** 剔除后缀 */
			if (!empty($suffix)) {
				$k = preg_replace('/'.addslashes($prefix).'$/i', '', $k);
			}

			$ret[$k] = $v;
		}

		return $ret;
	}

	/**
	 * filter_array_field
	 *
	 * @param array data 要过滤的数组
	 * @param array fields 过滤的字段
	 * @return array | false 如果成功array 如果要过滤的字段不存在false
	 */
	public static function filter_array_field($data, $fields) {
		$ret = array();
		if ($fields && is_array($data) && is_array(current($data))) {
			foreach ($data as $key => $value) {
				$tmp_value = self::filter_field($value, $fields);
				if (false === $tmp_value) {
					return false;
				} else {
					$ret[$key] = $tmp_value;
				}
			}

			return $ret;
		}

		return $data;
	}

	/** 获取对应 dao 的表名 */
	protected function get_table() {
		$class = get_class($this);
		$class = str_replace('_s_', '_d_', $class);
		/** 取类的默认属性值 */
		$vars = get_class_vars($class);
		if (isset($vars['__table'])) {
			return $vars['__table'];
		}

		return '';
	}

	/**
	 * begin 开始一个事务
	 *
	 * @param  string $table 数据表名称(用来分库)
	 * @return void
	 */
	public function begin($table = '') {
		if (empty($table)) {
			$table = $this->get_table();
		}

		$db = &db_table::factory($table);
		$db->begin();
	}

	/**
	 * commit 提交
	 *
	 * @param  string $table 数据表名称(用来分库)
	 * @return void
	 */
	public function commit($table = '') {
		if (empty($table)) {
			$table = $this->get_table();
		}

		$db = &db_table::factory($table);
		$db->commit();
	}

	/**
	 * rollback 回滚
	 *
	 * @param  string $table 数据表名称(用来分库)
	 * @return void
	 */
	public function rollback($table = '') {
		if (empty($table)) {
			$table = $this->get_table();
		}

		$db = &db_table::factory($table);
		$db->rollback();
	}

	/**
	 * add_extension
	 * service中的方法分组，必须在__contruct时调用这个方法，告诉service应该加载哪些子类里面的方法
	 *
	 * @param  string $name  分组名
	 * @param  string $clazz 类名
	 * @sample
	 * class vchangyi_s_message extends service {
	 *	  public function __construct() {
	 *		  $this->add_extension('vchangyi_s_message_a', 'a');
	 *		  $this->add_extension('vchangyi_s_message_b');
	 *	  }
	 * }
	 *
	 * class vchangyi_s_message_a extends vchangyi_s_message {
	 *	  public function a() {
	 *		  var_dump('a');
	 *	  }
	 * }
	 *
	 * class vchangyi_s_message_b extends vchangyi_s_message {
	 *	  public function b() {
	 *		  self::a();
	 *		  var_dump('b');
	 *	  }
	 * }
	 * @return void
	 */
	public function add_extension($clazz, $method = null) {

		// 获取类方法
		$methods = get_class_methods($clazz);

		// 做对应关系
		foreach ($methods as $_method) {
			// 如果未指定方法或者和指定方法名相同时
			if (empty($method) || $method == $_method) {
				self::$_methods[$this->_s_classname.'.'.$_method] = $clazz;
			}
		}

		return true;
	}

	/**
	 * __call
	 * 找不到方法名时，自动去根据self::$_methods的对应关系找到需要加载的类，并执行方法
	 *
	 * @param  string $method
	 * @param  mixed $args
	 * @return mixed
	 */
	public function __call($method, $args) {

		// 判断是否处在扩展方法中
		if (!isset(self::$_methods[$this->_s_classname.'.'.$method])) {
			exit('Class "'.get_class($this).'" not exists method "'.$method.'"');
		}

		// 获取类名
		$clazz = self::$_methods[$this->_s_classname.'.'.$method];
		// 如果类对象不存在, 则新建
		if (!array_key_exists($clazz, self::$_sub_instances)) {
			self::$_sub_instances[$clazz] = new $clazz;
		}

		return call_user_func_array(array(self::$_sub_instances[$clazz], $method), $args);
	}

	/**
	 * 获取键名对应的值
	 * @param string $key 键名
	 * @param * $default 默认值
	 * @return multitype:|string
	 */
	protected function _get($key, $default = null) {

		$key = (string)$key;
		if (array_key_exists($key, $this->_params)) {
			return $this->_params[$key];
		}

		return $default;
	}

	/**
	 * 提取指定字段数据
	 * @param array $fields 数据键值, 如:
	 *  + array(array('uname', 1), array('email', 2, 'chk_email', '格式错误', true), 'passwd', ...)
	 * @param array $from 数据来源
	 * @param array &$to 提取的数据
	 * @return boolean
	 */
	public function extract_field(&$to, $fields, $from = array()) {

		// 如果为空, 则取默认数据
		if (empty($from)) {
			$from = $this->_params;
		}

		// 提取指定字段对应的值
		foreach ($fields as $_k => $_f) {
			/**
			 * 取出键值和数据类型
			 * $k => 解析后的目的键值
			 * $type => 类型
			 * $method => 验证方法名称
			 * $method_err => 错误提示
			 * $ignore_null => 是否忽略不存在的键值
			 */
			list($k, $type, $method, $method_err, $ignore_null) = (array)$_f;
			// 取 $k 对应的值
			$k = (string)$k;

			// 如果来源键值为数字, 则说明未指定来源键值
			if ($_k == (int)$_k) {
				$_k = $k;
			}

			// 如果允许该字段为空
			if ($ignore_null && !isset($from[$_k])) {
				continue;
			}

			$val = isset($from[$_k]) ? $from[$_k] : '';
			// 类型强制转换
			switch ($type) {
				case self::VAR_ARR: $val = (array)$val; break;
				case self::VAR_INT: $val = (int)$val; break;
				case self::VAR_STR: $val = (string)$val; break;
				default: $val = (string)$val; break;
			}

			// 赋值
			$to[$k] = $val;

			// 处理方法
			$method = (string)$method;
			$method = trim($method);
			// 如果验证方法为空
			if (empty($method)) {
				continue;
			}

			// 如果是数组
			if (is_array($method)) {
				list($object, $func) = $method;
			} else {
				$object = $this;
				$func = $method;
			}

			// 判断处理函数是否存在
			if (!method_exists($object, $func)) {
				$this->errmsg(100, 'method_not_exists');
				return false;
			}

			// 验证参数
			$method_err = empty($method_err) ? $_k.' error' : $method_err;
			// 如果验证错误
			if (!$object->$func($to[$k], $method_err)) {
				return false;
			}
		}

		return true;
	}
}
