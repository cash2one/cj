<?php
/**
 *　统一数据访问基类(uniform data access)
 *
 * $Author$
 * $Id$
 */

class uda {
	/**
	 *  _instances
	 *  service对象集合
	 *
	 *  @var object
	 */
	private static $s__instances = array();
	/** servce对象集合 */
	private static $__s = array();
	/**
	 * 错误号, 为 0 时正确, 其他值为错误;
	 * @var int
	 */
	public $errno = 0;
	/** 错误详情 */
	public $error = '';

	/** （新版）错误编码 */
	public $errcode = 0;
	/** （新版）错误详情 */
	public $errmsg = '';

	/** controller_request 实例 */
	protected $_request;
	/** 数据数组 */
	protected $_params = array();
	/** 数据类型：整型 */
	const VAR_INT = 1;
	/** 数据类型：字符串 */
	const VAR_STR = 2;
	/** 数据类型：数组 */
	const VAR_ARR = 4;
	/** 数据类型：正整数 */
	const VAR_ABS = 5;

	/**
	 * &factory
	 * 实例化一个service
	 *
	 * @param string $class uda类名
	 * @param array $option 初始化参数
	 * @return object
	 */
	public static function &factory($class, $option = null) {

		$key = $class;
		if (is_array($option)) {
			asort($option);
			$key .= serialize($option);
		} elseif (!empty($option)) {
			$key .= $option;
		}

		$md5 = md5($key);
		if (!array_key_exists($md5, self::$s__instances)) {
			self::$s__instances[$md5] = new $class($option);
		}

		return self::$s__instances[$md5];
	}

	/**
	 * 用于调用service内格式化方法的工厂方法
	 * 该方法只允许呼叫service内format相关的方法
	 * @param string $class
	 * @return object
	 */
	public static function &f($class) {

		$key = md5(rstrtolower($class));
		if (!array_key_exists($key, self::$__s)) {
			self::$__s[$key] = &service::factory($class);
		}

		return self::$__s[$key];
	}

	public function __construct() {

		$this->_request = controller_request::get_instance();
		$this->_params = $this->_request->getx();
	}

	/**
	 * 获取键名对应的值
	 * @param string $key 键名
	 * @param * $default 默认值
	 * @return multitype:|string
	 */
	public function get($key, $default = null) {

		/** 如果未初始化数据数组, 则 */
		if (empty($this->_params)) {
			return $this->_request->get($key);
		}

		$key = (string)$key;
		if (array_key_exists($key, $this->_params)) {
			return $this->_params[$key];
		}

		return $default;
	}

	/**
	 * 提取指定字段数据
	 * @param array &$to 提取的数据
	 * @param array $fields 数据键值, 如:
	 *  + array(array('uname', 1), array('email', 2, 'chk_email', '格式错误', true), 'passwd', ...)
	 * @param array $from 数据来源
	 * @param boolean $throw_exception 是否抛出异常。true=是,false=否。默认：false=否
	 * @return boolean
	 */
	public function extract_field(&$to, $fields, $from = array(), $throw_exception = false) {

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
			if ($_k === (int)$_k) {
				$_k = $k;
			}

			// 如果允许该字段为空
			if ($ignore_null && !isset($from[$_k])) {
				continue;
			}

			$val = isset($from[$_k]) ? $from[$_k] : '';
			// 类型强制转换
			switch ($type) {
				case self::VAR_ARR: $val = empty($val) ? array() : (array)$val; break;
				case self::VAR_INT: $val = (int)$val; break;
				case self::VAR_STR: $val = (string)$val; break;
				case self::VAR_ABS: $val = (int)$val; $val < 0 && $val = 1; break;
				default: $val = (string)$val; break;
			}

			// 赋值
			$to[$k] = $val;

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
				$errcode = 1001;
				$errmsg = 'method "'.$func.'" not exists';
				$this->errmsg($errcode, $errmsg);
				if ($throw_exception) {
					throw new help_exception($errmsg, $errcode);
				}
				return false;
			}

			// 验证参数
			$method_err = empty($method_err) ? $_k.' error' : $method_err;
			if (!$object->$func($to[$k], $method_err)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * 设置错误信息
	 * @param int $errno 错误代号
	 * @param string $error 错误详情
	 */
	public function errmsg($code, $msg = '') {

		$this->errno = (int)$code;
		$this->error = (string)$msg;

		$this->errcode = (int)$code;
		$this->errmsg = (string)$msg;
	}

	/**
	 * 解析错误编码常量并输出到uda错误信息变量内
	 * @param string $const_string
	 * <pre>
	 * $this->errcode
	 * $this->errmsg
	 * </pre>
	 * @return boolean
	 */
	public function set_errmsg($const_string) {

		// 传入的参数
		$args = func_get_args();
		call_user_func_array(array($this, 'parse_errmsg'), func_get_args());

		// 新的错误变量名
		$this->errcode = (int)$this->errno;
		$this->errmsg = (string)$this->error;

		return false;
	}

	/**
	 * 全局输出定义错误码信息
	 * @param string $const_string 错误码常量文字，格式为：[number]:[string]
	 * @return boolean
	 */
	public function parse_errmsg($const_string) {

		$this->errno = -449;
		$this->error = 'vdoc_frontend_h_func default';
		if (preg_match('/^\s*(\d+)\s*\:\s*(.+)$/', $const_string, $match)) { // 分离 错误代码 和 错误消息
			$this->errno = (int)$match[1];
			$this->error = (string)$match[2];
		} else { // 错误代码定义出错
			$this->errno = -440;
			$this->error = $const_string;
		}

		// 根据变量切字符串
		$var_count = count(preg_split('/\%\w/i', $this->error));
		// 错误消息描述内未发现变量，则直接输出
		if (2 > $var_count) {
			return true;
		}

		// 获取给定的参数
		$values = func_get_args();
		// 清除非变量值
		unset($values[0]);
		// 如果变量值不存在
		if (empty($values)) {
			return false;
		}

		// 如果给定参数个数和定义的参数个数相同, 则
		$lost_count = $var_count - count($values);
		if (0 < $lost_count) {
			// 补充缺失的参数
			for ($i = 0; $i < $lost_count; $i ++) {
				$values[] = '';
			}
		}

		// 转义变量名
		$this->error = preg_replace('/\%\s+$/is', '', vsprintf($this->error, $values));

		return true;
	}

	/**
	 * 把提交的数据转成表数据
	 * @param array $gp2field 键值和类型以及处理函数的对应关系
	 *  array(
	 *  	key_1 => array('val_***', 'string'),
	 *  	key_2 => 'val_***'
	 *  	...
	 *  )
	 * @param array $data 返回验证后的数据数组
	 * @param array $odata 旧数据数组
	 */
	protected function _submit2table($gp2field, &$data, $odata = array()) {
		/** 遍历所需参数 */
		foreach ($gp2field as $gp => $props) {
			/** 取用户提交的数据 */
			$v = $this->get($gp);
			/** 取当前数据的类型 */
			$type = 'string';
			/** 处理函数名 */
			$method = '';

			/**
			 * 如果 $props 为数组, 则第一个值为处理函数, 第二个值为类型
			 * 如果 $props 为字串, 则为处理函数, 类型默认为 string
			 */
			if (is_array($props)) {
				$method = (string)$props[0];
				if (!empty($props[1])) {
					$type = (string)$props[1];
				}
			} else {
				$method = (string)$props;
			}

			/** 类型强制转换 */
			switch ($type) {
				case 'string':$v = (string)$v;break;
				case 'int':$v = (int)$v;break;
				case 'array':$v = (array)$v;break;
				default:$v = (string)$v;break;
			}

			/** 判断处理函数是否存在 */
			if (!method_exists($this, $method)) {
				$this->errmsg(100, 'method_not_exists');
				return false;
			}

			if (!$this->$method($v, $data, $odata)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * 校验提交的数据转换为搜索的真实条件
	 *
	 * <strong style="color:blue">除非验证条件值的方法名不存在，否则永远返回 true</strong>
	 * @param array $field_define 待验证的条件和验证定义数组
	 * <pre>array(
	 * 		'key_1' => array(默认值[, 数据类型, 验证方法名(以search_val_开头), 数据提交方式]),
	 * 		'key_2' => array(默认值[, 数据类型, 验证方法名(以search_val_开头), 数据提交方式]),
	 * 		... ...
	 * )</pre>
	 * @param array &$format_condition 通过验证了的条件数组
	 * @param array &$search_by 查询条件的真实原始值
	 * @return boolean
	 */
	protected function _submit2search_condition($field_define, &$format_condition, &$search_by) {

		$format_condition = array();
		$search_by = array();

		// 遍历参数定义
		foreach ($field_define as $gp => $rule) {

			/**
			 * 如果规则为数组，则键值：
			 * 0 默认值 （必须）
			 * 1 数据类型 （可选：string、int、array，默认为：get）
			 * 2 验证方法名 （可选）
			 * 3 数据提交方式（可选：get、post，默认为:get）
			 * 如果规则rule不是数组，则认为其为默认值，类型为字符串，不执行验证
			 */
			if (!is_array($rule)) {
				$rule = array(
					0 => $rule,
					1 => 'string',
					2 => '',
					3 => 'get',
				);
			}

			// 如果未指明验证方法名，则忽略对此数据的验证
			if (!isset($rule[2])) {
				$rule[2] = '';
			}

			// 如果未指明数据提交方式，则默认使用get来获取，否则使用post来获取
			if (!isset($rule[3]) || $rule[3] != 'post') {
				$rule[3] = 'get';
			}

			// 默认值
			$value_default = $rule[0];
			// 数据类型
			$data_type = $rule[1];
			// 验证方法名
			$validator_method = $rule[2];
			// 获取数据的方法
			$get_data_method = $rule[3];

			// 获取提交来的数据
			$value = $this->_request->$get_data_method($gp);

			$search_by[$gp] = $value;

			// 转换数据类型
			switch ($data_type) {
				case 'string':
					$value = (string) $value;
					$value = trim($value);
				break;
				case 'int':
					$value = (int) $value;
				break;
				case 'array':
					$value = (array) $value;
				break;
				default:
					$value = (string) $value;
					$value = trim($value);
				break;
			}

			// 如果传递来的值与默认值一致，则不考虑
			if ($value == $value_default) {
				continue;
			}

			// 需要验证数据
			if ($validator_method) {
				if (!method_exists($this, $validator_method)) {
					$this->errmsg(100, 'method_not_exists');
					return false;
				}

				// 如果条件值未通过验证，则忽略
				if (!$this->$validator_method($value)) {
					continue;
				}
			}

			$format_condition[$gp] = $value;
		}

		return true;
	}
}
