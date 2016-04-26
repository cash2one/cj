<?php
/**
 * voa_c_frontend_invite_base
 * 邀请人员基类
 * Created by zhoutao.
 * Created Time: 2015/7/8  16:59
 */

class voa_c_frontend_invite_base extends voa_c_frontend_base {

	// 加密密钥
	protected $_state_secret_key = null;
	// 手机模板
	protected $_mobile_tpl = true;
	// 自动登录
	protected $_auto_login = true;
	// 系统缓存
	protected $_invite_setting = array();

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_invite_setting = voa_h_cache::get_instance()->get('plugin.invite.setting', 'oa');

		return true;
	}

	protected function _auto_login() {
		// 如果游客上次访问时间距离当前时间比较远，则检查登录状态
		if ($this->_in_wechat() && startup_env::get('timestamp') - $this->session->get('_guest_login_') > 3600) {
			// 写入游客登录时间
			parent::_auto_login();
			$this->session->set('_guest_login_', startup_env::get('timestamp'));
		}
		$this->_require_login = $this->_auto_login;
	}

	/*
	 * 加密数据
	 */
	protected function _encryption ($data, &$out) {
		// 获取密钥
		$this->_state_secret_key = config::get('voa.auth_key');
		// 加密
		$out = base64_encode(authcode($data, $this->_state_secret_key, 'ENCODE'));
		return true;
	}

	/*
	 * 解密数据
	 */
	protected function _deciphering ($data, &$out) {
		// 获取密钥
		$this->_state_secret_key = config::get('voa.auth_key');
		// 解密
		$out = base64_decode($data);
		$out = authcode($out, $this->_state_secret_key, 'DECODE');
		return true;
	}

	/*
	 * 获取setting配置
	 */
	public static function fetch_cache_invite_setting() {
		$serv = &service::factory('voa_s_oa_invite_setting');
		$data = $serv->list_all();
		$arr = array();
		$_pluginid = -1;
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['type']) {
				$arr[$v['key']] = unserialize($v['value']);
			} else {
				$arr[$v['key']] = $v['value'];
			}
			if ($v['key'] == 'pluginid') {
				$_pluginid = $v['value'];
			}
		}

		self::_check_agentid($arr, 'invite');
		return $arr;
	}

}
