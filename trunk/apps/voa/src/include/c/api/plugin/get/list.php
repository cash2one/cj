<?php
/**
 * 获取插件列表
 * $Author$
 * $Id$
 */

class voa_c_api_plugin_get_list extends voa_c_api_plugin_base {

	public function execute() {

		$plugins = array();
		$serv_pd = &service::factory('voa_s_oa_common_plugin_display', array('pluginid' => startup_env::get('wbs_uid')));
		$list = $serv_pd->fetch_order_list($this->_member['m_uid']);

		$pluginids = array();

		foreach ($list as $_pd) {
			$this->arrange($plugins, $this->_plugins[$_pd['cp_pluginid']], $_pd);
			$pluginids[$_pd['cp_pluginid']] = $_pd['cp_pluginid'];
		}

		foreach ($this->_plugins as $_p) {
			if (array_key_exists($_p['cp_pluginid'], $pluginids)) {
				continue;
			}

			$this->arrange($plugins, $_p);
		}

		$this->_result = $plugins;
		return true;
	}

	/**
	 * 整理输出
	 * @param array $result 数据结果
	 * @param array $plugin 应用信息
	 * @param array $display
	 */
	public function arrange(&$result, $plugin, $display = array()) {

		// 测试代码, 临时, debug
		if (!in_array($plugin['cp_identifier'], array('notice', 'sign', 'project', 'dailyreport', 'askfor', 'askoff'))) {
			return true;
		}

		$icon = '';
		$scheme = config::get('voa.oa_http_scheme');
		if ($plugin['cp_icon'] && preg_match('/^[0-9]+$/', $plugin['cp_icon'])) {
			/** 以数字开头的路径被认为是本地上传的附件ID */
			$icon = voa_h_attach::attachment_url($plugin['cp_icon']);
		} else {
			$icon = $scheme.$this->_set['domain'].voa_h_func::cp_static_url().'images/application/'.$plugin['cp_icon'];
		}

		$result[] = array(
			'pluginid' => $plugin['cp_pluginid'],
			'identifier' => $plugin['cp_identifier'],
			'groupid' => $plugin['cpg_id'],
			'agentid' => $plugin['cp_agentid'],
			'pluginname' => $plugin['cp_name'],
			'description' => $plugin['cp_description'],
			'version' => $plugin['cp_version'],
			'isfav' => empty($display) ? "0" : $display['cpd_isfav'],
			'ordernum' => empty($display) ? "0" : $display['cpd_ordernum'],
			'icon' => $icon
		);

		return true;
	}
}

