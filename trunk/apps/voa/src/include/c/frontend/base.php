<?php
/**
 * voa_c_frontend_base
 * 基本控制器
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_base extends controller {

	/** 总后台试用期状态 */
	const PROBATION = 7;
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

	// openid
	protected $_openid = false;
	protected $_wechatid = false;
	// 当前用户信息
	protected $_user = array();
	// 站点配置
	protected $_setting = array();
	// 模块(插件)配置
	protected $_p_sets = array();
	// 插件信息
	protected $_plugin = array();
	// 插件名称
	protected $_pluginname = '';
	// 草稿信息
	protected $_draft = array();

	// 静态文件根目录URL
	public $staticdir = '';
	// 图片文件根目录url
	public $imgdir = '';
	// css样式文件根目录
	public $cssdir = '';
	// js样式文件根目录
	public $jsdir = '';
	// js框架根目录
	public $jsframework = '';
	// 设备类型
	public $device_type = 'mobile';
	// 是否需要登录
	public $_require_login = true;

	/** 是否启用新版H5模板 */
	protected $_mobile_tpl = false;

	// 企业号用户debug
	protected $_debug_qy_openid = false;

	/** 企业应用使用情况 */
	protected $_plugin_status = array();

	/**
	 * _before_action
	 *
	 * @param mixed $action
	 * @access protected
	 * @return void
	 */
	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 初始化页面标题变量到模板
		$this->view->set('navtitle', '');

		// 判断是否 ajax 请求
		$inajax = intval($this->request->get('inajax'));
		startup_env::set('inajax', $inajax);
		$this->view->set('inajax', $inajax);

		// 把当前实例输出到模板
		$this->view->set('cinstance', $this);

		// 加载提示语言
		language::load_lang('message');

		// 读取配置信息
		if (!voa_h_conf::init_db()) {
			$func = $this->request->get('login_result');
			if ($func) {
				$func = rhtmlspecialchars($func);
				exit("{$func}(\"config file is missing.\")");
			}
			exit('config file is missing.');
			return false;
		}

		// 读取站点配置
		$this->_setting = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->_start_cookie();

		// 升级检查
		voa_upgrade_index::check_upgrade();

		// 涉及到的静态目录配置
		$this->staticdir = config::get(startup_env::get('app_name').'.'.$this->device_type.'.staticdir');
		$this->imgdir = config::get(startup_env::get('app_name').'.'.$this->device_type.'.imgdir');
		$this->cssdir = config::get(startup_env::get('app_name').'.'.$this->device_type.'.cssdir');
		$this->jsdir = config::get(startup_env::get('app_name').'.'.$this->device_type.'.scriptdir');
		$this->jsframework = config::get(startup_env::get('app_name').'.'.$this->device_type.'.jsframework');

		// 将静态目录变量注入模板
		$this->view->set('STATICDIR', $this->staticdir);
		$this->view->set('IMGDIR', $this->imgdir);
		$this->view->set('CSSDIR', $this->cssdir);
		$this->view->set('JSDIR', $this->jsdir);
		$this->view->set('JSFRAMEWORK', $this->jsframework);
		$this->view->set('static_version', config::get(startup_env::get('app_name').'.'.$this->device_type.'.static_version'));

		// 接收外部传入的js参数，主要用于手机前端的模板载入
		// 选择模板
		$__view = (string)$this->request->get('__view');
		// 传入路由需要的参数
		$__params = (array)$this->request->get('__params');
		// 转义html
		$__view = rhtmlspecialchars($__view);
		$__params = rhtmlspecialchars($__params);
		$__view = raddslashes($__view);
		// 注入到模板变量
		$this->view->set('__view', $__view);
		$this->view->set('__params', rjson_encode($__params));
		unset($__params, $__view);
		// End 结束外部参数载入

		// 获取插件信息
		$this->_get_plugin();
		$this->view->set('p_sets', $this->_p_sets);

		// debug
		$this->_debug_qy_openid = config::get('voa.debug.qy_openid');
		if (!empty($this->_debug_qy_openid) && $this->request->get('__openid')) {
			$this->_debug_qy_openid = $this->request->get('__openid');
		}

		// 用户信息初始化
		$this->_init_user();
		if (empty($this->_user)) {
			$this->_auto_login();
		} else {
			// 有 code 就转向
			$code = $this->request->get('code');
			if (!empty($code)) {
				$boardurl = preg_replace('/\&?code\=(\w+)/i', '', startup_env::get('boardurl'));
				$this->response->set_redirect($boardurl);
				return true;
			}
		}

		// 如果需要强制登录
		if ($this->_require_login && empty($this->_user)) {
			$this->session->destroy();
			$this->_error_message('请联系您公司管理员，登录畅移后台进行人员【同步】', null, null, null, '尚未加入企业');
			return false;
		}

		// 公司信息
		$companyinfo = array(
			'companyname' => $this->_setting['sitename']
		);

		// 登录用户信息
		$this->view->set('userinfo', rjson_encode((array)$this->_user));
		$this->view->set('companyinfo', rjson_encode($companyinfo));

		// 年度专题结束推送日期，到该日期则不推送
		$year2014_end_date = '2015-01-15';
		// 年度专题分享/访问链接入口URL
		/**$year2014_url = '';
		if (!empty($this->_user) && startup_env::get('timestamp') < rstrtotime($year2014_end_date)) {
			$year2014_url = $this->_year_url('year2014', $this->_user['m_uid'], startup_env::get('time'));
		}
		$this->view->set('year2014_url', $year2014_url);*/

		// 更新最后登录时间
		if (!empty($this->_user) && !empty($this->_user['m_updated']) && $this->_user['m_updated'] + 1800 < startup_env::get('timestamp')) {
			$serv_m = &service::factory('voa_s_oa_member');
			$serv_m->update(array(), $this->_user['m_uid']);

			list($y, $m, $d, $w) = explode("-", rgmdate(startup_env::get('timestamp'), 'Y-m-d-W'));
			// 记录入库
			$serv_ul = &service::factory('voa_s_oa_common_userlog');
			$serv_ul->insert(array(
				'uid' => $this->_user['m_uid'],
				'year' => $y,
				'month' => $m,
				'day' => $d,
				'week' => $w
			));
		}

		if (!empty($this->_plugin)) {
			$this->view->set('stat_plugin_id', authcode($this->_plugin['cp_identifier'], config::get('voa.auth_key'), 'ENCODE'));
			$referer = $this->request->server('HTTP_REFERER');
			if (empty($referer)) {
				$referer = 'referer_empty';
			}
			$referer = authcode($referer, config::get('voa.auth_key'), 'ENCODE');
			$this->view->set('stat_referer', $referer);
		}

		// 判断套件使用权限
		$this->_judge_plugin_validity();

		return true;
	}

	/**
	 * _after_action
	 *
	 * @access protected
	 * @return void
	 */
	protected function _after_action($action) {

		return true;
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
		if (!empty($this->_plugin['cpg_id'])) {
			$cpg_id = $this->_plugin['cpg_id'];

			// 读取套件信息
			$plugin_group_cache = voa_h_cache::get_instance()->get('plugin_group', 'oa');
			// 获取现在时间
			$now_timestamp = startup_env::get('timestamp');

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
//		if ($this->_module_plugin['cp_identifier'] == 'blessingredpack' && $cpg_data['pay_status'] == self::ADMINCP_PROBATION) {
//			$this->_error_message('非常抱歉,红包应用我们不包括在"' . $cpg_data['cpg_name'] . '"套件下试用,请购买套件');
//		}

		// 判断是否免费应用
		if (in_array($this->_plugin['cp_identifier'], config::get('voa.cyadmin_domain.free_plugin'))) {
			return true;
		}

		// 判断使用人数是否少于30人
		$serv_member = &service::factory('voa_s_oa_member');
		$member_count = $serv_member->count_by_conditions(array('m_qywxstatus' => self::WX_FOCUS_STATUS));
		if ($member_count <= config::get('voa.cyadmin_domain.free_use_number')) {
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
	 * _start_session
	 * 启动session支持
	 *
	 * @return object
	 */
	protected function _start_session() {

		return null;
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
		// $domain = $_SERVER['HTTP_HOST'];
		$expired = config::get($prefix.'.expired');
		$public_key = empty($this->_setting['authkey']) ? config::get($prefix.'.public_key') : $this->_setting['authkey'];

		$this->session =& session::get_instance($domain, $expired, $public_key);
		ob_start(array($this->session, 'send'));
	}

	// 获取插件信息
	protected function _get_plugin() {

		return true;
	}

	/**
	 * 重写 _is_post 判断, 在 post 时, 判断 formhash 值
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
	 * 生成 formhash
	 */
	protected function _generate_form_hash() {

		$fh_key = $this->request->server('HTTP_HOST').(isset($this->_setting['formhash_key']) ? $this->_setting['formhash_key'] : '');
		if (!empty($this->_user) && !empty($this->_user['m_uid'])) {
			$fh_key .= $this->_user['m_uid'].$this->_user['m_username'];
		}

		return voa_h_form_hash::generate($fh_key);
	}

	/**
	 * 获取当前用户uid
	 */
	protected function _get_current_uid() {
		return trim($this->session->get('uid'));
	}

	/**
	 * 获取当前验证字串
	 */
	protected function _get_current_skey() {
		return trim($this->session->get('skey'));
	}

	/**
	 * 用户登陆
	 * @param string $username 用户名
	 * @param string $passwd 密码
	 */
	protected function _login($username, $passwd) {
		return false;
	}

	/**
	 * 判断登陆
	 * @param string $username 用户名
	 * @param string $passwd 密码
	 */
	protected function _init_user() {

		$uda_member_get = &uda::factory('voa_uda_frontend_member_get');

		// cookie 信息
		$cookie_data = array();
		if (!$uda_member_get->member_auth_by_cookie($cookie_data, $this->session)) {
			// 无法取得当前用户的cookie信息
			return false;
		}

		if (empty($cookie_data)) {
			return false;
		}

		$user = array();
		if (!$uda_member_get->member_info_by_cookie($cookie_data['uid'], $cookie_data['auth'], $cookie_data['lastlogin'], $user)) {
			return false;
		}

		// 设置用户相关环境变量
		$this->_set_user_env($user);

		return true;
	}

	/**
	 * 设置用户相关的环境变量
	 * @param array $user 用户信息
	 */
	protected function _set_user_env($user) {

		$this->_user = $this->_filter_user_secret_field($user, array('m_password', 'm_salt'));
		$this->_openid = $this->_user['m_openid'];

		// 登陆成功后, 清除 openid
		$this->session->remove('openid');
		startup_env::set('wbs_uid', $user['m_uid']);
		startup_env::set('wbs_username', $user['m_username']);
		startup_env::set('web_access_token', isset($user['m_web_access_token']) ? $user['m_web_access_token'] : '');
		startup_env::set('web_token_expires', isset($user['m_web_token_expires']) ? $user['m_web_token_expires'] : 0);

		// 推入用户信息数组
		voa_h_user::push($user);
	}

	// 自动登陆
	protected function _auto_login() {

		// 判断是否已经登录
		if (!empty($this->_user)) {
			return true;
		}

		$code = $this->request->get('code');
		// 如果 code 不为空
		if ($this->_debug_qy_openid || !$this->_require_login || !empty($code) || !preg_match("/vchangyi\.(net|com)$/i", $_SERVER['HTTP_HOST'])) {
			return $this->_auto_login_qy();
		}

		// 解析 url
		$parsed_url = parse_url(startup_env::get('boardurl'));
		$queries = array();
		// 解析参数
		if (isset($parsed_url['query'])) {
			parse_str($parsed_url['query'], $queries);
		}

		$boardurl = $parsed_url['scheme'].'://'.$parsed_url['host'].$parsed_url['path'].'?'.http_build_query($queries);
		if (!empty($parsed_url['fragment'])) {
			$boardurl .= '#'.$parsed_url['fragment'];
		}

		$serv = voa_wxqy_service::instance();
		header('Location: '.$serv->oauth_url_base($boardurl));
		$this->response->stop(); exit;

		return false;
	}

	// 自动登录企业号
	protected function _auto_login_qy() {

		// 判断是否已经登录
		if (!empty($this->_user)) {
			return true;
		}

		// 如果存在debug数据
		if ($this->_debug_qy_openid) {
			$this->_openid = $this->_debug_qy_openid;
		}

		$openid = $this->_get_qy_openid();
		if (empty($openid)) {
			logger::error('qy openid is empty.');
			return false;
		}

		// 读取用户信息
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$user = $servm->fetch_by_openid($openid);

		if (empty($user)) {
			logger::error('member is not exist.(openid:'.$openid.')');
			return false;
		}

		// 如果用户还处在验证状态, 则
		/**if (voa_d_oa_member::STATUS_VERIFY == $user['m_status']) {
			$this->_success_message('member_verify_waiting');
			return false;
		}*/

		// 设置用户相关环境变量
		$uda_member_update = &uda::factory('voa_uda_frontend_member_update');
		$result = array();
		if (!$uda_member_update->member_login($user['m_uid'], '', $result)) {
			return false;
		}

		// 登录信息相关
		foreach ($result['auth'] as $_nv) {
			$this->session->set($_nv['name'], $_nv['value']);
		}

		// 设置用户环境相关
		$this->_set_user_env($user);

		return true;
	}

	// 获取当前用户 openid
	protected function _get_qy_openid() {

		if (false !== $this->_openid) {
			return $this->_openid;
		}

		// 从网页接口获取 openid
		$wx_serv = voa_wxqy_service::instance();
		$wx_serv->get_web_openid($this->_openid);

		// 如果还是为空, 则
		if (empty($this->_openid)) {
			$this->_openid = '';
		}

		return $this->_openid;
	}

	/**
	 * 从用户信息数组中剔除保密信息
	 * @param unknown_type $user
	 * @param unknown_type $fields
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
	 * 生成数据表中的用户密码,
	 * @param string $passwd 用户提交的密码
	 * @param string $salt 干扰字串
	 */
	protected function _generate_passwd($passwd, $salt) {
		return md5($passwd.$salt);
	}

	/**
	 * 生成验证登陆用的 skey
	 * @param string $username 用户名
	 * @param string $passwd 密码
	 */
	protected function _generate_skey($username, $passwd) {
		return md5($username.$passwd);
	}

	/**
	 * 根据用户名读取用户信息
	 * @param string $username 用户名
	 */
	protected function _get_user($username) {
		$serv = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		return $serv->fetch_by_username($username);
	}

	/**
	 * 根据 uid 读取用户信息
	 * @param int $uid 用户uid
	 */
	protected function _get_user_by_uid($uid) {
		$serv = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		return $serv->fetch_by_uid($uid);
	}

	/**
	 * output
	 * 输出模板
	 *
	 * @param string $tpl 引入的模板
	 * @return unknown
	 */
	protected function _output($tpl) {

		// 当前时间戳
		$this->view->set('timestamp', startup_env::get('timestamp'));
		$this->view->set('domain', $this->_setting['domain']);
		$this->view->set('sitename', $this->_setting['sitename']);
		// 是否含有带发送的消息
		if ($this->session->get('mq_ids')) {
			$this->view->set('qywx_send', true);
		}

		// 输出当前用户信息
		$this->view->set('wbs_user', $this->_user);
		if (!empty($this->_user) && !empty($this->_user['m_uid'])) {
			$this->view->set('wbs_uid', $this->_user['m_uid']);
			$this->view->set('wbs_username', $this->_user['m_username']);
		} else {
			$this->view->set('wbs_uid', 0);
			$this->view->set('wbs_username', '');
		}

		$this->view->set('wbs_javascript_path', config::get('voa.scriptdir'));
		$this->view->set('wbs_css_path', config::get('voa.cssdir'));

		// 输出 forumHash
		$this->view->set('formhash', $this->_generate_form_hash());
		// 此判断仅为新旧手机版本模板兼容性判断
		// 待旧版手机模板全部替换完毕后可移除此判断
		if (empty($this->_mobile_tpl)) {
			$tpl = $this->route->get_module().'/'.$tpl;
		} else {
			if (strpos($tpl, 'mobile_v1/') !== 0 && strpos($tpl, 'mobile/') !== 0) {
				$tpl = 'mobile/'.$tpl;
			}
		}
		$this->view->render($tpl);

		return $this->response->stop();
	}

	/**
	 * _json_message
	 * 返回 json 信息
	 *
	 * @param integer $code 0是成功，非0是失败。失败时，不同数字代表不同失败类型。
	 * @param array $result 结果
	 * @return string
	 */
	protected function _json_message($result = array(), $message = null) {
		// header('Content-type: application/json');
		$this->response->append_body(rjson_encode($result));
		$this->response->stop();
	}

	/**
	 * _success_message
	 * 成功消息提示
	 *
	 * @param  mixed $message
	 * @param  mixed $url
	 * @param mixed $values
	 * @param  mixed $extra
	 * @param  mixed $title
	 * @param  string $tpl
	 * @return void
	 */
	protected function _success_message($message = null, $url = null, $values = null,
			$extra = null, $title = null, $tpl = 'message') {

		return $this->_message('success', $message, $url, $values, $extra, $title, $tpl);
	}

	/**
	 * _error_message
	 * 失败消息提示
	 *
	 * @param  mixed $message
	 * @param  mixed $url
	 * @param mixed $values
	 * @param  mixed $extra
	 * @param  mixed $title
	 * @param  string $tpl
	 * @return void
	 */
	protected function _error_message($message = null, $url = null, $values = null,
			$extra = null, $title = null, $tpl = 'error') {

		return $this->_message('error', $message, $url, $values, $extra, $title, $tpl);
	}

	/**
	 * _no_content
	 * 无内容消息提示
	 *
	 * @param null $message
	 * @param null $url
	 * @param null $values
	 * @param null $extra
	 * @param null $title
	 * @param string $tpl
	 */
	protected function _no_content($message = null, $url = null, $values = null,
			$extra = null, $title = null, $tpl = 'no_content') {
		return $this->_message('no_content', $message, $url, $values, $extra, $title, $tpl);
	}

	/**
	 * _no_authority
	 * 无权限消息提示
	 *
	 * @param null $message
	 * @param null $url
	 * @param null $values
	 * @param null $extra
	 * @param null $title
	 * @param string $tpl
	 */
	protected function _no_authority($message = null, $url = null, $values = null,
			$extra = null, $title = null, $tpl = 'no_authority') {
		return $this->_message('no_authority', $message, $url, $values, $extra, $title, $tpl);
	}

	/**
	 * _no_personnel
	 * 无人员消息提示
	 * @param null $message
	 * @param null $url
	 * @param null $values
	 * @param null $extra
	 * @param null $title
	 * @param string $tpl
	 */
	protected function _no_personnel($message = null, $url = null, $values = null,
			$extra = null, $title = null, $tpl = 'no_personnel') {
		return $this->_message('no_personnel', $message, $url, $values, $extra, $title, $tpl);
	}

	/**
	 * message
	 * 消息提示
	 *
	 * @param  string $type
	 * @param  mixed $message
	 * @param  mixed $url
	 * @param mixed $values
	 * @param  mixed $extra
	 * @param  mixed $title
	 * @param  string $tpl
	 * @return void
	 */
	protected function _message($type = 'success', $message = null, $url = null, $values = null,
			$extra = null, $title = null, $tpl = 'message') {

		// 不缓存提示页面
		$this->response->set_raw_header("Cache-Control: no-cache");
		$this->response->set_raw_header("Pragma: no-cache");
		$this->response->set_raw_header("Expires: 0");
		$this->response->send_headers();
		// 回调页面 js 相关
		$extra_js = '';
		// js 函数名
		$handlekey = $this->request->get('handlekey');
		// js 提示文字
		$message = parse_lang($message);
		$jsmessage = str_replace("'", "\\'", $message);
		if ($type == 'success') {
			if (!$title) {
				$title = '成功';
			}

			if (!$message) {
				$message = '操作已成功';
			}

			$extra_js .= 'if(typeof succeedhandle_'.$handlekey.'==\'function\') {succeedhandle_'.$handlekey.'(\''.$url.'\', \''.$jsmessage.'\');}';
		} else if ($type == 'no_content') {
			if (!$title) {
				$title = '没有内容';
			}

			if (!$message) {
				$message = '操作失败';
			}

			$extra_js .= 'if(typeof no_contenthandle_'.$handlekey.'==\'function\') {no_contenthandle_'.$handlekey.'(\''.$url.'\', \''.$jsmessage.'\');}';
		} else if ($type == 'no_authority') {
			if (!$title) {
				$title = '没有权限';
			}

			if (!$message) {
				$message = '操作失败';
			}

			$extra_js .= 'if(typeof no_authorityhandle_'.$handlekey.'==\'function\') {no_authorityhandle_'.$handlekey.'(\''.$url.'\', \''.$jsmessage.'\');}';

		} else if ($type == 'no_personnel') {
			if (!$title) {
				$title = '没有权限';
			}

			if (!$message) {
				$message = '操作失败';
			}

			$extra_js .= 'if(typeof no_personnelhandle_'.$handlekey.'==\'function\') {no_personnelhandle_'.$handlekey.'(\''.$url.'\', \''.$jsmessage.'\');}';

		} else {
			if (!$title) {
				$title = '失败';
			}

			if (!$message) {
				$message = '操作失败';
			}

			$extra_js .= 'if(typeof errorhandle_'.$handlekey.'==\'function\') {errorhandle_'.$handlekey.'(\''.$url.'\', \''.$jsmessage.'\');}';
		}

		if (!$url) {
			// 检查来源链接，不合法的跳转到首页
			$referer = $this->request->server('HTTP_REFERER');
			/**if (!$referer || !preg_match('/^[htps]+\:\/\/(.*?)\.vchangyi\.com/', $referer)) {
				$url = '/';
			} else {
				$url = $referer;
			}*/
		}

		if (!empty($this->_mobile_tpl) && strpos($this->_mobile_tpl, '/') === false) {
			$tpl = 'mobile/'.$tpl;
			// 如果是 ajax 请求, 则
			if (0 < startup_env::get("inajax")) {
				$result = array(
					'errcode' => 0,
					'errmsg' => "",
					'timestamp' => startup_env::get('timestamp'),
					'result' => array(
						"url" => empty($url) ? "" : $url,
						"type" => 'success' == $type ? 'success' : 'warn',
						"message" => $message
					)
				);
				return $this->_json_message($result);
			}
		}

		// 把 js 和消息一起显示
		$message .= $extra_js ? '<script type="text/javascript" reload="1">'.$extra_js.'</script>' : '';

		$this->view->set('title', $title);
		$this->view->set('url', $url);
		$this->view->set('message', $message);
		$this->view->set('extra', $extra);
		$this->view->set('type', $type);
		return $this->_output($tpl);
	}

	// 输出部门信息/职位信息
	protected function _set_dept_job() {
		$this->view->set('departments', voa_h_cache::get_instance()->get('department', 'oa'));
		$this->view->set('jobs', voa_h_cache::get_instance()->get('job', 'oa'));
	}

	/**
	 * 设置队列 id 的 session
	 * @param array $mq_ids
	 */
	public function set_queue_session($mq_ids) {
		voa_h_qymsg::set_queue_session($mq_ids, $this->session);
	}

	/**
	 * 获取指定用户的头像
	 * @param int $uid
	 */
	public function avatar($uid) {
		return voa_h_user::avatar($uid);
	}

	// xml 响应头
	public function xml_header() {
		ob_end_clean();
		ob_start();
		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", true);
		@header("Pragma: no-cache");
		@header("Content-type: text/xml; charset=UTF-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
	}

	/**
	 * 构造年度总结URL
	 * @param string $year 年度，影响使用哪个控制器
	 * @param number $uid 用户uid
	 * @param number $t 生成链接的时间戳
	 * @param string $key 进入指定页面，为空则进入活动专题首页
	 * @return string
	 */
	protected function _year_url($year = '', $uid = 0, $t = 0, $key = '') {

		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$scheme = config::get(startup_env::get('app_name').'.oa_http_scheme');
		$url = $scheme.$sets['domain'];
		$url .= '/frontend/'.$year.'/?sig='.urlencode(voa_h_func::sig_create($uid, $t));
		if ($key) {
			$url .= '&key='.$key;
		}
		$url .= '&id='.$uid;
		$url .= '&t='.$t;

		return $url;
	}

	/**
	 * 获取微信jsapi调用签名信息
	 * @param string $jsapi_list 需要调用的微信jsapi模块
	 * @todo 注入模板变量jsapi
	 * + corpid
	 * + timestamp
	 * + nonce_str
	 * + signature
	 */
	protected function _get_jsapi($jsapi_list = '[]') {

		$wxqy_service = new voa_wxqy_service();
		$jsapi = $wxqy_service->jsapi_signature();

		$this->view->set('jsapi', $jsapi);
		$this->view->set('jsapi_list', $jsapi_list);

		// 使用微信jsapi接口
		$this->view->set('use_wxjsapi', 1);
	}

	/**
	 * 验证是否在微信视窗内浏览
	 * @return boolean
	 */
	protected function _in_wechat() {

		return isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/MicroMessenger/i', $_SERVER['HTTP_USER_AGENT']);
	}

	/**
	 * 获取微信 openid
	 * @param string $openid 微信 openid
	 * @return boolean
	 */
	protected function _get_wx_openid(&$openid) {

		// 如果用户信息不存在, 则
		if (empty($this->_user)) {
			return false;
		}

		// 如果微信 openid 存在
		if (!empty($this->_user['wx_openid'])) {
			$openid = $this->_user['wx_openid'];
			return true;
		}

		// 获取微信 openid
		if (!$this->_get_openid_by_userid($openid, $this->_user['m_openid'])) {
			return false;
		}

		// 更新微信 openid
		$serv = &service::factory('voa_s_oa_member');
		$serv->update(array('wx_openid' => $openid));
		return true;
	}

	/**
	 * 根据 userid 来获取 openid
	 * @param string $openid 微信的 openid
	 * @param string $userid 企业号的 userid
	 */
	protected function _get_openid_by_userid(&$openid, $userid) {

		$serv = &service::factory('voa_wxqy_service');
		return $serv->convert_to_openid($openid, $userid);
	}

	// 检查 agentid
	protected static function _check_agentid(&$sets, $cp_name) {

		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		$plugin = array();
		// 获取指定插件
		foreach ($plugins as $_id => $_p) {
			if ($_p['cp_identifier'] == $cp_name) {
				$plugin = $_p;
				break;
			}
		}

		if (empty($plugin)) {
			return false;
		}

		// 检查插件ID
		$update = array();
		if ($sets['pluginid'] != $plugin['cp_pluginid']) {
			$update['pluginid'] = $plugin['cp_pluginid'];
			$sets['pluginid'] = $plugin['cp_pluginid'];
		}

		// 检查应用ID
		if ($sets['agentid'] != $plugin['cp_agentid']) {
			$update['agentid'] = $plugin['cp_agentid'];
			$sets['agentid'] = $plugin['cp_agentid'];
		}

		// 更新数据
		if (!empty($update)) {
			$serv = &service::factory('voa_s_oa_' . $cp_name . '_setting');
			$serv->update_setting($update);
		}

		return true;
	}

}
