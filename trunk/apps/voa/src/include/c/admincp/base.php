<?php
/**
 * voa_c_admincp_base
 * 基本控制器 /base/
 *
 * $Author$
 * $Id$
 */
class voa_c_admincp_base extends controller {

	/** 总后台试用期状态 */
	const PROBATION = 7;
	/** OA后台已付费状态 */
	const ADMINCP_PAID = 1;
	/** OA后台试用期状态 */
	const ADMINCP_PROBATION = 2;
	/** 标准服务 */
	const STANDARD_SERVICE = 1;
	/** 定制产品 */
	const CUSTOMIZED_SERVICE = 2;
	/** 私有部署 */
	const PRIVATE_DEPLOYMENT = 3;
	/** 套件关闭状态 */
	const OFF_STATUS = 1;
	/** 套件开启状态 */
	const OPEN_STATUS = 0;
	/** 用户微信关注 状态 */
	const WX_FOCUS_STATUS = 1;

	// 独立安装
	const INSTALL_MODE_ALONE = 2;
	// 标准安装
	const INSTALL_MODE_STANDARD = 1;

	/** 静态文件根目录URL */
	public $staticdir = '';
	/** 图片文件根目录url */
	public $imgdir = '';
	/** css样式文件根目录 */
	public $cssdir = '';
	/** js样式文件根目录 */
	public $jsdir = '';
	/** 静态文件版本号 */
	public $static_version = '';

	/** 前端静态目录 */
	public $fm_staticdir = '';
	/** 前端图片目录 */
	public $fm_imgdir = '';
	/** 前端样式目录 */
	public $fm_cssdir = '';
	/** 前端js目录 */
	public $fm_jsdir = '';
	/** 前端js框架目录 */
	public $fm_jsframework = '';
	/** 前端js版本号 */
	public $fm_static_version = '';

	/** 模板目录名 */
	public $tpl_dir_base = '';
	/** 扩展的自定义页面head内容 */
	protected $_expand_head = '';
	/** 扩展的自定义数组，标记页面头部需要引入的js文件 */
	protected $_expand_js = array();
	/** 扩展的自定义数组，标记页面头部需要引入的css文件 */
	protected $_expand_css = array();
	/** 扩展的自定义数据，标记页面底部需要引入的js文件 */
	protected $_expand_js_foot = array();
	/** 扩展的自定义页面底部内容 */
	protected $_expand_foot = '';

	/** (admincp/base) 当前用户信息 */
	protected $_user = array();
	/** (admincp/base) 后台用户所在用户组信息 */
	protected $_usergroup = array();
	/** (admincp/base) 模块 */
	protected $_module_list = array();
	/** (admincp/base) 模块所关联的插件id */
	protected $_module_plugin_id = 0;
	/** (admincp/base) 模块所关联的插件信息 */
	protected $_module_plugin = array();
	/** (admincp/base) 主业务, 二级菜单 */
	protected $_operation_list = array();
	/** (admincp/base) 子业务, 三级菜单 */
	protected $_subop_list = array();
	/** (admincp/base) 模块/主业务/自业务的默认操作 */
	protected $_default_list = array();
	/** (admincp/base) 模块/主业务/子业务 */
	protected $_module = '';
	protected $_operation = '';
	protected $_subop = '';
	/** (admincp/base) 具体业务上方的标签式导航链接菜单 */
	protected $_navmenu = array(
		'title'=>'',//当前业务名称
		'links'=>array(),//菜单链接名
		'right'=>'',//右侧快捷菜单，为空则显示返回按钮
	);
	/** 后台管理员活跃时间，超出此时间将更新最后登录时间，单位：秒 */
	protected $_user_ttl = 900;
	/** 通讯录与用户表需要保持一致的字段 */
	protected $_user_synch_fields = array('_mobilephone','_number','_gender','cd_id','cj_id',);
	/** 是否是ajax提交 */
	protected $_is_ajax = false;
	/** 系统环境变量 */
	protected $_setting = array();

	/** 记住登录管理员cookie的信息 */
	protected $_cookie_remember_adminer_name = 'remember_adminer';
	protected $_cookie_adminer_username_name = 'adminer';

	/** 定义微信的名词 */
	protected $_wechat_noun_list = array();
	/** 指定了来路URL */
	protected $_referer = '';
	/** 是否为API模式 */
	protected $_is_api = false;

	/**总后台应用设置信息*/
	protected $_appset_pub = array();
	/** 企业的各种付费状态 */
	protected $_qystates = array(
		1 => '已付费',
		2 => '已付费-即将到期',
		3 => '已付费-已到期',
		5 => '试用期-即将到期',
		6 => '试用期-已到期',
		7 => '试用期'
	);
	/** 产品种类 */
	protected $pay_types = array(
		1 => '标准产品',
		2 => '定制产品',
		3 => '私有部署'
	);
	/** 企业使用人数 */
	protected $_member_count = '';

	/**
	 * 封装了的s数据控制器单条操作（该方法即将废除，请不要再使用！）
	 * @param string $s_tablename 数据表名
	 * @param string $function S层方法名
	 * @param mixed $data S层方法名参数
	 * @return object
	 */
	protected function _service_single($tablename, $cp_pluginid, $function, $data = array()) {
		try {
			$s_tablename = 'voa_s_oa_'.$tablename;
			if (is_numeric($cp_pluginid)) {
				$servm = &service::factory($s_tablename, array('pluginid' => $cp_pluginid));
				$params = array_slice(func_get_args(), 3);
			} else {
				$servm = &service::factory($s_tablename, array('pluginid' => 0));
				$function = $cp_pluginid;
				$params = array_slice(func_get_args(), 2);
			}
			return call_user_func_array(array($servm, $function), $params);
		} catch (Exception $e) {
			logger::error($e);
			throw new controller_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * (admincp/base) _before_action
	 *
	 * @param mixed $action
	 * @access protected
	 * @return void
	 */
	protected function _before_action($action) {

		// 设置当前模板目录名
		$this->tpl_dir_base = config::get(startup_env::get('app_name').'.view.admincp_tpl_dir_base');

		error_reporting(E_ALL);

		// 读取当前请求的类型并根据请求输出不同类型的页面格式
		// 如果不需要自动载入页面类型，则在请求控制层加入：define('NO_AUTO_HEADER', true);
		if (!defined('NO_AUTO_HEADER') || !NO_AUTO_HEADER) {
			$accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
			if (stripos($accept, 'application/json') !== false) {
				// 输出json格式
				@header("Content-type: application/json; charset=utf-8");
			} else {
				// 输出 html
				@header("Content-type: text/html; charset=utf-8");
			}
		}

		if (!parent::_before_action($action)) {
			return false;
		}

		// 获取静态文件版本号信息
		$this->static_version = config::get(startup_env::get('app_name').'.misc.static_version');

		// 判断是否 ajax 请求
		$ajax = intval($this->request->get('ajax'));
		$this->_is_ajax = $ajax ? true : false;
		if ($this->request->get('ajax')
			|| (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
				&& rstrtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == rstrtolower('XMLHttpRequest'))) {
			$this->_is_ajax = true;
		}
		$this->view->set('ajax', $this->_is_ajax);

		// 设置静态文件url根目录（兼容旧的模板，不推荐再使用）
		$this->view->set('staticUrl', APP_STATIC_URL);

		$this->staticdir = config::get(startup_env::get('app_name').'.admincp.staticdir');
		$this->imgdir = config::get(startup_env::get('app_name').'.admincp.imgdir');
		$this->cssdir = config::get(startup_env::get('app_name').'.admincp.cssdir');
		$this->jsdir = config::get(startup_env::get('app_name').'.admincp.jsdir');

		$this->view->set('STATICDIR', $this->staticdir);
		$this->view->set('IMGDIR', $this->imgdir);
		$this->view->set('CSSDIR', $this->cssdir);
		$this->view->set('JSDIR', $this->jsdir);

		$this->view->set('msg_url','/admincp/system/message/list');
		$this->view->set('CYADMIN_URL', config::get(startup_env::get('app_name').'.cyadmin_url'));

		$this->fm_staticdir = config::get(startup_env::get('app_name').'.mobile.staticdir');
		$this->fm_imgdir = config::get(startup_env::get('app_name').'.mobile.imgdir');
		$this->fm_cssdir = config::get(startup_env::get('app_name').'.mobile.cssdir');
		$this->fm_jsdir = config::get(startup_env::get('app_name').'.mobile.jsdir');
		$this->fm_jsframework = config::get(startup_env::get('app_name').'.mobile.jsframework');
		$this->fm_static_version = config::get(startup_env::get('app_name').'.mobile.static_version');

		$this->view->set('FM_STATICDIR', $this->fm_staticdir);
		$this->view->set('FM_IMGDIR', $this->fm_imgdir);
		$this->view->set('FM_CSSDIR', $this->fm_cssdir);
		$this->view->set('FM_JSDIR', $this->fm_jsdir);
		$this->view->set('FM_JSFRAMEWORK', $this->fm_jsframework);
		$this->view->set('FM_STATIC_VERSION', $this->fm_static_version);

		/** 初始化变量到模板环境 */
		$this->view->set('module', 'home');
		$this->view->set('operation', '');
		$this->view->set('subop', '');
		$this->view->set('navmenu', '');
		$this->view->set('base',$this);

		/** 读取配置信息 */
		if (!voa_h_conf::init_db()) {
			exit('config file is missing.');
			return false;
		}

		$this->_wechat_noun_list = array(
			'corpid' => 'CorpID',// 微信corpid
			'secret' => 'Secret',// 微信 secret
			'app_url' => 'URL',// 应用回调URL
			'token' => 'Token',// 应用Token
			'aeskey' => 'EncodingAESKey',// 应用EncodingAESKey
			'menu' => '自定义菜单',
			'qrcode_url' => '二维码图片地址',
			'agentid' => '应用ID',
			'app_name' => '应用名称',
			'app_description' => '功能介绍',
			'app_logo' => '应用 LOGO',
			'app_domain' => '可信域名',
			'adminer' => '分级管理员',
			'administrator' => '超级管理员',
			'developer' => '回调模式',
		);

		// 获取系统环境配置
		$this->_setting = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->_start_cookie();

		$this->view->set( 'setting', $this->_setting );
		$this->_referer = (string) $this->request->get( 'referer' );
		if( stripos( $this->_referer, '/login' ) !== false ) {
			$this->_referer = '';
		}

		// 升级检查
		voa_upgrade_index::check_upgrade();

		$this->view->set('setting', $this->_setting);
		$this->_referer = (string)$this->request->get('referer');
		if (stripos($this->_referer, '/login') !== false) {
			$this->_referer = '';
		}

		/** 登录检查 login、退出、找回密码、短信发送 除外 */
		if (in_array($this->action_name, array('login', 'logout', 'pwd', 'sms'))) {
			return true;
		}

		/** 如果未登陆 */
		if (!$this->_is_login()) {
			$this->redirect($this->cpurl('login'));

			$url = $this->_referer;
			if (!$url) {
				$url = isset($_SERVER['REQUEST_URI']) ? rhtmlspecialchars($_SERVER['REQUEST_URI']) : '';
			}
			$this->redirect($this->cpurl('login', '', '', 0, array(
				'referer' => $url
			)));
			return false;
		} elseif ($this->_referer) {
			$this->redirect($this->_referer);
			return true;
		}

		/** 企业使用人数 */
		$serv_member = &service::factory('voa_s_oa_member');
//统计关注人数		$this->_member_count = $serv_member->count_by_conditions(array('m_qywxstatus' => self::WX_FOCUS_STATUS));
		$this->_member_count = $serv_member->count_all();
		$this->view->set('member_count', $this->_member_count);

		//  通过rpc 获取 总后台的消息数据  by ppker-------------------
		$HTTP_SERVER = substr($_SERVER['HTTP_HOST'], -13);
		$HTTP_SERVER_TRUE = ($HTTP_SERVER != '.vchangyi.net' && $HTTP_SERVER != 'gyi.net:10080');
		if ($HTTP_SERVER_TRUE) { // 跳过 .net 后缀域名
			$header_info = $this->_request_rpc($this->_setting['ep_id']); // 获取头部所需数据
			// 免费人数 的免费使用显示
			if ($this->_member_count <= config::get('voa.cyadmin_domain.free_use_number')) {
				$header_info['header_free_pay_status'] = true;
			}
			$this->view->set('header_info', $header_info);
		}

		/*用户id*/
		$uid = $this->_user['ca_id'];
		$settings = voa_h_cache::get_instance()->get('setting', 'oa');
		$epid = $settings['ep_id'];
		$str = $uid.'`'.$epid;
		$this->view->set('info', rbase64_encode(authcode($str, config::get('voa.development.cyadmin.urlkey'),'ENCODE')));
		/** 检查所在管理组权限 */
		$this->_is_cpgroup();

		/** 模块id */
		$this->_module_plugin_id = rintval($this->request->get('pluginid'), false);
		$this->view->set('module_plugin_id', $this->_module_plugin_id);

		/** 获取权限菜单 */
		$this->_get_cpmenu();

		// 不是API模式则检查模块权限模块/主业务/子业务
		if (!$this->_is_api) {
			$this->_check_moso();
		}

		/** 获取浏览器标题 */
		$this->_get_moso_nav_title();

		/** 当前运行的插件信息 */
		$this->_get_plugin();

		// 标记当前执行的插件id
		startup_env::set('pluginid', $this->_module_plugin_id);

		/** 将业务上方导航变量写入模板环境 */
		$this->view->set('navmenu', $this->_navmenu);

		$this->view->set('base',$this);

		/*
				$this->view->set('first_start', false);
				if (empty($this->_setting['not_first_start']) && $this->_module != 'help') {
					// 检查是否是“非首次使用”，如果首次使用，则引导用户选择服务类型
					$first_start_menu = array(
						'setting_servicetype_modify',
						'system_profile_pwd'
					);
					$cmenu = "{$this->_module}_{$this->_operation}_{$this->_subop}";
					if (!in_array($cmenu, $first_start_menu)) {
						// 如果不是服务类型设置页，则跳转到服务类型页。
						$this->message('info', '首次使用系统必须设置服务类型', $this->cpurl('setting', 'servicetype'), false);
						exit;
					}
					$this->view->set('first_start', true);
				}

		*/
		// 判断套件使用权限
		if ($HTTP_SERVER_TRUE) { // 跳过 .net 后缀域名
			$this->_judge_plugin_validity();
		}

		return true;
	}

	/**
	 * 调用rpc方法
	 * @param $ep_id 传递的企业id
	 * @return mixed 返回所需数据
	 */
	protected function _request_rpc($ep_id) {

		// 判断是否独立部署
		if (self::INSTALL_MODE_ALONE == config::get('voa.install_mode')) {
			return true;
		}

		$uid = $this->_user['ca_id']; // 当前登录用户的uid  `ca_id`
		// 总后台domain
		$domain = config::get('voa.cyadmin_domain.domain_url');
		// 获取套件信息
		$tao = voa_h_cache::get_instance()->get('plugin_group', 'oa');
		// 调用rpc

		$rpc = voa_h_rpc::phprpc(config::get('voa.oa_http_scheme') . $domain . '/OaRpc/Rpc/EnterMessage');

		$re = $rpc->get_mes_data($ep_id, $tao, $uid);

		$this->_appset_pub = $re['appset_pub']; // 提供应该设置
		unset($re['appset_pub']);
		return $re;
	}


	/**
	 * 抽出来 通过rpc调用
	 * @param $rpc_con rpc的控制器
	 * @return 返回rpc对象
	 */
	public function  by_rpc_fun($rpc_con) {

		$domain = config::get('voa.cyadmin_domain.domain_url');
		return voa_h_rpc::phprpc(config::get('voa.oa_http_scheme') . $domain . $rpc_con);

	}


	/**
	 * 判断套件使用是否 合法
	 * @return bool
	 */
	protected function _judge_plugin_validity() {

		// 判断是否独立部署
		if (self::INSTALL_MODE_ALONE == config::get('voa.install_mode')) {
			return true;
		}

		if (isset($this->_setting['locked']) && $this->_setting['locked'] == 1) {
			$this->_error_message('非常抱歉, 企业已经被锁定');
		}

		// 跳过没有套件ID的判断
		if (!empty($this->_module_plugin['cpg_id'])) {
			// 读取套件信息
			$plugin_group_cache = voa_h_cache::get_instance()->get('plugin_group', 'oa');
			// 获取现在时间
			$now_timestamp = startup_env::get('timestamp');

			$cpg_id = $this->_module_plugin['cpg_id'];

			// 取出 当前所在套件 信息
			$cpg_data = array();
			foreach ($plugin_group_cache as $k => $v) {
				if ($v['cpg_id'] == $cpg_id) {
					$cpg_data = $plugin_group_cache[$k];
				}
			}
		} else {
			return true;
		}

		// 红包的单独判断
		if ($this->_module_plugin['cp_identifier'] == 'blessingredpack' && $cpg_data['pay_status'] != self::ADMINCP_PAID) {
			$this->_error_message('“祝福红包为收费应用，需开通授权后才能顺利使用。如需了解收费详情请致电：400-860-6961。想要体验一下？扫我吧！' . '<br /><div style="text-align:center;"><img style="width:80px;height:80px;" src="/admincp/static/images/redpack_admincp_message.jpg"></div>');
		}

		// 判断是否免费应用
		if (!empty($this->_module_plugin) && in_array($this->_module_plugin['cp_identifier'], config::get('voa.cyadmin_domain.free_plugin'))) {
			return true;
		}

		// 判断使用人数是否少于30人
		if ($this->_member_count <= config::get('voa.cyadmin_domain.free_use_number')) {
			return true;
		}

		// 如果是定制服务或者 私有部署 (允许)
		if ($cpg_data['pay_type'] == self::CUSTOMIZED_SERVICE || $cpg_data['pay_type'] == self::PRIVATE_DEPLOYMENT) {
			return true;
		}

		// 判断是否被关闭
		if ($cpg_data['stop_status'] == self::OFF_STATUS) {
			$this->_error_message('非常抱歉:套件已经被关闭,请联系客服');
		}

		// 获取 总后台 域名
		$domain = config::get('voa.cyadmin_domain.domain_url');

		// 如果开始时间时间 和截止时间为空, 那么可能是老用户, 添加试用期时间
		if ($cpg_data['date_start'] == 0 && $cpg_data['date_end'] == 0) {

			// 试用期时间
			$probation_time = voa_h_cache::get_instance()->get('probation_time', 'oa');
			// 如果 试用期时间缓存 创建时间 大于1小时 ,那么更新缓存
			if ($probation_time['created'] - startup_env::get('timestamp') > 3600) {
				$probation_time = voa_h_cache::get_instance()->get('probation_time', 'oa', true);
			}
			if (empty($probation_time)) {
				$probation_time['trydate'] = config::get('voa.cyadmin_domain.free_time');
			}

			// 结束时间
			$date_end = $now_timestamp + $probation_time['trydate'] * 86400;

			$update_data = array(
				'pay_type' => self::STANDARD_SERVICE, // 标准产品
				'pay_status' => self::ADMINCP_PROBATION, // 试用期
				'date_start' => $now_timestamp, // 开始时间
				'date_end' => $date_end, // 结束时间
			);

			$serv_plugin_group = &service::factory('voa_s_oa_common_plugin_group');
			$serv_plugin_group->update($update_data, array('cpg_id' => $cpg_id));

			/** 更新缓存操作 */
			$uda_base = &uda::factory('voa_uda_frontend_base');
			$uda_base->update_cache();

			// 同步到总后台
			$oa_to_cy_data = array(
				'ep_id' => $this->_setting['ep_id'],
				'pay_type' => self::STANDARD_SERVICE,
				'cpg_id' => $cpg_id,
				'pay_status' => self::PROBATION,
				'date_start' => $now_timestamp,
				'date_end' => $date_end
			);
			// 初始化RPC
			$rpc = voa_h_rpc::phprpc(config::get('voa.oa_http_scheme') . $domain . '/OaRpc/Rpc/CompanyPaysetting');
			$rpc->oldprobation($oa_to_cy_data);

			return true;
		}

		// 为开启状态 并且 当前时间在 开始时间 和 结束时间 之间
		if ($cpg_data['date_start'] <= $now_timestamp && $now_timestamp <= $cpg_data['date_end']) {
			return true;
		} else {
			$this->_error_message('该套件使用时间为 ' . rgmdate($cpg_data['date_start'], 'Y-m-d H:i:s' . ' 至 ' . rgmdate($cpg_data['date_end'], 'Y-m-d H:i:s') . ' 之间'));
		}

		return true;
	}

	/**
	 * (admincp/base) _after_action
	 *
	 * @access protected
	 * @return void
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

		// session 初始化
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

	/** (admincp/base) 获取模块/主业务/子业务的导航标题 */
	protected function _get_moso_nav_title() {
		$titles = array();
		if ($this->_subop && isset($this->_subop_list[$this->_module][$this->_operation][$this->_module_plugin_id][$this->_subop])) {
			$titles[] = $this->_subop_list[$this->_module][$this->_operation][$this->_module_plugin_id][$this->_subop]['name'];
		}
		if ($this->_operation && isset($this->_operation_list[$this->_module][$this->_operation][$this->_module_plugin_id])) {
			$titles[] = $this->_operation_list[$this->_module][$this->_operation][$this->_module_plugin_id]['name'];
		}
		if ($this->_module && isset($this->_module_list[$this->_module])) {
			$titles[] = $this->_module_list[$this->_module]['name'];
		}
		$this->view->set('nav_title', implode(' - ', $titles));
	}

	/** (admincp/base) 检查模块/主业务/子业务 */
	protected function _check_moso() {

		if (in_array($this->action_name, array('ueditor', 'attachment'))) {
			// 不需要检查权限的动作
			return true;
		}

		/** 获取当前的operation 和 subop */
		if (strpos($this->action_name, '_') !== false) {
			@list($this->_operation, $this->_subop) = explode('_', $this->action_name);
			// 获取当前module
			list($this->_module) = explode('_',str_replace('voa_c_admincp_', '', $this->controller_name));

			// 检查主菜单权限
			if (!isset($this->_module_list[$this->_module])) {
				$this->_module = $this->_default_list['module'];
			}
			// 检查主业务
			if (!isset($this->_operation_list[$this->_module][$this->_operation][$this->_module_plugin_id])) {
				$this->_operation = $this->_default_list['operation'][$this->_module];
			}
			// 具体子业务
			if (!isset($this->_subop_list[$this->_module][$this->_operation][$this->_module_plugin_id][$this->_subop])) {
				$menu = $this->_default_list['subop'][$this->_module][$this->_operation];
				// 跳转到默认业务链接
				if ($menu['module'] != $this->_module || $menu['operation'] != $this->_operation
					|| ($menu['subop'] != $this->_subop && !empty($menu['subop']))
					|| $menu['cp_pluginid'] != $this->_module_plugin_id) {
					// 为避免反复跳转
					$url = $this->cpurl($menu['module'], $menu['operation'], $menu['subop'], $menu['cp_pluginid']);
					@header("Location: {$url}");
					exit;
				}
			}

		} else {
			//公共模块

			@list($this->_module) = explode('_',str_replace('voa_c_admincp_', '', $this->controller_name));

			$current_default = false;
			if (isset($this->_subop_list[$this->_module])) {
				foreach ($this->_subop_list[$this->_module] as $_operations) {
					foreach ($_operations as $_pluginid => $_subops) {
						foreach ($_subops as $_s) {
							// 设置默认
							if (!empty($_s['default'])) {
								$current_default = $_s;
								break;
							}
						}
						if ($current_default) {
							break;
						}
					}
					if ($current_default) {
						break;
					}
				}
			}

			if ($this->_module != 'help') {
				if (empty($current_default)) {
					$this->message('error', '未知的动作');
				}

				$url = $this->cpurl($current_default['module'], $current_default['operation'], $current_default['subop'], $current_default['cp_pluginid']);
				// 跳转到默认的页面
				@header("Location: {$url}");
				exit;
			}
		}

		/** 模板 */
		$this->view->set('module', $this->_module);
		$this->view->set('operation', $this->_operation);
		$this->view->set('subop', $this->_subop);
	}

	/**
	 * (admincp/base) 判断当前用户的后台管理组权限
	 */
	protected function _is_cpgroup() {
		$usergroup = $this->_usergroup;
		if (!$usergroup) {
			$this->_error_message('无效的授权访问后台管理');
			return null;
		}
		if (voa_d_oa_common_adminergroup::ENABLE_NO == $usergroup['cag_enable']) {
			$this->_error_message('您所在管理组禁止访问后台，请联系管理员解决');
		}
		return null;
	}

	/** (admincp/base) 获取当前用户的管理权限菜单 */
	protected function _get_cpmenu() {

		// 自缓存读取当前权限组具有权限的菜单
		list($this->_default_list, $this->_module_list, $this->_operation_list, $this->_subop_list)
			= voa_h_cache::get_instance()->get('adminergroupcpmenu.'.$this->_usergroup['cag_id'], 'oa');

		//$this->view->set('cpmenu_list', voa_h_cache::get_instance()->get('cpmenu', 'oa'));
		$this->view->set('module_list', $this->_module_list);
		$this->view->set('operation_list', $this->_operation_list);
		$this->view->set('subop_list', $this->_subop_list);
		$this->view->set('default_list', $this->_default_list);

		return true;
	}

	/**
	 * 重置系统菜单
	 * 移除系统的硬性固定菜单，菜单键值为-1
	 * 可见：self::_get_cpmenu()内的，额外增加的固定公共菜单部分
	 * @return boolean
	 */
	protected function _remove_cpmenu_system_all($cpmenu = array()) {

		if (is_array($cpmenu)) {
			$tmp = array();
			foreach ($cpmenu as $key => $values) {
				if (isset($values['id'])) {
					if ($values['id'] > 0) {
						$tmp[$key] = $values;
					}
				} else {
					$tmp2 = $this->_remove_cpmenu_system_all($values);
					if ($tmp2) {
						$tmp[$key] = $tmp2;
					}
					unset($tmp2);
				}
			}
			return $tmp;
		} else {
			return $cpmenu;
		}
	}

	/**
	 * (admincp/base) 重写 _is_post 判断, 在 post 时, 判断 formhash 值
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
	 * (admincp/base) 生成 formhash
	 */
	protected function _generate_form_hash() {
		$sets = $this->_setting ? $this->_setting : voa_h_cache::get_instance()->get('setting', 'oa');
		$fh_key = $this->request->server('HTTP_HOST').(isset($sets['formhash_key']) ? $sets['formhash_key'] : '');
		if (!empty($this->_user)) {
			$fh_key .= $this->_user['ca_id'].$this->_user['ca_username'];
		}
		return voa_h_form_hash::generate($fh_key);
	}

	/**
	 * (admincp/base) 判断登陆
	 * @return boolean
	 */
	protected function _is_login() {

		// 获取当前用户
		$uda_adminer_get = &uda::factory('voa_uda_frontend_adminer_get');

		$cookie_data = array();
		if (!$uda_adminer_get->adminer_auth_by_cookie($cookie_data, $this->session)) {
			// 无法取得当前用户的cookie信息
			return false;
		}
		$uid = $cookie_data['uid'];

		// debug 指定当前登录人，仅用于测试，不可在生产环境取消此注释
		//$this->_user = $this->_service_single('common_adminer', 'fetch', 1);
		//$this->_user  = $this->_filter_user_secret_field($this->_user, array('ca_password'));
		//$this->_usergroup = $this->_service_single('common_adminergroup', 'fetch', 1);
		//return true;

		// 读取管理员信息
		$user = $usergroup = array();
		if (!$uda_adminer_get->adminer_info_by_cookie($cookie_data['uid'], $cookie_data['auth'], $cookie_data['lastlogin'], $user, $usergroup)) {
			return false;
		}

		/** 用户数据推入成员变量 */
		$this->_user  = $this->_filter_user_secret_field($user, array('ca_password'));

		$this->_usergroup = $usergroup;

		/** 当前时间距离最后更新时间超过15分钟，则更新其最后登录时间 */
		$timestamp = startup_env::get('timestamp');
		if ($timestamp - $user['ca_updated'] > $this->_user_ttl) {
			$this->_service_single('common_adminer', 'update', array(
				'ca_lastlogin'=>$timestamp,
				'ca_lastloginip'=>$this->request->get_client_ip()
			), $user['ca_id']);
		}

		return true;
	}

	/**
	 * (admincp/base) 从用户信息数组中剔除保密信息
	 * @param array $user
	 * @param array_type $fields
	 */
	protected function _filter_user_secret_field($user, $fields = array()) {

		if (empty($fields)) {
			return $user;
		}
		/** 剔除保密信息 */
		foreach ($fields as $f) {
			unset($user[$f]);
		}
		return $user;
	}

	/**
	 * (admincp/base) 生成数据表中的用户密码,
	 * @param string $passwd 用户提交的密码
	 * @param string $salt 干扰字串
	 */
	protected function _generate_passwd($passwd, $salt) {
		// 使用公共方法生成密码
		list($new_password, $new_salt) = voa_h_func::generate_password($passwd, $salt, false);
		return $new_password;
		//return md5($passwd.$salt); // 旧的密码方式
	}

	/**
	 * (admincp/base) 生成验证登陆用的 skey
	 * @param string $uid 用户名
	 * @param string $passwd 密码
	 */
	protected function _generate_skey($uid, $passwd) {
		return md5($uid.$passwd);
	}

	/**
	 * (admincp/base) _ajax_message
	 * 返回ajax信息
	 *
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
		$result = array(
			'errcode' => $code,
			'errmsg' => $message,
			'result' => $result
		);
		if ($jsonp) {
			/** referer 检查 */
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
			echo $this->response->append_body($callback.'('.rjson_encode($result).')');
		} else {
			echo $this->response->append_body(rjson_encode($result));
		}
		exit;
	}

	/**
	 * (admincp/base) _success_message
	 * 成功消息提示
	 *
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
	 * (admincp/base) _error_message
	 * 失败消息提示
	 *
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
	 * (admincp/base) message
	 * 消息提示
	 *
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
			/** 检查来源链接，不合法的跳转到首页 */
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

		if (preg_match('/\(errno\:(.*?)\)/i', $message, $matches)) {
			if (60011 == $matches[1]) {
				$message .= " <a href='/admincp/help/view/?read=怎么开启微信企业号通讯录权限' target='_blank'>如何开启</a>";
			}
		}

		$this->view->set('title', $title);
		$this->view->set('redirect', $redirect);
		$this->view->set('url', $url);
		$this->view->set('jsUrl', ($url ? str_replace('&amp;', '&', $url) : ''));
		$this->view->set('message', $message);
		$this->view->set('extra', $extra);
		$this->view->set('type', $type);
		return $this->output(''.$tpl);
	}

	/**
	 * (admincp/base) 根据用户名读取用户信息
	 * array(common_adminer,common_adminergroup)
	 * @param string $uid uid
	 */
	protected function _get_user($uid) {
		/** 读取管理员信息 */
		$serv = &service::factory('voa_s_oa_common_adminer');
		$user = $serv->fetch($uid);
		if (empty($user)) {
			return array(false, false);
		}
		$usergroup = $this->_get_usergroup($user['cag_id']);
		return array($user, $usergroup);
	}

	/**
	 * (admincp/base) 获取指定的管理用户组信息
	 * @param number $cag_id
	 */
	protected function _get_usergroup($cag_id) {
		/** 读取其所在的管理组 */
		if (!$cag_id) {
			return false;
		}
		$db = &service::factory('voa_s_oa_common_adminergroup');
		$ret = $db->fetch($cag_id);
		return $ret;
	}

	/**
	 * 格式化用户数据
	 * @param array $datas
	 * @return array
	 */
	protected function _member_list_format($datas = array()) {

		$list = array();
		$departmentList = $this->_department_list();
		$jobList = $this->_job_list();
		$activeList = array();
		if (property_exists($this, '_status_gender_description')) {
			$activeList = $this->_status_gender_description;
		}

		$genders = array();
		if (property_exists($this, '_status_gender_description')) {
			$genders = $this->_status_gender_description;
		}

		foreach ($datas AS $m) {
			$list[$m['m_uid']] = array(
				'm_uid' => $m['m_uid'],
				//'cab_id' => $m['cab_id'],
				'm_username' => $m['m_username'],
				'm_mobilephone' => $m['m_mobilephone'],
				'm_number' => $m['m_number'],
				'cd_id' => $m['cd_id'],
				'cj_id' => $m['cj_id'],
				'_department' => isset($departmentList[$m['cd_id']]) ? $departmentList[$m['cd_id']]['cd_name'] : '',
				'_job' => isset($jobList[$m['cj_id']]) ? $jobList[$m['cj_id']]['cj_name'] : '',
				'_gender' => isset($genders[$m['m_gender']]) ? $genders[$m['m_gender']] : '',
				'_face' => '',
				//'cab_realname' => $m['cab_realname'],
				//'_activestatus' => isset($activeList[$m['cab_active']]) ? $activeList[$m['cab_active']] : '',
			);
		}

		return $list;
	}

	/**
	 * (admincp/base) 返回所有部门列表
	 * @return array
	 */
	protected function _department_list($force = false) {
		if (!$force && isset($this->_department_list_)) {
			return $this->_department_list_;
		}
		$db = &service::factory('voa_s_oa_common_department');
		$list = $db->fetch_all(array());
		$this->_department_list_ = $list;
		unset($list);
		return $this->_department_list_;
	}

	/**
	 * 提取出需要更新的数据
	 * @param array $oldData
	 * @param array $newData
	 * @return array
	 */
	protected function _updated_fields($oldData, $newData) {
		$update = array();
		foreach ($oldData AS $k=>$v) {
			if (isset($newData[$k]) && $newData[$k] != $v) {
				$update[$k] = $newData[$k];
			}
		}
		return $update;
	}

	/**
	 * (admincp/base) 返回所有职务列表
	 * @return array
	 */
	protected function _job_list($force = false) {
		if (!$force && isset($this->_job_list_)) {
			return $this->_job_list_;
		}
		$db = &service::factory('voa_s_oa_common_job');
		$list = $db->fetch_all(array());
		$this->_job_list_ = $list;
		unset($list);
		return $this->_job_list_;
	}

	/**
	 * 获取用户信息
	 * @param mixed $value
	 * @param string $is_uid
	 * @return array
	 */
	protected function _get_member($value, $is_uid = false) {
		$serv = &service::factory('voa_s_oa_member');
		if ($is_uid === false) {
			$member = $serv->fetch_by_username($value);
		} else {
			$member = $serv->fetch($value);
		}
		return $member;
	}

	/**
	 * 返回给定的uid的member信息
	 * @param unknown $m_uids
	 * @return array
	 */
	protected function _get_member_by_uids($m_uids) {
		if (!$m_uids) {
			return array();
		}
		$serv = &service::factory('voa_s_oa_member');
		return $serv->fetch_all_by_ids($m_uids);
	}

	/**
	 * _json_message
	 * 返回 json 信息
	 * @param number $errcode 错误编码
	 * @param string $errmsg 错误信息
	 * @param array $result 结果集合
	 * @return string
	 */
	protected function _json_message($errcode = 0, $errmsg = 'OK', $result = array()) {
		if (isset($_SERVER['HTTP_ACCEPT']) && (stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
			header('Content-type: application/json');
		} else {
			header('Content-type: text/plain');
		}
		$r = array(
			'errcode' => $errcode,
			'errmsg' => $errmsg,
			'result' => $result
		);
		$this->response->append_body(rjson_encode($r));
		$this->response->stop();
	}

	/**
	 * 输出模板
	 * @param string $tpl 引入的模板
	 * @param boolean $return 是否输出
	 * @return unknown
	 */
	public function output($tpl, $return = false) {

		// 输出当前时间戳
		$this->view->set('timestamp', startup_env::get('timestamp'));
		// 输出当前用户信息
		$this->view->set('user', $this->_user);
		// 输出当前用户组
		$this->view->set('usergroup', $this->_usergroup);
		// 尝试输出当前执行的插件信息
		$this->view->set('module_plugin', $this->_module_plugin);
		// 输出 forumhash
		$this->view->set('formhash', $this->_generate_form_hash());
		// 输出当前模板目录名
		$this->view->set('tpl_dir_base', $this->tpl_dir_base);
		$this->view->set('', $this->_expand_head);
		$this->view->set('', $this->_expand_js);
		$this->view->set('', $this->_expand_css);
		$this->view->set('', $this->_expand_foot);
		$this->view->set('', $this->_expand_js_foot);

		$tpl = $this->tpl_dir_base.'/'.$tpl;
		if ($return !== false) {
			return $this->view->render($tpl, true);
		}
		$this->view->render($tpl);

		return $this->response->stop();
	}

	/**
	 * (admincp/base) 消息提醒
	 * @param succees|error $type
	 * @param string $message
	 * @param string $url
	 * @param string $redirect
	 * @param array $extra
	 */
	public function message($type, $message, $url = '', $redirect = true, $extra = array()) {
		/** 不显示提示信息，直接header跳转 */
		if ($url && $redirect === true) {
			@header("Location: ".rhtmlspecialchars($url));
			exit;
		}
		$this->_message($type, $message, '操作提示', $extra, $redirect, $url, 'message'.$type);
	}

	/**
	 * (admincp/base) 构造后台访问URL
	 * @author Deepseath
	 * @param string $module
	 * @param string $operation
	 * @param string $subop
	 * @param array|boolean $extParam
	 * @param boolean $htmlEncode
	 * @param ....
	 * @return string
	 */
	public function cpurl($module = '', $operation = '', $subop = '', $pluginid = 0, $extParam = array(), $htmlEncode = true) {
		$pluginid = rintval($pluginid, false);
		if ($module && $operation && $subop && !isset($this->_subop_list[$module][$operation][$pluginid][$subop])) {
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
			if ($pluginid) {
				$url .= 'pluginid/'.$pluginid.'/';
				//$url .= $pluginid.'/';
			}
		}

		/** 存在扩展参数 */
		if ($extParam) {
			$url .= '?';
			/** 扩展参数是数组形式定义的 */
			if (is_array($extParam)) {
				$urlComma = '';
				foreach ($extParam AS $key=>$value) {
					if (is_scalar($value)) {
						$url .= $urlComma.$key.'='.$value;
					} else {
						$url .= $urlComma.http_build_query(array($key => $value), '', $comma).$comma;
						/**foreach ($value as $_value) {
						$url .= $urlComma.$key.'[]='.$_value;
						$urlComma = $comma;
						}*/
					}
					$urlComma = $comma;
				}
			} else {

				/** 如果extParam是一个布尔值，则认为传入的是多参url变量和值 */
				if (is_bool($extParam)) {
					/** extParam 用来定义htmlEncode */
					$htmlEncode = $extParam;
					/** extParam 后的参数都认为是url的变量与值 */
					/** 变量名，值，变量名，值 ... 的顺序 */
					$arrayKeyValue = array_slice(func_get_args(), 4);
					/** 第一个参数是变量名 */
					$isKey = true;
					$urlComma = '';
					/** 循环参数数组 */
					foreach ($arrayKeyValue AS $v) {
						if ($isKey === true) {
							$isKey = false;
							/** url变量名 */
							$url .= $urlComma.$v;
							$urlComma = $comma;
						} else {
							$isKey = true;
							/** url变量值 */
							$url .= '='.($htmlEncode ? urlencode($v) : $v);
						}
					}
					/** 传入的是已经构造好了的url字符串，则直接使用 */
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
	public function linkShow($link, $linkExtraParam, $text, $icon = '', $extAttr = '') {
		// 以下均为适应多情况而硬性拼写的结构，并不是一个常规的写法^_^ by Deepseath
		if ($icon) {
			$icon = '<i class="fa '.$icon.'"></i> ';
		}
		if ($link) {
			$link .= $linkExtraParam;
			return '<a href="'.$link.'"'.($extAttr ? ' '.$extAttr : '').'>'.$icon.$text.'</a>';
		} else {
			if (!$extAttr) {
				$extAttr = ' class="disabled"';
			} else {
				if (stripos($extAttr, 'class=') === false) {
					// 不存在样式类定义
					$extAttr .= ' class="disabled"';
				} else {
					if (stripos($extAttr, 'disabled') === false) {
						$extAttr = str_ireplace('class="', 'class="disabled ', $extAttr);
					}
				}
			}
			return '<span'.$extAttr.'>'.$icon.$text.'</span>';
		}
	}

	/**
	 * 转义自定义标签为html
	 * @param string $string
	 * @return string
	 */
	public function _bbcode2html($string) {
		return bbcode::instance()->bbcode2html($string);
	}

	/**
	 * 返回附件的绝对Url路径
	 * @param string $path
	 * @return string
	 */
	public function attchment_url($path) {
		return voa_h_attach::attachment_url($path);
	}

	/**
	 * 当前正执行的插件信息
	 */
	protected function _get_plugin() {
		if ($this->_module_plugin_id && empty($this->_module_plugin)) {
			$db = &service::factory('voa_s_oa_common_plugin');
			$this->_module_plugin = $db->fetch_by_cp_pluginid($this->_module_plugin_id);
		}
	}

	/**
	 * 按分级列出部门列表
	 * @param number $upid 上级部门ID
	 * @return array
	 */
	protected function _department_level_list($upid = 0) {
		if (!$upid) {
			if (isset($this->_department_level_list_)) {
				return $this->_department_level_list_;
			}
		}

		$department_list = $this->_department_list();

		$list = array();
		foreach ($department_list as $d) {
			if ($d['cd_upid'] != $upid) {
				continue;
			}
			$list[$d['cd_id']] = $d;
			$list[$d['cd_id']]['sub'] = $this->_department_level_list($d['cd_id']);
		}

		if (!$upid) {
			$this->_department_level_list_ = $list;
			unset($list);

			return $this->_department_level_list_;
		} else {
			return $list;
		}
	}

	/**
	 * 构造部门选择器
	 * @param string $name
	 * @param array $defaults
	 * @param string $id
	 * @return string
	 */
	protected function _department_select($name, $defaults = array(), $id = '') {
		if (is_scalar($defaults)) {
			$defaults = explode(',', $defaults);
		}

		$options = array();
		$options[] = '<option value="0">请选择……</option>';
		/** 循环遍历 */
		$options[] = $this->_department_select_option($defaults, 0, 0);
		$options = implode("\r\n", $options);
		if (!$id) {
			$id = $name;
		}
		$html = <<<EOF
		<select id="{$id}" name="{$name}" size="1" class="form-control">{$options}</select>
EOF;
		return $html;
	}

	/**
	 * 构造单选框部门选项列表
	 * @param string|array $defaults
	 * @param number $upid
	 * @param number $level
	 * @return string
	 */
	protected function _department_select_option($defaults, $upid = 0, $level = 0) {

		if (!$upid) {
			if (isset($this->_department_option_)) {
				return $this->_department_option_;
			}
		}

		$department_list = $this->_department_list();

		$pad_left = '';
		if ($level > 0) {
			for ($i=0; $i<$level;$i++) {
				$pad_left .= ' &nbsp; ';
			}
			$pad_left .= ' |- ';
		} else {
			$pad_left .= ' |- ';
		}

		$option = '';
		foreach ($department_list as $d) {
			if ($d['cd_upid'] != $upid) {
				continue;
			}
			$option .= '<option value="'.$d['cd_id'].'"'.(in_array($d['cd_id'], $defaults) ? ' selected="selected"' : '').'>'.$pad_left.$d['cd_name'].'</option>';
			$option .= $this->_department_select_option($defaults, $d['cd_id'], $level+1);
		}

		if (!$upid) {
			$this->_department_option_ = $option;
			unset($option);

			return $this->_department_option_;
		} else {
			return $option;
		}

	}

	/**
	 * 读取文档的链接
	 * @param string $filename
	 * @return string
	 */
	public function view_help_url($filename) {
		return '/admincp/help/view/?read='.rawurlencode($filename);
	}

	/**
	 * 将微信专有名词注入到模板内
	 */
	public function _wechat_lang_set() {
		foreach ($this->_wechat_noun_list as $k => $v) {
			$this->view->set('lang_'.$k, $v);
		}
	}

	/**
	 * 输出错误消息提醒
	 * @param mixed $h 异常抛出的错误对象 或者 自定义的错误编码
	 * @param string $custom_errmsg 自定义的错误提示文字，仅在 $h 为自定义编码时有效
	 * @param array $result 输出的结果集合（如果有）
	 * @example 存在如下使用场景：<pre>
	 * 异常错误抛出给用户的提示：_admincp_error_message($h);
	 * 使用错误编码库的错误：_admincp_error_message(voa_errcode_oa_xxx::ERROR[[,array()], var1, var2, var3 ....]);
	 * 完全自定义的错误：_admincp_error_message(xxx, '发生错误');
	 * 自定义的内部错误：_admincp_error_message(xxx);
	 * </pre>
	 * @return void
	 */
	public function _admincp_error_message($h, $custom_errmsg = '', $result = array()) {

		// 通过编码库定义的错误
		if (is_scalar($h) && strpos($h, ':') !== false) {

			// 获取给定的参数
			$values = func_get_args();
			// 移除错误编码字符串，其他参数则为错误编码的变量值和可能返回的结果集合
			unset($values[0]);
			// 如果不存在其他参数，则直接使用help方法获取错误信息
			if (empty($values)) {
				voa_h_func::set_errmsg($h);
				$this->__output_error_message(voa_h_func::$errcode, voa_h_func::$errmsg, $result);
				return;
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
			$this->__output_error_message(voa_h_func::$errcode, voa_h_func::$errmsg, $result);

			return;
		}

		/** 使用异常抛出的错误对象 或 完全自定义错误方式 */

		// 自定义的错误编码
		if (!is_object($h) || !method_exists($h, 'getCode')) {
			$errcode = $h;
			$errmsg = $custom_errmsg ? $custom_errmsg.'[Error: '.$h.']' : '系统发生内部错误，错误编码：'.$h;
			$this->__output_error_message($errcode, $errmsg, $result);
			return;
		}

		// 通过异常抛出的呈现给用户的错误提示信息（非系统错误）
		$errcode = $h->getCode();
		$errmsg = $h->getMessage().'[Error: '.$h->getCode().']';
		$this->__output_error_message($errcode, $errmsg, $result);

		return;
	}

	/**
	 * 系统内部错误输出
	 * @todo 使用该方法时，一般推荐同时使用 logger::error($e); 记录日志
	 * @param mixed $e 异常抛出的错误对象 或者 自定义的错误编码
	 * @param string $custom_message 自定义的错误提示文字，仅在 $e 为自定义编码时有效
	 * @param array $result 输出的结果集合（如果有）
	 * @example 使用场景<pre>
	 * 异常错误抛出给用户提示（内部错误）：_admincp_system_message($e);
	 * 完全自定义的错误：_admincp_system_message(xxx, '发生错误');
	 * 自定义的内部错误：_admincp_system_message(xxx);
	 * @return void
	 */
	protected function _admincp_system_message($e, $custom_message = '', $result = array()) {

		// 自定义的错误编码
		if (!is_object($e) || !method_exists($e, 'getCode')) {
			$errcode = $e;
			$errmsg = $custom_message ? $custom_message : '系统发生内部错误，错误编码：'.$e;
			$this->__output_error_message($errcode, $errmsg, $result);
			return;
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
		if (!$this->_is_ajax) {
			$error_detail = "<pre>".nl2br(rhtmlspecialchars($error_detail))."</pre>";
		}

		// 系统错误：通过异常抛出的不呈现给用户的内部错误提示
		$errcode = $e->getCode();
		if (!$errcode) {
			$errcode = -9999;
		}
		$errmsg = '操作失败，系统发生内部错误，错误编码：'.$errcode.$error_detail;
		$this->__output_error_message($errcode, $errmsg, $result);

		return;
	}

	/**
	 * 输出成功消息
	 * @param string $message 提示信息内容
	 * @param string $url 跳转的url
	 * @param array $result 结果集
	 * @return void
	 */
	protected function _admincp_success_message($message, $url = '', $result = array()) {

		// 判断是否是ajax请求
		if ($this->_is_ajax || $this->_is_api) {
			$result['url'] = $url;
			$r = array(
				'errcode' => 0,
				'errmsg' => 'OK',
				'timestamp' => startup_env::get('timestamp'),
				'result' => $result
			);
			$this->response->append_body(rjson_encode($r));
			$this->response->stop();
		} else {
			$this->message('success', $message, $url, false);
		}
	}

	/**
	 * 输出给前端界面的提示信息
	 * @param number $errcode 错误编码
	 * @param string $errmsg 提示内容
	 * @param array $result 结果集合
	 * @return void
	 */
	private function __output_error_message($errcode, $errmsg, $result) {

		// 判断是否是 ajax 请求
		if ($this->_is_ajax || $this->_is_api) {
			$r = array(
				'errcode' => $errcode,
				'errmsg' => $errmsg,
				'timestamp' => startup_env::get('timestamp'),
				'result' => $result
			);
			$this->response->append_body(rjson_encode($r));
			$this->response->stop();
		} else {
			$this->message('error', $errmsg);
		}
	}
	public function output_error_message($errcode, $errmsg, $result){
		$this->__output_error_message($errcode, $errmsg, $result);
	}
	/**
	 * 构造后台人员部门选择器需要的数据
	 * @param mixed $m_uids
	 * @param mixed $cd_ids
	 * @param string $input_m_uids
	 * @param string $input_cd_ids
	 * @return string
	 */
	protected function _make_user_data($m_uids = array(), $cd_ids = array(), $input_m_uids = 'm_uids[]', $input_cd_ids = 'cd_ids[]') {
		$default = array();
		//设置快递接收人有用户时，获取用户姓名
		if(!empty($m_uids)) {
			if (!is_array($m_uids)) {
				$m_uids = explode(',', $m_uids);//用户id
			}
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch_all_by_ids($m_uids);
			foreach ($m_uids as $_k => $_v) {
				//获取人员
				if ($_v != 0) {
					$default[] = array(
						'id' => $_v,
						'name' => $users[$_v]['m_username'],
						'input_name' => $input_m_uids
					);
				}
			}
		}

		//设置快递接收人有用户时，获取用户所在部门
		if(!empty($cd_ids)) {
			if (!is_array($cd_ids)) {
				$cd_ids = explode(',', $cd_ids);//部门id
			}
			$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
			$depms = $serv_d->fetch_all_by_key($cd_ids);
			foreach ($cd_ids as $_k => $_v) {
				//获取部门
				if ($_v != 0) {
					$default[] = array(
						'id' => $_v,
						'name' => $depms[$_v]['cd_name'],
						'input_name' => $input_cd_ids
					);
				}
			}
		}

		return $default;
	}

}

