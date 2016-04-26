<?php
/**
 * base.php
 * 红包前端基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_redpack_base extends voa_c_frontend_base {

	protected function _before_action($action) {

		if (! parent::_before_action($action)) {
			return false;
		}

		// 指定使用/mobile/目录的模板
		$this->_mobile_tpl = true;
		// 定义页面默认的标题<title></title>
		$this->view->set('navtitle', '红包');
		return true;
	}

	protected function _after_action($action) {

		if (! parent::_after_action($action)) {
			return false;
		}

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.redpack.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = !empty($this->_p_sets['pluginid']) ? $this->_p_sets['pluginid'] : 0;
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
	public static function fetch_cache_redpack_setting() {

		$serv = &service::factory('voa_s_oa_redpack_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->list_all();
		$arr = array();
		$_pluginid = -1;
		foreach ($data as $v) {
			if (voa_d_oa_redpack_setting::TYPE_ARRAY == $v['type']) {
				$arr[$v['key']] = unserialize($v['value']);
			} else {
				$arr[$v['key']] = $v['value'];
			}

			if ($v['key'] == 'pluginid') {
				$_pluginid = $v['value'];
			}
		}

		self::_check_agentid($arr, 'redpack');
		return $arr;
	}

}
