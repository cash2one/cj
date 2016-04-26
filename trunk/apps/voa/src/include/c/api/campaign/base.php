<?php
/**
 * 活动接口基类
 * $Author$
 * $Id$
 */
class voa_c_api_campaign_base extends voa_c_api_base {
	protected $db; // 数据库操作类(直接使用sql语句形式)

	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		if (! parent::_before_action($action)) {
			return false;
		}

		$this->db = new voa_d_oa_campaign_db();
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.campaign.setting', 'oa');

		// 取应用插件信息
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		if (array_key_exists($pluginid, $plugins)) {
			$this->_plugin = $plugins[$pluginid];
			startup_env::set('agentid', $this->_plugin['cp_agentid']);
			// 加载提示语言
			language::load_lang($this->_plugin['cp_identifier']);
		}

		if (empty($plugins[$pluginid]) || voa_d_oa_common_plugin::AVAILABLE_OPEN != $plugins[$pluginid]['cp_available']) {
			return false;
		}

		return true;
	}

	/**
	 * 获取查看详情的url
	 *
	 * @param string $url url地址
	 * @param int $id
	 * @return boolean
	 */
	public function get_view_url(&$url, $id) {

		/**
		 * 组织查看链接
		 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme . $this->_setting['domain'].'/campaign/view/?id='.$id.'?pluginid='.startup_env::get('pluginid'));
		return $url;
	}
}
