<?php

/**
 * $Author$
 * $Id$
 */
class voa_uda_frontend_association_abstract extends voa_uda_frontend_base {

	// 最大附件数
	protected $_attach_max = 5;
	// 配置信息
	protected $_sets = array();
	/** 应用信息 */
	protected $_plugin = array();

	public function __construct() {

		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.community.setting', 'oa');
		/** 取应用插件信息 */
		$pluginid = $this->_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		// 如果应用信息不存在
		if (!array_key_exists($pluginid, $plugins)) {
			$this->errcode = 1001;
			$this->errmsg = '应用信息丢失，请重新开启';

			return false;
		}
		// 获取应用信息
		$this->_plugin = $plugins[$pluginid];
		startup_env::set('agentid', $this->_plugin['cp_agentid']);
		// 判断应用是否关闭
		if ($this->_plugin['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
			$this->errcode = 1002;
			$this->errmsg = '本应用尚未开启 或 已关闭，请联系管理员启用后使用';

			return false;
		}
	}



}
