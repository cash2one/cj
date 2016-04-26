<?php
/**
 * uda_frontend_application_get
 * 应用uda
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_application_get extends voa_uda_frontend_application_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 当前站点的应用列表
	 * @param array $available
	 * @return array
	 */
	public function site_plugin_list($available = array()) {

		// 应用分组列表
		$group_list = $this->_module_group_list(0, true);

		// 应用列表
		$list = array();
		// 将应用按分组列表
		foreach ($this->_plugin_list(0, $available, false) as $plugin) {
			$list[$plugin['cmg_id']][$plugin['cp_pluginid']] = $plugin;
		}

		// 输出带分组信息的应用列表
		$plugin_list = array();
		foreach ($group_list as $cmg_id => $group) {
			if (empty($list[$cmg_id])) {
				continue;
			}
			$plugin_list[$cmg_id]['group'] = $group;
			foreach ($list[$cmg_id] as $_plugin_id => $_plugin) {
				$plugin_list[$cmg_id]['list'][$_plugin_id] = $this->plugin_format($_plugin);
			}
		}

		return $plugin_list;
	}

	/**
	 * 以应用套件进行分组的应用列表
	 */
	public function suite_agent_list() {

		// 应用分组列表
		$group_list = $this->_plugin_group_list(0, true);

		// 获取所有有效的应用，由于套件方式无具体的启用状态因此列出除系统隐藏外的所有应用
		$available = array(
			voa_d_oa_common_plugin::AVAILABLE_NEW,
			voa_d_oa_common_plugin::AVAILABLE_WAIT_OPEN,
			voa_d_oa_common_plugin::AVAILABLE_WAIT_CLOSE,
			voa_d_oa_common_plugin::AVAILABLE_WAIT_DELETE,
			voa_d_oa_common_plugin::AVAILABLE_OPEN,
			voa_d_oa_common_plugin::AVAILABLE_CLOSE,
			voa_d_oa_common_plugin::AVAILABLE_DELETE,
		);

		// 应用列表
		$list = array();
		// 将应用按分组列表
		foreach ($this->_plugin_list(0, $available, false) as $plugin) {
			$list[$plugin['cpg_id']][$plugin['cp_pluginid']] = $plugin;
		}

		// 输出带分组信息的应用列表
		$plugin_list = array();
		foreach ($group_list as $_cpg_id => $_group) {
			if (empty($list[$_cpg_id])) {
				continue;
			}
			$plugin_list[$_cpg_id]['group'] = $_group;
			foreach ($list[$_cpg_id] as $_plugin_id => $_plugin) {
				$plugin_list[$_cpg_id]['list'][$_plugin_id] = $this->plugin_format($_plugin);
			}
		}

		return $plugin_list;
	}

}
