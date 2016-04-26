<?php
/**
 * voa_c_uc_api_post_register
 * 企业站注册接口
 * https://uc.dev.vchangyi.com/uc/api/post/register
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_api_post_register extends voa_c_uc_api_base {

	/** 企业uda */
	private $_uda_enterprise = null;

	/** 加密key */
	private $_auth_key = '&*h3GaGHWKJT67a23*&';

	public function execute() {

		$this->_uda_enterprise = &uda::factory('voa_uda_uc_enterprise');

		if (isset($this->_params['smsauth'])) {
			$step = 'create';
		} elseif (isset($this->_params['submitauth'])) {
			$step = 'dnsmail';
		} else {
			$step = 'smscode';
		}

		// 步骤对应执行的方法
		$class_method_name = '_step_'.$step;
		if (!method_exists($this, $class_method_name)) {
			$this->_set_errcode(voa_errcode_uc_register::REGISTER_STEP_NOT_EXISTS, $class_method_name);
		}

		// 对应步骤执行的方法
		$this->$class_method_name();

		return;
	}

	/**
	 * 注册流程1：发送手机验证码
	 * @return boolean
	 */
	private function _step_smscode() {

		// 可接受的参数
		$fields = array(
			// 注册人手机号
			'mobilephone' => array('type' => 'string', 'required' => true),
			// 输入的验证码
			//'smscode' => array('type' => 'string', 'required' => true),
		);

		// 基本参数检查和过滤
		$this->_check_params($fields);

		// 验证手机号
		if (!$this->_uda_enterprise->check_enterprise_mobilephone($this->_params['mobilephone'], 0)) {
			$this->errcode = $this->_uda_enterprise->errcode;
			$this->errmsg = $this->_uda_enterprise->errmsg;
			return false;
		}

		// 验证码有效期
		$set_expire_second = config::get('voa.smscode_send_expire');

		// 验证短信验证码合法性
		$uda_smscode_get = &uda::factory('voa_uda_uc_smscode_get');
		if (!$uda_smscode_get->validator($this->_params['mobilephone'], $this->_params['smscode'], $set_expire_second)) {
			$this->errcode = $uda_smscode_get->errno;
			$this->errmsg = $uda_smscode_get->error;
			$this->result = array();
			return false;
		}


		// 验证有效，返回一组加密字符串
		$crypt_xxtea = new crypt_xxtea($this->_auth_key);
		$smsauth = $crypt_xxtea->encrypt($this->_params['mobilephone']);

		// 结果数据
		$this->result['mobilephone'] = $this->_params['mobilephone'];
		$this->result['smsauth'] = rbase64_encode($smsauth);

		return true;
	}

	/**
	 * 注册流程2，关键信息写入、企业站内数据写入
	 * @return boolean
	 */
	private function _step_create() {

		// 可接受的参数
		$fields = array(
			// 注册人手机号
			'mobilephone' => array('type' => 'string', 'required' => true),
			// 短信扰码信息
			'smsauth' => array('type' => 'string', 'required' => true),
			// 真实姓名
			'realname' => array('type' => 'string', 'required' => true),
			// 邮箱
			'email' => array('type' => 'string', 'required' => true),
			// 企业名称
			'ename' => array('type' => 'string', 'required' => true),
			// 企业号
			'enumber' => array('type' => 'string', 'required' => true),
			// 密码
			'password' => array('type' => 'string', 'required' => true),
			// 行业
			'industry' => array('type' => 'string', 'required' => true),
			// 公司规模
			'companysize' => array('type' => 'string', 'required' => true),
			// 微信unionid
			'unionid' => array('type' => 'string', 'required' => false),
			// 微信unionid
			'ref' => array('type' => 'string', 'required' => false),
			//来源域名
			'ref_domain' => array('type' => 'string', 'required' => false)
		);

		// 基本变量检查和过滤
		$this->_check_params($fields);

		// 经过基本过滤和整理的数据
		$reset_data = $this->_params;

		// 校验手机短信扰码
		$cypt_xxtea = new crypt_xxtea();
		$_smsauth = rbase64_decode($this->_params['smsauth']);
		if ($_smsauth === false) {
			logger::error("A".$this->_params['smsauth']);
			$this->_set_errcode(voa_errcode_uc_register::REGISTER_SMSCODE_FORBID_ERROR);
			return false;
		}
		$_mobilephone = $cypt_xxtea->decrypt($_smsauth);
		$_mobilephone = preg_replace('/0{10}$/i', '', $_mobilephone);
		if ($_mobilephone != $this->_params['mobilephone']) {
			logger::error("B".$this->_params['smsauth']);
			$this->_set_errcode(voa_errcode_uc_register::REGISTER_SMSCODE_FORBID);
			return false;
		}

		// 启动注册过程
		if (!$this->_uda_enterprise->open($this->_params, $reset_data)) {
			$this->errcode = $this->_uda_enterprise->errcode;
			$this->errmsg = $this->_uda_enterprise->errmsg;
			return false;
		}

		// 注册过程的认证扰码
		$submitauth = $this->_authstr_generate($reset_data['ep_id'], $reset_data['submit']['enumber'], 2);

		// 注册的提交的数据（经过基本整理）
		$this->result = array(
			'submitauth' => $submitauth,
			'password' => md5($this->_params['password'])
		);
//		$this->result['submitauth'] = $submitauth;

		return true;
	}

	/**
	 * 步骤3：写入DNS、发送邮件
	 * @return boolean
	 */
	private function _step_dnsmail() {

		$submitauth = isset($this->_params['submitauth']) ? $this->_params['submitauth'] : '';
		$ep_id = 0;
		if (!$this->_authstr_verify($submitauth, $ep_id)) {
			$this->_set_errcode(voa_errcode_uc_register::REGISTER_STEP_VERIFY_ERROR);
			return false;
		}

		// 获取企业信息
		$enterprise = array();
		$this->_uda_enterprise->enterprise($ep_id, $enterprise);
		if (empty($enterprise)) {
			$this->_set_errcode(voa_errcode_uc_register::REGISTER_STEP_FAILED);
			return false;
		}

		$uda_webhost_search = &uda::factory('voa_uda_uc_webhost_search');
		// 其所在web主机信息
		$webhost = array();
		if (!$uda_webhost_search->fetch($enterprise['web_id'], $webhost)) {
			$this->_set_errcode(voa_errcode_uc_register::REGISTER_WEBHOST_NOT_EXISTS, (int)$enterprise['web_id']);
			return false;
		}

		// 避免出错引起客户端解析异常
		error_reporting(0);

		// 企业站二级域名
		$cname = $enterprise['ep_enumber'];
		// web主机的别名
		$domain = $webhost['web_alias'];
		// 写入DNS
		$uda_dnspod = &uda::factory('voa_uda_uc_dnspod_insert');
		$uda_dnspod->add_cname($cname, $domain);

		// 发送注册成功邮件，不返回发送失败错误提示，因为不影响前台显示
		$uda_mailcloud = &uda::factory('voa_uda_uc_mailcloud_insert');
		$mails = array(
			$enterprise['ep_adminemail'],
		);
		$subject = config::get('voa.mailcloud.subject_for_register');
		$domain_childs = explode('.', $enterprise['ep_domain']);
		$scheme = config::get('voa.oa_http_scheme');
		$vars = array(
			'%domain%' => array($domain_childs[0]),
			'%sitename%' => array($enterprise['ep_name']),
			'%mobilephone%' => array($enterprise['ep_adminmobilephone']),
			'%email%' => array($enterprise['ep_adminemail']),
			'%admincpurl%' => array($scheme.$enterprise['ep_domain'].'/admincp'),
			'%pc_url%' => array($scheme.$enterprise['ep_domain'].'/pc'),
			'%download_pc%' => array('<a href="' . $scheme.$enterprise['ep_domain'].'/frontend/index/download">点击下载</a>')
		);

		// 如果是总后台添加,就发总后台的邮件模板 和 短信
		if (isset($this->_params['ref']) && $this->_params['ref'] == config::get('voa.cyadmin_domain.ref')) {
			$uda_mailcloud->send_reg_cyadmin_mail($mails, $subject, $vars);

			// 发送短信, 通知用户注册成功
			$this->_sendsms($enterprise, config::get('voa.cyadmin_domain.ref'));
		} else {
			$uda_mailcloud->send_reg_vchangyi_mail($mails, $subject, $vars);

			// 发送短信, 通知用户注册成功
			$this->_sendsms($enterprise);
		}

		// 直接跳转登录
		$this->result = array(
			'http' => $scheme,
			'domain' => $enterprise['ep_domain'],
			'mobilephone' => $enterprise['ep_adminmobilephone'],
			'password' => $this->_params['password']
		);

		// 返回注册结果
//		$this->result = array(
//			'ename' => $enterprise['ep_name'],
//			'enumber' => $enterprise['ep_enumber'],
//			'domain' => $domain,
//			'wxqy' => $enterprise['ep_wxqy'],
//			'mobilephone' => $enterprise['ep_adminmobilephone'],
//			'email' => $enterprise['ep_adminemail'],
			//'wxname' => $enterprise['epp_wxname'],
			//'industry' => $enterprise['epp_industry'],
			//'city' => $enterprise['epp_city'],
//		);

		return true;
	}

	/**
	 * 发送短信, 通知用户注册成功
	 * @param array $enterprise 企业信息
	 * @param string $ref 来源
	 * @return boolean
	 */
	protected function _sendsms($enterprise, $ref = '') {

		// zhuxun 去除短信通知
		return true;
		// 检查手机号
		if (!validator::is_mobile($enterprise['ep_adminmobilephone'])) {
			return false;
		}

		// 消息范本
		$domain_childs = explode('.', $enterprise['ep_domain']);
		// 如果是总后台的添加
		if (!empty($ref) && $ref == config::get('voa.cyadmin_domain.ref')) {
			$msg = config::get(startup_env::get('cfg_name').'.mailcloud.register_succeed_msg');
		} else {
			$msg = config::get(startup_env::get('cfg_name').'.mailcloud.cyadmin_register_succeed_msg');
		}
		$scheme = config::get('voa.oa_http_scheme');
		$msg = sprintf($msg, $enterprise['ep_name'], $domain_childs[0], $enterprise['ep_adminmobilephone'], $enterprise['ep_adminemail'], $scheme.$enterprise['ep_domain'].'/admincp', $scheme.$enterprise['ep_domain'].'/pc');

		/** ip */
		$ip = empty($ip) ? controller_request::get_instance()->get_client_ip() : $ip;

		// 发送
		$uda_sms = &uda::factory('voa_uda_uc_sms_insert');
		if (!$uda_sms->send($enterprise['ep_adminmobilephone'], $msg, $ip)) {
			// 去除错误提示
			/**if ($uda_sms->errno) {
				$this->errcode = $uda_sms->errno;
				$this->errmsg = $uda_sms->error;
				$this->result = array();
			} else {
				$this->set_errmsg(voa_errcode_uc_mobileverify::UC_MV_SEND_SMSCODE_UNKNOW);
			}*/
			return false;
		}

		return true;
	}

	/**
	 * 生成一个用于注册流程中的扰码验证
	 * @param number $ep_id
	 * @param string $enumber
	 * @return string
	 */
	private function _authstr_generate($ep_id, $enumber) {
		return md5($ep_id."\t".$enumber."\t").$ep_id;
	}

	/**
	 * 校验流程扰码合法性
	 * @param string $authstr
	 * @param number $ep_id <strong style="color:red">(引用结果)</strong>$ep_id
	 * @return boolean
	 */
	private function _authstr_verify($authstr, &$ep_id = 0) {
		$ep_id = substr($authstr, 32);
		$ep = array();
		$this->_uda_enterprise->enterprise($ep_id, $ep);
		if ($ep && $this->_authstr_generate($ep['ep_id'], $ep['ep_enumber']) == $authstr) {
			return true;
		}

		return false;
	}

}
