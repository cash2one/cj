<?php
/**
 * login.php
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_wxwall_admincp_login extends voa_c_wxwall_admincp_base {

	public function execute() {

		$account = $this->request->get('wxscreen_account');
		$password= $this->request->get('wxscreen_password');

		if ($account == '' || $password == '') {
			$this->_message('error', '微信墙管理帐号或密码错误');
		}

		/** 根据管理名获取微信墙信息 */
		$serv_wxwall = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
		$wxwall = $serv_wxwall->fetch_by_admin($account);

		if (empty($wxwall)) {
			$this->_message('error', '微信墙管理帐号或者密码输入错误');
		}
		if ($wxwall['ww_passwd'] == '' || $wxwall['ww_passwd'] != $this->_generate_passwd($password, $wxwall['ww_salt'])) {
			$this->_message('error', '对不起，微信墙管理帐号或者密码输入错误');
		}

		$this->session->setx($this->_cookiename_ww_id, $wxwall['ww_id'], 0);
		$this->session->setx($this->_cookiename_key, $this->_wxwall_key($wxwall['ww_admin'], $wxwall['ww_passwd']), 0);

		$this->_message('success', '验证成功，即将进入管理界面', $this->wxwall_admincp_url('verify'), false);

	}

}
