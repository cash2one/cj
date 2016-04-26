<?php

/**
 * 审批基类
 * $Author$
 * $Id$
 */
class voa_c_frontend_activity_base extends voa_c_frontend_base {

	protected $_plugin_identifier;

	protected $_auto_login = true;

	protected function _auto_login() {
		// 如果游客上次访问时间距离当前时间比较远，则检查登录状态
		if ($this->_in_wechat() && startup_env::get('timestamp') - $this->session->get('_guest_login_') > 3600) {
			// 写入游客登录时间
			parent::_auto_login();
			$this->session->set('_guest_login_', startup_env::get('timestamp'));
		}
		$this->_require_login = $this->_auto_login;
	}

	/**
	 * 解码 acid
	 * @param string $acid 字串
	 * @return unknown|Ambigous <boolean, string>
	 */
	protected function _decode_acid($acid) {

		$acid_i = (int)$acid;
		if (strlen($acid_i) == strlen($acid)) {
			return $acid;
		}

		$acid = urldecode($acid);
		$acid = urldecode($acid);
		$acid = str_replace(" ", "+", $acid);
		$acid = authcode($acid, 'zhoutao', 'DECODE', '0');
		return $acid;
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 标记当前使用新版的手机模板目录
		// 此成员设置仅为新旧手机版本模板文件同时存在的一个过渡性判断
		// 未来旧版手机模板文件全部更换为新模板后可移除本设置
		// 此设置对应voa_c_frontend_base::_output()方法
		$this->_mobile_tpl = true;

		// 获取当前应用的唯一标识名
		list(, , , $this->_plugin_identifier) = explode('_', rstrtolower(__CLASS__));

		// 将应用唯一标识名注入模板变量
		$this->view->set('plugin_identifier', $this->_plugin_identifier);
		$this->view->set('pluginid', startup_env::get('pluginid'));


		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.activity.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		// 如果应用信息不存在
		if (!array_key_exists($pluginid, $plugins)) {
			$this->_error_message('应用信息丢失，请重新开启');
			return true;
		}

		// 获取应用信息
		$this->_plugin = $plugins[$pluginid];

		// 判断应用是否关闭
		if ($this->_plugin['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
			$this->_error_message('本应用尚未开启 或 已关闭，请联系管理员启用后使用');
			return true;
		}

		startup_env::set('agentid', $this->_plugin['cp_agentid']);
		/** 加载提示语言 */
		language::load_lang($this->_plugin['cp_identifier']);

		return true;
	}

	/**
	 * 获取审批配置
	 */
	public static function fetch_cache_activity_setting() {

		$serv = &service::factory('voa_s_oa_activity_setting');
		$data = $serv->list_all();
		$arr = array();
		$_pluginid = 0;
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

		self::_check_agentid($arr, 'activity');
		return $arr;
	}

}
