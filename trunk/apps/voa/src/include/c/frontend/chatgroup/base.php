<?php
/**
 * 群聊
 * $Author$
 * $Id$
 */

class voa_c_frontend_chatgroup_base extends voa_c_frontend_base {

	protected function _before_action($action) {

		$this->_mobile_tpl = true;
		return parent::_before_action($action);
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.chatgroup.setting', 'oa');

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
	 * 获取配置信息
	 */
	public static function fetch_cache_chatgroup_setting() {

		$serv = new voa_d_oa_chatgroup_setting();
		$data = $serv->list_all();
		$arr = array();
		$_pluginid = - 1;
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['cgs_type']) {
				$arr[$v['cgs_key']] = unserialize($v['cgs_value']);
			} else {
				$arr[$v['cgs_key']] = $v['cgs_value'];
			}

			if ($v['cgs_key'] == 'pluginid') {
				$_pluginid = $v['cgs_value'];
			}
		}

		self::_check_agentid($arr, 'chatgroup');
		return $arr;
	}
}

