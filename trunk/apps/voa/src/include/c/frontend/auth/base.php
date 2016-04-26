<?php
/**
 * voa_c_frontend_auth_base
 * auth认证基类
 * Created by zhoutao.
 * Created Time: 2015/7/5  8:23
 */

class voa_c_frontend_auth_base extends voa_c_frontend_base {

	protected $_auto_login = true;

	protected function _auto_login() {
		// 如果游客上次访问时间距离当前时间比较远，则检查登录状态
		if ($this->_in_wechat() && startup_env::get('timestamp') - $this->session->get('_guest_login_') > 3600) {
			// 写入游客登录时间

			$this->session->set('_guest_login_', startup_env::get('timestamp'));
		}
		// 判断是否需要前往微信 自动登录
		if ($this->_auto_login) {
			parent::_auto_login();
		}
		$this->_require_login = $this->_auto_login;

		// 用户信息初始化
		$this->_init_user();

		// 如果需要强制登录
		if ($this->_require_login && empty($this->_user)) {
			$this->session->destroy();
			$this->_error_message('未在企业号里的人员不能使用PC版', null, null, null, '当前没有权限','error_tmp');
			return false;
		}
	}

}
