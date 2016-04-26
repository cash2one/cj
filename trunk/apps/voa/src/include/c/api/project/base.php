<?php
/**
 * voa_c_api_project_base
 * 任务基础控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_project_base extends voa_c_api_base {

	/** 插件配置信息 */
	protected $_p_sets = null;
	/** 插件id */
	protected $_pluginid = 0;

	protected $_plugin = null;

	protected $_plugin_identifier = 'project';

	/** 参与人员的状态代码映射关系 */
	protected $_status_maps = array(
			voa_d_oa_project_mem::STATUS_NORMAL => 'normal',
			voa_d_oa_project_mem::STATUS_UPDATE => 'normal',
			voa_d_oa_project_mem::STATUS_OUTOF => 'outof',
			voa_d_oa_project_mem::STATUS_CC => 'cc',
			voa_d_oa_project_mem::STATUS_QUIT => 'quit',
			voa_d_oa_project_mem::STATUS_REMOVE => 'remove'
		);

	public function __construct() {
		parent::__construct();
	}

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		// 插件配置信息
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.project.setting', 'oa');

		// 取应用插件信息
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		foreach ($plugins as $p) {
			if ($p['cp_identifier'] == $this->_plugin_identifier) {
				$this->_pluginid = $p['cp_pluginid'];
				startup_env::set('pluginid', $this->_pluginid);
				$this->_plugin = $p;
				break;
			}
		}
		startup_env::set('agentid', $this->_plugin['cp_agentid']);

		return true;
	}

	protected function _after_action($action) {
		if (!parent::_after_action($action)) {
			return false;
		}

		return true;
	}

	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $p_id 会议记录信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $p_id) {
		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/project/view/'.$p_id.'?pluginid='.$this->_pluginid);

		return true;
	}

}
