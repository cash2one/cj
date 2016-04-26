<?php
/**
 * voa_c_api_base
 * 接口/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_base extends controller {

	/**
	 * 当前执行的模块名
	 * @var string
	 */
	protected $_api_module = '';

	/**
	 * 当前请求的方法
	 * @var string
	 */
	protected $_api_method = '';

	/**
	 * 当前请求的具体动作
	 * @var string
	 */
	protected $_api_action = '';

	/**
	 * 当前请求的参数
	 * @var array
	 */
	protected $_params = array();

	/**
	 * 当前GET请求的全部参数，仅做为动作辅助，不推荐经常性定义会导致接口定义混乱
	 * @var array
	 */
	protected $_get = array();

	/**
	 * 接收的数据参数名
	 * @var string
	 */
	private $_api_variable = 'json';

	/**
	 * 输出的错误代码
	 * @var number
	 */
	protected $_errcode = 0;

	/**
	 * 输出的错误消息
	 * @var string
	 */
	protected $_errmsg = 'ok';

	/**
	 * 输出的结果集
	 * @var array
	 */
	protected $_result = array();

	/**
	 * 插件配置信息
	 * @var array
	 */
	protected $_p_sets = array();

	/**
	 * 系统环境配置
	 * @var array
	 */
	protected $_setting = array();

	/**
	 * cookie储存名前缀
	 * @var string
	 */
	protected $_cookie_prefix = '';

	/**
	 * 登录认证 auth 的 cookie 储存名
	 * @var string
	 */
	protected $_auth_cookie_name = 'auth';

	/**
	 * 登录认证 uid 的 cookie 储存名
	 * @var string
	 */
	protected $_uid_cookie_name = 'uid';

	/**
	 * 最后登录时间的 cookie 储存名
	 * @var string
	 */
	protected $_lastlogin_cookie_name = 'lastlogin';

	/**
	 * 当前操作用户的用户信息
	 * @var array
	 */
	protected $_member = array();

	/** 当前请求输出的时间日期格式 */
	protected $_date_format = 'Y-m-d H:i';
	// 是否需要登录
	protected $_require_login = true;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 读取配置信息
		if (!voa_h_conf::init_db()) {
			$this->_set_errcode(voa_errcode_api_system::API_DB_ERROR);
			$this->_output();
			return false;
		}

		// 系统环境配置
		$this->_setting = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->_start_cookie();

		// 登录认证auth的cookie储存名
		// 测试的专用啊
		//$this->_cookie_prefix = voa_h_func::get_domain() . '_member_';
		$this->_cookie_prefix = '';

		$this->_auth_cookie_name = $this->_cookie_prefix.$this->_auth_cookie_name;

		// 登录认证uid的cookie储存名
		$this->_uid_cookie_name = $this->_cookie_prefix.$this->_uid_cookie_name;

		// 最后登录时间的cookie储存名
		$this->_lastlogin_cookie_name = $this->_cookie_prefix.$this->_lastlogin_cookie_name;

		// 初始化
		$this->_api_init();

		// 获取动作参数
		$this->_get_params();

		// 验证身份
		if ($this->_api_module != 'auth') {
			if (!$this->_access_check()) {
				// 如果不需要强制登录
				if (!$this->_require_login) {
					$this->_errcode = 0;
					$this->_errmsg = 'OK';
					return true;
				}

				$this->_output();
				exit;
			}
		}
		// 注意：以下禁止写入其他执行代码！！避免身份验证问题！！！
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);

		// 输出结果
		$this->_output();
		return true;
	}

	// cookie 初始化
	protected function _start_cookie() {

		$startup =& startup::factory();
		$session = $startup->get_option('session');

		if (is_string($session)) {
			$prefix = $session;
		} else if ($session) {
			$app_name = startup_env::get('app_name');
			$prefix = $app_name.'.session';
		} else {
			return false;
		}

		$domain = config::get($prefix.'.domain');
		$expired = config::get($prefix.'.expired');
		$public_key = empty($this->_setting['authkey']) ? config::get($prefix.'.public_key') : $this->_setting['authkey'];

		$this->session =& session::get_instance($domain, $expired, $public_key);
		ob_start(array($this->session, 'send'));
	}

	/**
	 * _start_session
	 * 启动session支持
	 *
	 * @return object
	 */
	protected function _start_session() {

		return null;
	}

	/**
	 * 初始化接口
	 * @return void
	 */
	protected function _api_init() {

		// 当前执行的模块名
		$this->_api_module = '';
		list(,,,$this->_api_module) = explode('_', $this->controller_name);

		// 当前执行的http的get请求参数
		$this->_get = $this->request->getx();

		// 当前http请求的方法 和 请求的动作
		// $this->action_name 后加“_”是为了避免$this->action_name意外没“_”的情况
		list($this->_api_method, $this->_api_action) = explode('_', $this->action_name.'_');
	}

	/**
	 * 身份检查
	 * @return void
	 */
	protected function _access_check() {

		$uda_member_get = &uda::factory('voa_uda_frontend_member_get');

		$cookie_names = array(
			'uid_cookie_name' => $this->_uid_cookie_name,
			'lastlogin_cookie_name' => $this->_lastlogin_cookie_name,
			'auth_cookie_name' => $this->_auth_cookie_name
		);

		// cookie 信息
		$cookie_data = array();
		if (!$uda_member_get->member_auth_by_cookie($cookie_data, $this->session, $cookie_names)) {
			// 无法取得当前用户的cookie信息
			return $this->_set_errcode(voa_errcode_api_system::API_ACCESS_NO_COOKIE, 1);
		}

		if (empty($cookie_data)) {
			return $this->_set_errcode(voa_errcode_api_system::API_ACCESS_NO_COOKIE, 2);
		}

		$this->_member = array();
		if (!$uda_member_get->member_info_by_cookie($cookie_data['uid'], $cookie_data['auth'], $cookie_data['lastlogin'], $this->_member)) {
			return $this->_set_errcode(voa_errcode_api_system::API_ACCESS_AUTH_ERROR);
		}

		startup_env::set('wbs_uid', $this->_member['m_uid']);
		startup_env::set('wbs_username', $this->_member['m_username']);
		startup_env::set('web_access_token', isset($this->_member['m_web_access_token']) ? $this->_member['m_web_access_token'] : '');
		startup_env::set('web_token_expires', isset($this->_member['m_web_token_expires']) ? $this->_member['m_web_token_expires'] : 0);

		return true;
	}

	/**
	 * 根据路由方法名来确定提交的请求方式，并获取相应提交的参数和数据
	 * @return void
	 */
	protected function _get_params() {

		switch (rstrtolower($this->_api_method)) {
			case 'get':
				$params = array();
				foreach ($_GET as $key => $value) {
					$params[$key] = $value;
				}
				$this->_params = $params;
				unset($params);
			break;
			case 'post':
				$params = array();
				foreach ($_POST as $key => $value) {
					$params[$key] = $value;
				}
				$this->_params = $params;
				unset($params);
			break;
			case 'put':
			case 'delete':
				$params = array();
				$input = file_get_contents('php://input');
				parse_str($input, $params);
				$this->_params = $params;
				unset($params, $input);
			break;
			default:
				$this->_set_errcode(voa_errcode_api_system::API_UNKNOWN);
				$this->_output();
			break;
		}

		return;
	}

	/**
	 * _get
	 * 获取请求的 params 参数
	 *
	 * @paramstring $key
	 * @parammixed $default key不存在时的默认值
	 * @return mixed
	 */
	protected function _get($key, $default = null) {

		if (array_key_exists($key, $this->_params)) {
			return $this->_params[$key];
		}

		return $default;
	}

	/**
	 * 赋值 错误代码 和 错误消息
	 * @param string $str errcode::CY_OK
	 * @param mixed $params1 ... 变量值
	 * @uses _set_errcode(errcode:CY_TEST, 'aa', 'bb', 'cc');
	 * @return void
	 */
	protected function _set_errcode($str) {

		call_user_func_array("voa_h_func::set_errmsg", func_get_args());

		$this->_errcode = voa_h_func::$errcode;
		$this->_errmsg = voa_h_func::$errmsg;

		return $this->_errcode ? false : true;
	}

	/**
	 * 输出结果
	 * @param number $errcode
	 * @param string $errmsg
	 * @param array $result
	 */
	protected function _output($errcode = 0, $errmsg = '', $result = array()) {

		// 输出 json 类型
		$this->response->set_header('Content-type', 'application/json;charset=utf-8');

		// 未所有数组型的输出增加一个输出：request_uid，标明当前的请求uid
		//if (is_array($this->_result) && !empty($this->_result) && !isset($this->_result['request_uid'])) {
		//	$this->_result['request_uid'] = $this->_member['m_uid'];
		//}

		// 输出结果
		$result = array(
			'errcode' => $this->_errcode,
			'errmsg' => $this->_errmsg,
			'timestamp' => startup_env::get('timestamp'),
			'result' => $this->_result
		);

		echo rjson_encode($result);

		return $this->response->stop();
	}

	/**
	 * 写入需要缓存的数据
	 * 用于对公共结果集的缓存方法，一般用于缓存与用户标记无关(所有人都一致)的数据
	 * @param array $data <strong style="color:red">(引用结果)</strong>待缓存的数据，请求缓存时不需要，更新缓存时需要
	 * @param string $nunique 缓存唯一标识符，请求缓存时需要，更新缓存时不需要
	 * @param array $param_allow 允许的请求参数数组
	 * @uses <p>一般在写公共数据返回时，在实际查询前调用此方法 $this->_api_common_cache($data, $unique); 引用$data结果数据，必须提供$unique。
	 * 更新缓存，在实际输出时所有查询之后再使用查询结果进行一次调用 $this->_api_common_cache($data, $unique)，必须提供$data</p>
	 * 请求此类缓存的接口，需额外增加一个参数：unique，用于提供给客户端的唯一标识字符串
	 * 返回此类缓存的接口，统一的返回格式为：array('unique' => string, 'data' => mixed)，其中data是真实的数据
	 * @return boolean
	 */
	protected function _api_common_cache(&$data, $unique = '', $param_allow = array()) {

		// 判断当前是否是强制请求最新
		$force_update = isset($this->_params['_api_force']) && $this->_params['_api_force'] ? true : false;

		if ($unique && $force_update) {
			// 强制更新，用于返回标记为必须更新
			return true;
		}

		// 公共api缓存数据表
		$serv_apicache = &service::factory('voa_s_oa_common_apicache');

		// 当前请求的有效参数数组
		$params = array();

		if ($param_allow) {
			// 如果定义了允许的参数数组，则对所有请求的参数进行过滤，
			// 避免被注入无用的参数导致同一真实的缓存数据被写入非常多的缓存

			foreach ($param_allow as $key) {
				if (isset($this->_params[$key])) {
					$params = $this->_params[$key];
				}
			}
		} else {
			// 未定义则认为所有传入的参数名都是合法有效的
			// 不建议这么使用！！！

			$params = $this->_params;
		}

		// 将唯一标识字符串自请求中移除
		unset($params['_api_unique'], $params['_api_force']);

		// 将当前的请求方法和模块也注入参数数组内
		$params['_api_module'] = $this->_api_module;
		$params['_api_method'] = $this->_api_method;
		$params['_api_action'] = $this->_api_action;

		// 将参数数组按字母顺序排序，避免同样参数因为排序顺序不同导致多个缓存
		natksort($params);

		// 将参数和值数组序列化并md5做为该条件请求的缓存名
		$name = md5(serialize($params));

		// 根据请求参数唯一标识字符串 和 数据唯一标识符来查询是否有缓存
		$cache = $serv_apicache->fetch_by_name($name);

		// 如果提供了数据唯一标识字符串，则尝试查询缓存
		if ($unique) {
			if (empty($cache) || $cache['cac_unique'] == $unique) {
				// 缓存不存在 或者 缓存数据未发生变动，则返回未更新
				return false;
			} else {
				// 缓存存在，则引用结果
				$data = array('unique' => $unique, 'list' => unserialize($cache['cac_data']));
				return true;
			}
		}

		// 将待缓存的数据进行序列化并md5做为该数据的唯一标识字符串
		$tmp = $data;
		if (is_array($tmp)) {
			natksort($tmp);
		}
		$unique = md5(serialize($tmp));
		unset($tmp);

		// 如果当前缓存数据与历史缓存数据一致，则不写入
		if (isset($cache['cac_unique']) && $unique == $cache['cac_unique'] && $force_update === false) {
			return true;
		}

		// 开始写入公共数据更新缓存
		$cache_data = array(
				'cac_unique' => $unique,
				'cac_name' => $name,
				'cac_data' => serialize($data),
				'cac_param' => serialize($params),
			);
		if ($cache) {
			$serv_apicache->update($cache_data, $cache['cac_id']);
		} else {
			$serv_apicache->insert($cache_data, false, true);
		}

		// 清理过期的缓存数据避免缓存表太大
		$serv_apicache->clear(86400);

		return true;
	}

	/**
	 * 基本变量检查和过滤方法
	 * @param array $fields = array(
	 * 	'变量名' => array(
	 * 		'type' => '',// 变量值类型
	 * 		'required' => boolean,// 是否为必须参数
	 * 	)
	 * 	... ...
	 * )
	 * 或者
	 * array(
	 * 	'变量名' => 变量值类型,
	 *	... ...
	 * )
	 * @return boolean
	 */
	protected function _check_params($fields) {
		foreach ($fields as $key => $rule) {
			if (!is_array($rule)) {
				$rule['type'] = $rule;
			}
			if (!isset($this->_params[$key])) {
				if (!isset($rule['required']) || $rule['required']) {
					$this->_set_errcode(voa_errcode_uc_system::UC_PARAM_LOSE, $key);
					$this->_output();
					break;
				} else {
					$this->_params[$key] = '';
				}
			}
			if (!isset($rule['type'])) {
				$rule['type'] = 'string';
			}
			switch (rstrtolower($rule['type'])) {
				case 'int':
					$this->_params[$key] = rintval($this->_params[$key], false);
					break;
				case 'number':
					if (!is_numeric($this->_params[$key])) {
						$this->_params[$key] = 0;
					}
					break;
				case 'array':
					$this->_params[$key] = (array)$this->_params[$key];
					break;
				case 'string_trim':
					$this->_params[$key] = (string)$this->_params[$key];
					$this->_params[$key] = trim($this->_params[$key]);
					break;
				default:
					$this->_params[$key] = (string)$this->_params[$key];
					break;
			}
		}

		// 请求了页码参数
		if (isset($this->_params['page'])) {
			$this->_params['page'] = trim((string)$this->_params['page']);
			if (!preg_match('/^[0-9]+$/', $this->_params['page'])) {
				$this->_set_errcode(voa_errcode_api_system::API_PARAM_PAGE_ERROR);
				$this->_output();
				return false;
			}
			$this->_params['page'] = (int)$this->_params['page'];
			if ($this->_params['page'] <= 0) {
				$this->_params['page'] = 1;
			}
		}

		// 请求了数据行数参数
		if (isset($this->_params['limit'])) {
			$this->_params['limit'] = trim((string)$this->_params['limit']);
			if (!preg_match('/^[0-9]+$/', $this->_params['limit'])) {
				$this->_set_errcode(voa_errcode_api_system::API_PARAM_LIMIT_ERROR);
				$this->_output();
				return false;
			}
			$this->_params['limit'] = (int)$this->_params['limit'];
			if ($this->_params['limit'] <= 0) {
				$this->_params['limit'] = 10;
			}
			if ($this->_params['limit'] > 500) {
				$this->_set_errcode(voa_errcode_api_system::API_PARAM_LIMIT_OVERFLOW);
				$this->_output();
				return false;
			}
		}

		// 标记请求的设备，则设置当前环境的日期格式
		if (isset($this->_params['device'])) {
			$this->_set_date_format($this->_params['device']);
		}

		return true;
	}

	/**
	 * 设置输出的时间日期格式
	 * @param string $device
	 * 来自 voa_d_oa_member_field 的常量成员定义
	 * 如果某个应用各个设备有特殊需求，请在对应应用api基类覆盖此方法
	 * @return boolean
	 */
	protected function _set_date_format($device) {

		switch ($device) {
			case voa_d_oa_member_field::DEVICE_ANDROID:
				// 安卓输出unix时间戳
				$this->_date_format = null;
				break;
			case voa_d_oa_member_field::DEVICE_IOS:
				// IOS输出unix时间戳
				$this->_date_format = null;
				break;
			default:
				// 默认输出Y-m-d H:i
				$this->_date_format = 'Y-m-d H:i';
				break;
		}

		return true;
	}

	/**
	 * 输出错误消息提醒
	 * @param mixed $h 异常抛出的错误对象 或者 自定义的错误编码
	 * @param string $custom_errmsg 自定义的错误提示文字，仅在 $h 为自定义编码时有效
	 * @param array $result 输出的结果集合（如果有）
	 * @example 存在如下使用场景：<pre>
	 * 异常错误抛出给用户的提示：_api_error_message($h);
	 * 使用错误编码库的错误：_api_error_message(voa_errcode_oa_xxx::ERROR[[,array()], var1, var2, var3 ....]);
	 * 完全自定义的错误：_api_error_message(xxx, '发生错误');
	 * 自定义的内部错误：_api_error_message(xxx);
	 * </pre>
	 * @return void
	 */
	public function _api_error_message($h, $custom_errmsg = '', $result = array()) {

		// 通过编码库定义的错误
		if (is_scalar($h) && strpos(':', $h) !== false) {

			// 获取给定的参数
			$values = func_get_args();
			// 移除错误编码字符串，其他参数则为错误编码的变量值和可能返回的结果集合
			unset($values[0]);
			// 如果不存在其他参数，则直接使用help方法获取错误信息
			if (empty($values)) {
				voa_h_func::set_errmsg($h);
				$this->_errcode = voa_h_func::$errcode;
				$this->_errmsg = voa_h_func::$errmsg;
				$this->_result = $result;
				return false;
			}

			/** 存在其他参数，则解析 */
			// 要返回的结果集
			$result = array();
			// 传递给错误编码解析的变量值
			$params = array();
			foreach ($values as $_param) {
				if (is_array($_param)) {
					// 数组，则为要返回输出的结果集
					$result = $_param;
				} else {
					// 给错误编码解析的变量值
					$params[] = $_param;
				}
			}
			$func = new voa_h_func();
			call_user_func_array(array($func, 'set_errmsg'), $params);
			$this->_errcode = voa_h_func::$errcode;
			$this->_errmsg = voa_h_func::$errmsg;
			$this->_result = $result;
			return false;
		}

		/** 使用异常抛出的错误对象 或 完全自定义错误方式 */

		// 自定义的错误编码
		if (!is_object($h) || !method_exists($h, 'getCode')) {
			$this->_errcode = $h;
			$this->_errmsg = $custom_errmsg ? $custom_errmsg.'[Error: '.$h.']' : '系统发生内部错误，错误编码：'.$h;
			$this->_result = $result;
			return false;
		}

		// 通过异常抛出的呈现给用户的错误提示信息（非系统错误）
		$this->_errcode = $h->getCode();
		$this->_errmsg = $h->getMessage().'[Error: '.$h->getCode().']';
		$this->_result = $result;
		return false;
	}

	/**
	 * 系统内部错误输出
	 * @todo 使用该方法时，一般推荐同时使用 logger::error($e); 记录日志
	 * @param mixed $e 异常抛出的错误对象 或者 自定义的错误编码
	 * @param string $custom_message 自定义的错误提示文字，仅在 $e 为自定义编码时有效
	 * @param array $result 输出的结果集合（如果有）
	 * @example 使用场景<pre>
	 * 异常错误抛出给用户提示（内部错误）：_api_system_message($e);
	 * 完全自定义的错误：_api_system_message(xxx, '发生错误');
	 * 自定义的内部错误：_api_system_message(xxx);
	 * @return void
	 */
	protected function _api_system_message($e, $custom_message = '', $result = array()) {

		// 自定义的错误编码
		if (!is_object($e) || !method_exists($e, 'getCode')) {
			$this->_errcode = $e;
			$this->_errmsg = $custom_message ? $custom_message : '系统发生内部错误，错误编码：'.$e;
			$this->_result = $result;
			return false;
		}

		// 如果是开发环境则显示具体的系统错误信息
		$error_detail = array();
		if (isset($_SERVER['RUN_MODE']) && $_SERVER['RUN_MODE'] == 'development') {
			$error_detail[] = "\n\n************************************************\n";
			$error_detail[] = "File: ".(is_array($e->getFile()) ? implode('; ', $e->getFile()) : $e->getFile());
			$error_detail[] = "Line: ".(is_array($e->getLine()) ? implode('; ', $e->getLine()) : $e->getLine());
			$error_detail[] = "Error: ".print_r($e->getMessage(), true)."\n";
			$error_detail[] = "Previous: ".(is_array($e->getPrevious()) ? implode('; ', $e->getPrevious()) : $e->getPrevious());
			$error_detail[] = "Trace: \n".(is_array($e->getTraceAsString()) ? implode("#\n", $e->getTraceAsString()) : $e->getTraceAsString());
			$error_detail[] = "\n################################################";
		}
		$error_detail = implode("\n", $error_detail);
		if ($error_detail) {
			$error_detail = "\n\n<pre>\n".nl2br(rhtmlspecialchars($error_detail))."\n</pre>";
		}

		// 系统错误：通过异常抛出的不呈现给用户的内部错误提示
		$errcode = $e->getCode();
		if (!$errcode) {
			$errcode = -9999;
		}
		$errmsg = '操作失败，系统发生内部错误，错误编码：'.$errcode.$error_detail;
		$this->_errcode = $errcode;
		$this->_errmsg = $errmsg;
		$this->_result = $result;

		return false;
	}

	/**
	 * 获取微信 openid
	 * @param string $openid 微信 openid
	 * @return boolean
	 */
	protected function _get_wx_openid(&$openid) {

		// 如果用户信息不存在, 则
		if (empty($this->_member)) {
			return false;
		}

		// 如果微信 openid 存在
		if (!empty($this->_member['wx_openid'])) {
			$openid = $this->_member['wx_openid'];
			return true;
		}

		// 获取微信 openid
		if (!$this->_get_openid_by_userid($openid, $this->_member['m_openid'])) {
			return false;
		}

		// 更新微信 openid
		$serv = &service::factory('voa_s_oa_member');
		$serv->update(array('wx_openid' => $openid), $this->_member['m_uid']);
		$this->_member['wx_openid'] = $openid;
		return true;
	}

	/**
	 * 根据 userid 来获取 openid
	 * @param string $openid 微信的 openid
	 * @param string $userid 企业号的 userid
	 */
	protected function _get_openid_by_userid(&$openid, $userid) {

		$serv = &service::factory('voa_wxqy_service');
		return $serv->convert_to_openid($openid, $userid, startup_env::get('agentid'));
	}

}
