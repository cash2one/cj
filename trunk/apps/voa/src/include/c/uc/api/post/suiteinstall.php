<?php
/**
 * suiteinstall.php
 * 更改企业信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_api_post_suiteinstall extends voa_c_uc_api_base {

	/** 企业ID */
	private $__ep_id = 0;
	/** 提交的待更改的企业资料 */
	private $__profile = array();
	private $__uda_enterprise = null;
	private $__enterprise = array();

	public function execute() {

		$this->__uda_enterprise = new voa_uda_uc_enterprise();

		if (!$this->__param_check()) {
			return false;
		}

		if (!$this->__open_enterprise()) {
			return false;
		}

		return true;
	}

	/**
	 * 检查提交的参数数据
	 * @return boolean
	 */
	private function __param_check() {

		// 可接受的参数
		//cpurl,mobilephone,realname,email,ename,companysize,industry,
		$fields = array(
			'suiteid' => array('type' => 'string_trim', 'required' => true),
			'corpid' => array('type' => 'string_trim', 'required' => true),
			// 公司号（域名）
			'enumber' => array('type' => 'string_trim', 'required' => true),
			// 手机号码
			'mobilephone' => array('type' => 'string_trim', 'required' => true),
			// 新密码
			'newpw' => array('type' => 'string_trim', 'required' => false),
			// 真实姓名
			'realname' => array('type' => 'string_trim', 'required' => true),
			// 邮箱
			'email' => array('type' => 'string_trim', 'required' => true),
			// 企业名称
			'ename' => array('type' => 'string_trim', 'required' => true),
			// 企业号
			'enumber' => array('type' => 'string_trim', 'required' => true),
			// 行业
			'industry' => array('type' => 'string_trim', 'required' => true),
			// 公司规模
			'companysize' => array('type' => 'string_trim', 'required' => true),
			// 来源
			'ref' => array('type' => 'string_trim', 'required' => false)
		);

		// 基本变量检查和过滤
		$this->_check_params($fields);

		if (empty($this->_params['enumber'])) {
			$this->_params['enumber'] = 'z'.startup_env::get('timestamp').random(5);
		}
		if (empty($this->_params['suiteid'])) {
			return $this->_set_errcode(voa_errcode_uc_suiteinstall::SUITEID_NULL);
		}

		return true;
	}

	/**
	 * 开启企业站(注册)
	 * @return boolean
	 */
	private function __open_enterprise() {

		$cypt_xxtea = new crypt_xxtea();
		if (empty($this->_params['enumber'])) {
			$this->_params['enumber'] = rstrtolower(startup_env::get('timestamp').random(5));
		}

		// 启动注册过程
		$enterprise = array();
		$params = array(
			'realname' => $this->_params['realname'],
			'mobilephone' => $this->_params['mobilephone'],
			'password' => $this->_params['password'],
			'email' => $this->_params['email'],
			'ename' => $this->_params['ename'],
			'enumber' => $this->_params['enumber'],
			'password' => $this->_params['password'],
			'industry' => $this->_params['industry'],
			'companysize' => $this->_params['companysize'],
			'smsauth' => rbase64_encode($cypt_xxtea->encrypt($this->_params['mobilephone'])),
			'ref' => empty($this->_params['ref']) ? '' : $this->_params['ref'],
			'ref_domain' => empty($this->_params['ref_domain']) ? '' : $this->_params['ref_domain']
		);
		if (!$this->__uda_enterprise->open($params, $this->__enterprise)) {
			$this->errcode = $this->__uda_enterprise->errcode;
			$this->errmsg = $this->__uda_enterprise->errmsg;
			return false;
		}

		$this->__create_enumber_map();

		$this->__send_mail($params);

		$this->__sendsms($params);
		//$suite = $this->__update_suite_auth();

		$scheme = config::get('voa.oa_http_scheme');
		$suite_install_url = $scheme.$this->_params['enumber'].'.vchangyi.com/admincp/login/?';
		$suite_install_url .= 'referer='.rawurlencode('/admincp/setting/application/list/?corpid='.$this->_params['corpid'].'');
		$suite_install_url .= '&login=1';
		$suite_install_url .= '&account='.$this->_params['mobilephone'];
		$suite_install_url .= '&password='.md5($this->_params['password']);
		$this->result = array(
			'suite_install_url' => $suite_install_url
		);

		return true;
	}

	/**
	 * 创建手机号、域名、企业之间的映射关系
	 * @return boolean
	 */
	private function __create_enumber_map() {

		return true;
	}

	/**
	 * 写入授权信息到企业站
	 */
	private function __update_suite_auth() {

		// 读取授权信息
		$s_preauth = new voa_s_uc_preauth();
		$preauth = $s_preauth->get_by_conds(array('corpid' => $this->_params['corpid']));
		$authdata = unserialize($preauth['authdata']);
		$oa_suite = array(
			'auth_corpid' => $authdata['auth_corp_info']['corpid'],
			'permanent_code' => $authdata['permanent_code'],
			'access_token' => $authdata['access_token'],
			'expires' => startup_env::get('timestamp') + ($authdata['expires_in'] * 0.8),
			'authinfo' => serialize($authdata)
		);

		return array(
			'suiteid' => $preauth['suiteid']
		);
	}

	/**
	 * 发送邮件，注册成功
	 * @param $params
	 */
	private function __send_mail($params) {

		$uda_mailcloud = &uda::factory('voa_uda_uc_mailcloud_insert');
		$mails = array(
			$params['email'],
		);
		$subject = config::get('voa.mailcloud.subject_for_register');
		$scheme = config::get('voa.oa_http_scheme');
		$vars = array(
			'%domain%' => array($params['enumber']),
			'%sitename%' => array($params['ename']),
			'%mobilephone%' => array($params['mobilephone']),
			'%email%' => array($params['email']),
			'%admincpurl%' => array($scheme.$params['enumber'].'.vchangyi.com/admincp'),
			'%pc_url%' => array($scheme.$params['enumber'].'.vchangyi.com/pc'),
			'%download_pc%' => array('<a href="' . $scheme.$params['enumber'].'.vchangyi.com/frontend/index/download">点击下载</a>')
		);

		$uda_mailcloud->send_reg_vchangyi_mail($mails, $subject, $vars);
	}

	/**
	 * 发送短信, 通知用户注册成功
	 * @param array $enterprise 企业信息
	 * @return boolean
	 */
	private function __sendsms($params) {

		return true;
		// 检查手机号
		if (!validator::is_mobile($params['mobilephone'])) {
			return false;
		}

		// 消息范本
		$msg = config::get(startup_env::get('cfg_name').'.mailcloud.register_succeed_msg');
		$scheme = config::get('voa.oa_http_scheme');
		$msg = sprintf($msg, $params['ename'], $params['enumber'], $params['mobilephone'], $params['email'], $scheme.$params['enumber'].'.vchangyi.com/admincp', $scheme.$params['enumber'].'.vchangyi.com/pc');

		/** ip */
		$ip = empty($ip) ? controller_request::get_instance()->get_client_ip() : $ip;

		// 发送
		$uda_sms = &uda::factory('voa_uda_uc_sms_insert');
		if (!$uda_sms->send($params['mobilephone'], $msg, $ip)) {
			return false;
		}

		return true;
	}
}
