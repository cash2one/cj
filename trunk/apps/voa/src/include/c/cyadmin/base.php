<?php
/**
 * voa_c_cyadmin_base
 * 主站后台/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_base extends controller {

	/** 当前管理员信息 */
	protected $_user = array();

	/** 后台用户所在用户组信息 */
	protected $_usergroup = array();

	/** 后台管理员活跃时间，超出此时间将更新最后登录时间，单位：秒 */
	protected $_user_ttl = 900;

	/** 模块列表 */
	protected $_module_list = array();

	/** 主业务, 二级菜单列表 */
	protected $_operation_list = array();

	/** 子业务, 三级菜单列表 */
	protected $_subop_list = array();

	/** 模块/主业务/自业务的默认操作 */
	protected $_default_list = array();

	/** 模块 */
	protected $_module = '';

	/** 主业务 */
	protected $_operation = '';

	/** 子业务 */
	protected $_subop = '';

	/** 具体业务上方的标签式导航链接菜单 */
	protected $_navmenu = array(
			'title'=>'',//当前业务名称
			'links'=>array(),//菜单链接名
			'right'=>'',//右侧快捷菜单，为空则显示返回按钮
	);

	/** 全局系统环境配置 */
	protected $_setting = array();

	/** 所有的应用ID */
	protected $_domain_plugin = array();

	/** 分组应用列表 */
	protected $_domain_plugin_list = array();

	/** cookie存储名 */
	protected $_auth_cookie_names = array(
		'username' => 'username',
		'skeycp' => 'skeycp',
		'adminer_remember' => 'adminer_remember'
	);

	/**
	 * 动作执行之前触发
	 * @see controller::_before_action()
	 */
	protected function _before_action($action) {

		// 全局系统环境配置
		$this->_setting = voa_h_cache::get_instance()->get('setting', 'cyadmin');
		$this->_start_cookie();
		$this->view->set('setting', $this->_setting);

		if (!parent::_before_action($action)) {
			return false;
		}

		// 为验证cookie存储名增加前缀
		$domain = voa_h_func::get_domain();
		foreach ($this->_auth_cookie_names as $_key => &$_value) {
			$_value = $domain.'_'.$_value;
		}

		// 判断是否 ajax 请求
		$ajax = intval($this->request->get('ajax'));
		$this->view->set('ajax', $ajax);

		// 设置静态文件url根目录
		$this->view->set('static_url', APP_STATIC_URL);

		// 初始化变量到模板环境
		$this->view->set('module', 'home');
		$this->view->set('operation', '');
		$this->view->set('subop', '');
		$this->view->set('navmenu', '');

		// 登录检查 login 除外
		if (in_array($this->action_name, array('login', 'logout'))) {
			return true;
		}

		// 如果未登陆
		if (!$this->_is_login()) {
			$this->redirect($this->cpurl('login'));
			return false;
		}

		// 检查所在管理组权限
		$this->_is_cpgroup();
		// 获取权限菜单
		$this->_get_cpmenu();
		// 检查模块/主业务/子业务
		$this->_check_moso();
		// 获取浏览器标题
		$this->_get_moso_nav_title();
		// 将业务上方导航变量写入模板环境
		$this->view->set('navmenu', $this->_navmenu);
		// 基类对象
		$this->view->set('base', $this);
		// 扩展的样式表文件组
		$this->view->set('css_extend_files', array());

		$this->view->set('notification_total', $this->_get_notification_app_total());
		$ajax_get_notification_app_url = $this->cpurl('enterprise', 'company', 'list', array('get_app_notification'=>1));
		$this->view->set('ajax_get_notification_app_url', $ajax_get_notification_app_url);

		$ajax_get_notification_total_url = $this->cpurl('enterprise', 'recbill', 'edit', array('ajax'=>1));

		$this->view->set('ajax_get_notification_total_url', $ajax_get_notification_total_url);

		$this->view->set('enterprise_app_url', $this->cpurl('enterprise', 'company', 'edit', array('id'=>'')));

		$this->view->set('bill_url', $this->cpurl('enterprise', 'recbill', 'edit', array('id'=>'')));
		$this->view->set('card_url', $this->cpurl('enterprise', 'reccard', 'edit', array('id'=>'')));


		$this->view->set('notification_bill_total', $this->_get_notification_bill_total());

		$this->view->set('notification_card_total', $this->_get_notification_card_total());

		$this->view->set('ext_job', $this->_user['ca_job']);

		$this->view->set('sell_man', $this->_get_epids()); // 当前负责人负责的企业

		$this->view->set('notification_overdue_total', $this->_get_notification_overdue_total());

		return true;
	}

	/**
	 * 动作执行之后触发执行
	 * @see controller::_after_action()
	 */
	protected function _after_action($action) {

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
	 * 判断是否登录
	 * @param string $username 登录名
	 * @param string $passwd 登录密码
	 * @param string $adminer_remember 是否记住cookie保持登录，null则按原cookie纪录方式操作
	 * @return boolean
	 */
	protected function _is_login($username = '', $passwd = '', $adminer_remember = null) {

		// 如果未提供登录名，则试图自 cookie 获取当前登录的用户
		if (empty($username)) {
			$username = trim($this->session->getx($this->_auth_cookie_names['username']));
		}

		// 根据用户名读取用户信息
		list($user, $usergroup) = $this->_get_user($username);
		if (empty($user) || empty($usergroup)) {
			return false;
		}

		// 生成 skey
		$skey = $this->_generate_skey($username, $user['ca_password']);
		// 如果当前密码为空, 则
		if (empty($passwd)) {
			// 和 cookie 中的验证串比对
			if ($this->_get_current_skey() != $skey) {
				return false;
			}
		} else {
			// 比对用户密码
			if ($user['ca_password'] != $this->_generate_passwd($passwd, $user['ca_salt'])) {
				return false;
			}
		}

		// 用户数据推入成员变量
		$this->_user = $this->_filter_user_secret_field($user, array('ca_password'));
		$this->_usergroup = $usergroup;
		// 记住登录一周
		$weekSecond = 86400 * 7;

		if ($adminer_remember !== null) {
			// 设置了是否保持登录
			$cookieLife = $adminer_remember ? $weekSecond : 0;
		} else {
			// 未设置则使用原cookie来判断
			// 未避免“永远”会保持1周登录，则将cookie周期设置为原始的2/3
			$cookieLife = $this->session->getx($this->_auth_cookie_names['adminer_remember']) > 0 ? $weekSecond : 0;
		}

		// 写入 cookie 信息
		$this->session->setx($this->_auth_cookie_names['username'], $username, $cookieLife > 0 ? ($cookieLife + ceil($cookieLife * 2/3)) : 0);
		$this->session->setx($this->_auth_cookie_names['skeycp'], $skey, $cookieLife);
		$this->session->setx($this->_auth_cookie_names['adminer_remember'], $cookieLife, 86400 * 365);
		unset($cookieLife);

		// 当前时间距离最后更新时间超过15分钟，则更新其最后登录时间
		$timestamp = startup_env::get('timestamp');
		if ($timestamp - $user['ca_updated'] > $this->_user_ttl) {
			$serv = &service::factory('voa_s_cyadmin_common_adminer', array('pluginid' => 0));
			$serv->update(array(
				'ca_lastlogin'=>$timestamp,
				'ca_lastloginip'=>$this->request->get_client_ip()
			), $user['ca_id']);
		}

		return true;
	}

	/**
	 * 根据登录名来获取管理员信息
	 * @param string $username
	 * @return array(string|false, array|false)
	 */
	protected function _get_user($username) {
		$serv = &service::factory('voa_s_cyadmin_common_adminer', array('pluginid' => 0));
		$user = $serv->fetch_by_username($username);
		if (empty($user)) {
			return array(false, false);
		}
		$usergroup = $this->_get_usergroup($user['cag_id']);
		return array($user, $usergroup);
	}

	/**
	 * 获取指定管理组信息
	 * @param number $cag_id
	 * @return boolean
	 */
	protected function _get_usergroup($cag_id) {
		if (!$cag_id) {
			return false;
		}
		$serv = &service::factory('voa_s_cyadmin_common_adminergroup', array('pluginid' => 0));
		return $serv->fetch($cag_id);
	}

	/**
	 * 生成验证登陆用的 skey
	 * @param string $username 用户名
	 * @param string $passwd 密码
	 * @return string
	 */
	protected function _generate_skey($username, $passwd) {
		return md5($username.$passwd);
	}

	/**
	 * 生成数据表中的用户密码,
	 * @param string $passwd 用户提交的密码
	 * @param string $salt 干扰字串
	 * @return string
	 */
	protected function _generate_passwd($passwd, $salt) {
		return md5($passwd.$salt);
	}

	/**
	 * 过滤管理员信息中安全的字段
	 * @param array $user
	 * @param array $fields
	 * @return array
	 */
	protected function _filter_user_secret_field($user, $fields = array()) {
		if (empty($fields)) {
			return $user;
		}
		// 剔除保密信息
		foreach ($fields as $f) {
			unset($user[$f]);
		}
		return $user;
	}

	/**
	 * 判断当前后台管理组的权限
	 * @return NULL
	 */
	protected function _is_cpgroup() {
		$usergroup = $this->_usergroup;
		if (!$usergroup) {
			$this->_error_message('无效的授权访问后台管理');
			return null;
		}
		if (voa_d_cyadmin_common_adminergroup::ENABLE_NO == $usergroup['cag_enable']) {
			$this->_error_message('您所在管理组禁止访问后台，请联系管理员解决');
		}
		return null;
	}

	/**
	 * 获取当前验证字符串
	 * @return string
	 */
	protected function _get_current_skey() {
		return trim($this->session->getx($this->_auth_cookie_names['skeycp']));
	}

	/**
	 * 获取当前有权限的管理菜单
	 */
	protected function _get_cpmenu() {
		// 全部菜单列表
		$cpmenu = voa_h_cache::get_instance()->get('cpmenu', 'cyadmin');
		// 用户有权限的菜单id
		switch ($this->_usergroup['cag_enable']) {
			case voa_d_cyadmin_common_adminergroup::ENABLE_SYS :
				$ids = true;
				break;
			case voa_d_cyadmin_common_adminergroup::ENABLE_YES :
				$ids = explode(',', $this->_usergroup['cag_role']);
				break;
			default :
				$ids = array();
		}

		// 读取后台权限菜单缓存
		$defaults = array();
		foreach ($cpmenu as $menu) {

			// 当前没权限使用的功能
			if ($ids !== true && !in_array($menu['id'], $ids)) {
				continue;
			}

			// 被隐藏了的功能
			if (!$menu['display']) {
				continue;
			}

			// 模块(一级菜单)
			if ('module' == $menu['type']) {
				$this->_module_list[$menu['module']] = $menu;
			} elseif ('operation' == $menu['type']) {
				// 二级菜单(左侧菜单)
				$this->_operation_list[$menu['module']][$menu['operation']] = $menu;
			} elseif ('subop' == $menu['type']) {
				// 三级菜单
				$this->_subop_list[$menu['module']][$menu['operation']][$menu['subop']] = $menu;
			}

			// 定义动作的默认菜单
			if ($menu['default']) {
				if (!isset($defaults['module'])) {
					$defaults['module'] = $menu['module'];
				}
				if (!isset($defaults['operation'][$menu['module']])) {
					$defaults['operation'][$menu['module']] = $menu['operation'];
				}
				if (!isset($defaults['subop'][$menu['module']][$menu['operation']])) {
					$defaults['subop'][$menu['module']][$menu['operation']] = $menu;
				}
			}
		}
		$this->_default_list = $defaults;

		// 移除无下级的第一级菜单
		foreach ($this->_module_list AS $_module => $_array) {
			if (1 > count(@$this->_subop_list[$_module])) {
				unset($this->_module_list[$_module], $this->_operation_list[$_module]);
			}
		}

		// 导入模板变量
		$this->view->set('cpmenu_list', $cpmenu);
		$this->view->set('module_list', $this->_module_list);
		$this->view->set('operation_list', $this->_operation_list);
		$this->view->set('subop_list', $this->_subop_list);
		$this->view->set('default_list', $this->_default_list);
	}

	/**
	 * 检查模块/主业务/子业务
	 */
	protected function _check_moso() {
		// 获取当前的operation 和 subop
		if (strpos($this->action_name,'_') !== false) {
			@list($this->_operation, $this->_subop) = explode('_', $this->action_name);

			// 获取当前module
			list($this->_module) = explode('_',str_replace('voa_c_cyadmin_', '', $this->controller_name));

			// 检查主菜单权限
			if (!isset($this->_module_list[$this->_module])) {
				$this->_module = $this->_default_list['module'];

			}
			// 检查主业务

			if (!isset($this->_operation_list[$this->_module][$this->_operation])) {
				$this->_operation = $this->_default_list['operation'][$this->_module];
			}
			// 具体子业务

			if (!isset($this->_subop_list[$this->_module][$this->_operation][$this->_subop])) {

				$menu = $this->_default_list['subop'][$this->_module][$this->_operation];
				// 跳转到默认业务链接
				$this->message('success', '', $this->cpurl($menu['module'], $menu['operation'], $menu['subop']), true);
			}

		} else {
			//公共模块
			$this->_module = str_replace('voa_c_cyadmin_', '', $this->controller_name);
			$this->_operation = '';
			$this->_subop = $this->action_name;
		}

		// 模板变量
		$this->view->set('module', $this->_module);
		$this->view->set('operation', $this->_operation);
		$this->view->set('subop', $this->_subop);
	}

	/**
	 * 获取模块/主业务/子业务的导航标题
	 */
	protected function _get_moso_nav_title() {
		$titles = array();
		if ($this->_subop && isset($this->_subop_list[$this->_module][$this->_operation][$this->_subop])) {
			$titles[] = $this->_subop_list[$this->_module][$this->_operation][$this->_subop]['name'];
		}
		if ($this->_operation && isset($this->_operation_list[$this->_module][$this->_operation])) {
			$titles[] = $this->_operation_list[$this->_module][$this->_operation]['name'];
		}
		if ($this->_module && isset($this->_module_list[$this->_module])) {
			$titles[] = $this->_module_list[$this->_module]['name'];
		}
		$this->view->set('nav_title', implode(' - ', $titles));
	}

	/**
	 * 判断是否为提交，验证formhash
	 * @return boolean
	 */
	protected function _is_post() {
		if (!$this->request->is_post()) {
			return false;
		}
		if (!voa_h_form_hash::check('', $this->request->post('formhash'))) {
			return false;
		}
		return true;
	}

	/**
	 * 输出模板
	 * @param string $tpl 模板文件路径
	 * @param string $return 是否输出
	 */
	public function output($tpl, $return = false) {

		// 当前时间戳
		$this->view->set('timestamp', startup_env::get('timestamp'));

		// 输出当前用户信息
		$this->view->set('user', $this->_user);

		// 输出当前用户组
		$this->view->set('usergroup', $this->_usergroup);

		// 输出 forumhash
		$this->view->set('formhash', $this->_generate_form_hash());

		if ($return !== false) {
			return $this->view->render($tpl, true);
		}

		$this->view->render($tpl);

		return $this->response->stop();
	}

	/**
	 * 生成 formhash 字符串
	 * @return string
	 */
	protected function _generate_form_hash() {
		$sets = $this->_setting;
		$fh_key = $this->request->server('HTTP_HOST').(isset($sets['formhash_key']) ? $sets['formhash_key'] : '');
		if (!empty($this->_user)) {
			$fh_key .= $this->_user['ca_id'].$this->_user['ca_username'];
		}
		return voa_h_form_hash::generate($fh_key);
	}

	/**
	 * _ajax_message
	 * 返回ajax信息
	 * @param integer $code 0是成功，非0是失败。失败时，不同数字代表不同失败类型。
	 * @param string $message 成功消息或失败消息
	 * @param array $result 结果
	 * @return string
	 */
	protected function _ajax_message($code, $message = null, $result = array(), $jsonp = false, $callback = '') {
		if ($message === null) {
			if ($code > 0) {
				$message = '操作失败';
			} else {
				$message = '操作成功';
			}
		}
		$code = intval($code);
		if (!$code) {
			$code = 0;
		}
		if ($jsonp) {
			// referer 检查
			$jsonpWhiteRule = config::get('WeLife.home.jsonp_white_rule');
			if (!$jsonpWhiteRule) {
				$jsonpWhiteRule = '/^http[s]*\:\/\/[\w\.\-]*life\.qq\.com(\/|$)/i';
			}
			$referer = $this->request->server('HTTP_REFERER');
			if (!$referer || !preg_match($jsonpWhiteRule, $referer)) {
				$result['result'] = array();
				$result['code'] = 403;
				$result['message'] = 'Access denied';
			}
			return $this->response->append_body($callback.'('.rjson_encode($result).')');
		} else {
			return $this->response->append_body(rjson_encode($result));
		}
	}

	/**
	 * _success_message
	 * 成功消息提示
	 * @param  mixed $message
	 * @param  mixed $title
	 * @param  mixed $extra
	 * @param  mixed $redirect
	 * @param  mixed $url
	 * @param  string $tpl
	 * @return void
	 */
	protected function _success_message($message = null, $title = null, $extra = null,
			$redirect = false, $url = null, $tpl = 'message') {
		return $this->_message('success', $message, $title, $extra, $redirect, $url, $tpl);
	}

	/**
	 * _error_message
	 * 失败消息提示
	 * @param  mixed $message
	 * @param  mixed $title
	 * @param  mixed $extra
	 * @param  mixed $redirect
	 * @param  mixed $url
	 * @param  string $tpl
	 * @return void
	 */
	protected function _error_message($message = null, $title = null, $extra = null,
			$redirect = false, $url = null, $tpl = 'error') {
		return $this->_message('error', $message, $title, $extra, $redirect, $url, $tpl);
	}

	/**
	 * _message
	 * 消息提示
	 * @param  string $type
	 * @param  mixed $message
	 * @param  mixed $title
	 * @param  mixed $extra
	 * @param  mixed $redirect
	 * @param  mixed $url
	 * @param  string $tpl
	 * @return void
	 */
	protected function _message($type = 'success', $message = null, $title = null, $extra = null,
			$redirect = false, $url = null, $tpl = 'message') {

		if ($type == 'success') {
			if (!$title) {
				$title = '成功';
			}
			if (!$message) {
				$message = '操作已成功';
			}
		} else {
			if (!$title) {
				$title = '失败';
			}
			if (!$message) {
				$message = '操作失败';
			}
		}
		if (!$url && $url !== false) {
			// 检查来源链接，不合法的跳转到首页
			$referer = $this->request->server('HTTP_REFERER');
			if (!$referer || !preg_match('/^[htps]+\:\/\/(bbs\.|)life\.qq\.com/', $referer)) {
				$url = '/';
				if (defined('APP_DIRNAME') && APP_DIRNAME) {
					$url .= APP_DIRNAME.'/';
				}
			} else {
				$url = $referer;
			}
		}
		$this->view->set('title', $title);
		$this->view->set('redirect', $redirect);
		$this->view->set('url', $url);
		$this->view->set('js_url', str_replace('&amp;', '&', $url));
		$this->view->set('message', $message);
		$this->view->set('extra', $extra);
		$this->view->set('type', $type);
		return $this->output('cyadmin/'.$tpl);
	}

	/**
	 * 提示信息
	 * @param succees|error $type
	 * @param string $message
	 * @param string $url
	 * @param string $redirect
	 * @param array $extra
	 */
	public function message($type, $message, $url = '', $redirect = false, $extra = array()) {
		if ($url && $redirect === true) {
			// 不显示提示信息，直接header跳转
			@header("Location: ".rhtmlspecialchars($url));
			exit;
		}
		$this->_message($type, $message, '操作提示', $extra, $redirect, $url, 'message'.$type);
	}

	/**
	 * 构造后台访问URL
	 * @author Deepseath
	 * @param string $module
	 * @param string $operation
	 * @param string $subop
	 * @param array|boolean $extParam
	 * @param boolean $htmlEncode
	 * @param ....
	 * @return string
	 */
	public function cpurl($module = '', $operation = '', $subop = '', $extParam = array(), $htmlEncode = true) {

		if ($module && $operation && $subop && !isset($this->_subop_list[$module][$operation][$subop])) {
			return false;
		}
		$comma = $htmlEncode ? '&amp;' : '&';
		$url = '/';
		if (defined('APP_DIRNAME') && APP_DIRNAME) {
			//如果使用了目录方式，则加一级目录
			$url .= APP_DIRNAME.'/';
		}
		if ($module != '') {
			$url .= $module.'/';
			if (isset($this->_module_list[$module]) && $operation != '') {
				$url .= $operation.'/';
				if ($subop != '') {
					$url .= $subop.'/';
				}
			}
		}

		// 存在扩展参数
		if ($extParam) {
			$url .= '?';

			// 扩展参数是数组形式定义的
			if (is_array($extParam)) {
				$urlComma = '';
				foreach ($extParam AS $key=>$value) {
					if (is_scalar($value)) {
						$url .= $urlComma.$key.'='.$value;
					} else {
						foreach ($value as $_value) {
							$url .= $urlComma.$key.'[]='.$_value;
							$urlComma = $comma;
						}
					}
					$urlComma = $comma;
				}
			} else {

				// 如果extParam是一个布尔值，则认为传入的是多参url变量和值
				if (is_bool($extParam)) {
					// extParam 用来定义htmlEncode
					$htmlEncode = $extParam;
					// extParam 后的参数都认为是url的变量与值
					// 变量名，值，变量名，值 ... 的顺序
					$arrayKeyValue = array_slice(func_get_args(), 4);
					// 第一个参数是变量名
					$isKey = true;
					$urlComma = '';
					// 循环参数数组
					foreach ($arrayKeyValue AS $v) {
						if ($isKey === true) {
							$isKey = false;
							// url变量名
							$url .= $urlComma.$v;
							$urlComma = $comma;
						} else {
							$isKey = true;
							// url变量值
							$url .= '='.($htmlEncode ? urlencode($v) : $v);
						}
					}
					// 传入的是已经构造好了的url字符串，则直接使用
				} else {
					$url .= $extParam;
				}
			}
		}
		return $url;
	}

	/**
	 * 显示一个链接
	 * @param string $link
	 * @param string $linkExtraParam
	 * @param string $text
	 * @param string $icon
	 * @param string $extAttr
	 * @return string
	 */
	public function show_link($link, $linkExtraParam, $text, $icon = '', $extAttr = '') {
		if ($icon) {
			$icon = '<i class="fa '.$icon.'"></i> ';
		}
		if ($link) {
			$link .= $linkExtraParam;
			return '<a href="'.$link.'"'.($extAttr ? ' '.$extAttr : '').'>'.$icon.$text.'</a>';
		} else {
			return '<span class="disabled">'.$icon.$text.'</span>';
		}
	}
	/**
	 * 格式化企业应用信息
	 * @param array $item
	 * @return array
	 */
	protected function _app_format($item) {
		//<!--应用维护状态-->
		$status_text = array('待建立', '待删除', '待关闭', '已建立', '已删除', '已关闭');
		$item['_updated'] = rgmdate($item['ea_updated'], 'Y-m-d');
		$item['_created'] = rgmdate($item['ea_created'], 'm-d');
		$item['ea_appstatus_text'] = $status_text[$item['ea_appstatus']];
		$profile = $this->_profile_get($item['ep_id']);
		$item['ea_enterprise_name'] = $profile['ep_name'];
		//$profile['_role'] = explode(',', $profile['ep_role']);

		return $item;
	}


	protected function _profile_get($ep_id){
		$profile = $this->_serv_profile->fetch($ep_id);

		return $this->_profile_format($profile);
	}

	/**
	 * 获取应用通知信息
	 * @param void
	 * @return array()
	 */
	protected function _get_notification_app_total() {
		$serv = &service::factory('voa_s_cyadmin_enterprise_app', array('pluginid' => 0));
		$num = $serv->fetch_all_notification_total();

		return $num;
	}

	/**
	 * 获取应用通知信息
	 * @param void
	 * @return array()
	 */
	protected function _get_notification_app() {
		$serv = &service::factory('voa_s_cyadmin_enterprise_app', array('pluginid' => 0));
		$list = $serv->fetch_all_notification();
		foreach ($list as $k=>$v) {
			$list[$k] = $this->_app_format($v);
		}

		return $list;
	}

	protected function _get_notification_bill_total() {
		$serv = &service::factory('voa_s_cyadmin_recognition_bill', array('pluginid' => 0));
		$total = $serv->count_by_conditions(array());

		return $total;

	}

	protected function _get_reccard_total_all() {
		$serv = &service::factory('voa_s_cyadmin_recognition_namecard', array('pluginid' => 0));

		$condi['ca_id'] = '';
		$total = $serv->count_by_conditions($condi);

		return $total;

	}

	protected function _get_recbill_total_all() {
		$serv = &service::factory('voa_s_cyadmin_recognition_bill', array('pluginid' => 0));
		$condi['ca_id'] = '';
		$total = $serv->count_by_conditions($condi);

		return $total;

	}

	protected function _get_notification_card_total() {
		$serv = &service::factory('voa_s_cyadmin_recognition_namecard', array('pluginid' => 0));

		$total = $serv->count_by_conditions(array());

		return $total;

	}

	protected function _get_epids() {

		$ca_id = $this->_user['ca_id']; // 根据当前登录的ca_id 查询企业名下企业信息
		$profile = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$conds = array('ca_id = ?' => $ca_id);
		$re_data = $profile->list_by_conds($conds);
		if (!empty($re_data)) {
			return array_column($re_data, 'ep_name');
		} else return array();


	}

	protected function _get_notification_overdue_total() {

		$user = $this->_user;
		$act = (int)$user['ca_job'];
		$uid = (int)$user['ca_id'];
		$serv = &service::factory( 'voa_s_cyadmin_enterprise_overdue' );

		$dueread = &service::factory('voa_s_cyadmin_enterprise_dueread');

		$dueread->get_dueread_data($uid, $read_data);

		if (!empty($read_data)) {
			$read_data = array_column($read_data, 'ovid');
			$read_data = array_unique($read_data); // 进行过滤去重
		}
		//获取总页数
		if (in_array($act, array(0,1))) {
			$total   = $serv->count();
			if (false == $read_data) $read_data = null; // 修复bug

			$total -= count($read_data);
		} elseif (2 == $act) { // 销售人员的消息提醒
			$over_due = &service::factory( 'voa_uda_cyadmin_enterprise_overdue' );
			$epids = $over_due->list_epids($uid); // 销售对应的企业ids 多个企业
			$conds = array('ovid NOT IN (?)' => $read_data, 'epid IN (?)' =>$epids );
			$total   = $serv->count_by_conds($conds); // 获取真正的总数
		}

		return $total;
	}


	protected function _get_img_url($id){

		return config::get('voa.main_url').'attachment/read/'.$id;
	}
}
