<?php
/**
 * voa_uda_uc_mailcloud_insert
 * 统一数据访问/邮件发送操作/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_uc_mailcloud_insert extends voa_uda_uc_mailcloud_base {

	public function __construct() {
		parent::__construct();
	}

	public function send_reg_vchangyi_mail($mails, $subject, $vars = array(), $from = '', $fromname = '') {

		$tpl = $this->_tpls['register_vchangyi'];
		$result = mailcloud::get_instance()->send_tpl_mail($tpl, $mails, $subject, $vars, $from, $fromname);
		$data = array();
		foreach ($mails as $m) {
			$data[] = array(
				'mc_tplname' => $tpl,
				'mc_email' => $m,
				'mc_subject' => $subject,
				'mc_vars' => serialize($vars),
				'mc_repeat' => 0,
				'mc_status' => $result ? voa_d_uc_mailcloud::STATUS_SENDED : voa_d_uc_mailcloud::STATUS_FAILED
			);
		}

		$serv_mc = &service::factory('voa_s_uc_mailcloud', array('pluginid' => startup_env::get('pluginid')));
		$serv_mc->insert_multi($data);

		return true;
	}


	public function send_reg_cyadmin_mail($mails, $subject, $vars = array(), $from = '', $fromname = '') {

		$tpl = $this->_tpls['register_cyadmin'];
		$result = mailcloud::get_instance()->send_tpl_mail($tpl, $mails, $subject, $vars, $from, $fromname);
		$data = array();
		foreach ($mails as $m) {
			$data[] = array(
				'mc_tplname' => $tpl,
				'mc_email' => $m,
				'mc_subject' => $subject,
				'mc_vars' => serialize($vars),
				'mc_repeat' => 0,
				'mc_status' => $result ? voa_d_uc_mailcloud::STATUS_SENDED : voa_d_uc_mailcloud::STATUS_FAILED
			);
		}

		$serv_mc = &service::factory('voa_s_uc_mailcloud', array('pluginid' => startup_env::get('pluginid')));
		$serv_mc->insert_multi($data);

		return true;
	}

	/**
	 * 发送邀请关注邮件
	 * @param $mails
	 * @param $subject
	 * @param array $vars
	 * @param string $from
	 * @param string $fromname
	 * @return bool
	 */
	public function send_invite_follow_mail($mails, $subject, $vars = array(), $from = '', $fromname = '') {

		$tpl = $this->_tpls['invite_follow'];
		$result = mailcloud::get_instance()->send_tpl_mail($tpl, $mails, $subject, $vars, $from, $fromname);
		$data = array();
		foreach ($mails as $m) {
			$data[] = array(
				'mc_tplname' => $tpl,
				'mc_email' => $m,
				'mc_subject' => $subject,
				'mc_vars' => serialize($vars),
				'mc_repeat' => 0,
				'mc_status' => $result ? voa_d_uc_mailcloud::STATUS_SENDED : voa_d_uc_mailcloud::STATUS_FAILED
			);
		}

		$serv_mc = &service::factory('voa_s_uc_mailcloud', array('pluginid' => startup_env::get('pluginid')));
		$serv_mc->insert_multi($data);

		return true;
	}

	/**
	 * 发送注册模板邮件
	 * @param array $mails 接收人邮箱地址
	 * @param string $subject 邮箱主题
	 * @param array $vars 模板邮件的变量值, 保持和邮箱地址一致的顺序
	 *  array(
	 *  	'%name%' => array('zhuxun37', 'zhuxun'),
	 *  	'%url%' => array('http://www.a.com', 'http://www.b.com')
	 *  )
	 * @return string
	 */
	public function send_reg_mail($mails, $subject, $vars = array(), $from = '', $fromname = '') {

		$tpl = $this->_tpls['register'];
		$result = mailcloud::get_instance()->send_tpl_mail($tpl, $mails, $subject, $vars, $from, $fromname);
		$data = array();
		foreach ($mails as $m) {
			$data[] = array(
				'mc_tplname' => $tpl,
				'mc_email' => $m,
				'mc_subject' => $subject,
				'mc_vars' => serialize($vars),
				'mc_repeat' => 0,
				'mc_status' => $result ? voa_d_uc_mailcloud::STATUS_SENDED : voa_d_uc_mailcloud::STATUS_FAILED
			);
		}

		$serv_mc = &service::factory('voa_s_uc_mailcloud', array('pluginid' => startup_env::get('pluginid')));
		$serv_mc->insert_multi($data);

		return true;
	}

	/**
	 * 发送密码重置模板邮件
	 * @param array $mail 接收人邮箱地址
	 * @param string $subject 邮箱主题
	 * @param array $vars 模板邮件的变量值
	 *  array(
	 *  	'%name%' => array('zhuxun37'),
	 *  	'%url%' => array('http://www.a.com')
	 *  )
	 * @return string
	 */
	public function send_pwdreset_mail($mail, $subject, $vars = array(), $from = '', $fromname = '') {

		$mails = array($mail);
		$tpl = $this->_tpls['pwdreset'];
		$result = mailcloud::get_instance()->send_tpl_mail($tpl, $mails, $subject, $vars, $from, $fromname);
		$data = array();
		foreach ($mails as $m) {
			$data[] = array(
				'mc_tplname' => $tpl,
				'mc_email' => $m,
				'mc_subject' => $subject,
				'mc_vars' => serialize($vars),
				'mc_repeat' => 0,
				'mc_status' => $result ? voa_d_uc_mailcloud::STATUS_SENDED : voa_d_uc_mailcloud::STATUS_FAILED
			);
		}

		$serv_mc = &service::factory('voa_s_uc_mailcloud', array('pluginid' => startup_env::get('pluginid')));
		$serv_mc->insert_multi($data);

		return true;
	}

}
