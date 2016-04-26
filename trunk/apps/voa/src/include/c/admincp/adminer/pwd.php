<?php
/**
 * voa_c_admincp_adminer_pwd
 * 忘记密码找回
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_adminer_pwd extends voa_c_admincp_adminer_base {

	public function execute() {

		$err_msg = '';


		if ($this->_is_post()) {

			$mobilephone = (string)$this->request->post('mobilephone');
			$password = (string)$this->request->post('password');
			$smscode = (string)$this->request->post('smscode');

			if (!validator::is_mobile($mobilephone)) {
				return $this->_output(100, '请正确输入手机号码');
			}

			$password = rstrtolower($password);
			if (!validator::is_md5($password)) {
				return $this->_output(101, '密码格式不正确');
			}

			// 通过接口检查验证码是否正确
			$params = array(
				'mobilephone' => $mobilephone,
				'smscode' => $smscode
			);

			// 呼叫oa uc api接口
			$r = $this->_call_oauc_api('smscodeverify', $params, false);
			if (!isset($r['errcode'])) {
				return $this->_output(100, '请求UC短信验证接口发生错误');
			}
			if ($r['errcode'] > 0) {
				return $this->_output($r['errcode'], $r['errmsg'], $r['result']);
			}

			// 获取管理员信息
			$uda_adminer_get = &uda::factory('voa_uda_frontend_adminer_get');
			$adminer = array();
			$adminergroup = array();
			if (!$uda_adminer_get->adminer_by_account($mobilephone, $adminer, $adminergroup)) {
				$this->_output($uda_adminer_get->errcode, $uda_adminer_get->errmsg);
				return;
			}

			// 修改密码
			$uda_adminer_update = &uda::factory('voa_uda_frontend_adminer_update');
			$uda_adminer_update->adminer_pwd_modify($adminer['ca_id'], $password, false);

			$this->_output(0, '重置密码操作成功，请使用新密码登录系统');
			return true;
		}

		// 官网首页普通登录跳转的找回密码页面，填写的手机号
		$get_mobilephone = $this->request->get('mobilephone');

		$this->view->set('err_msg', $err_msg);
		$this->view->set('get_mobilephone', $get_mobilephone);

		$this->output('adminer/pwd');
	}

}
