<?php
/**
 * base.php
 * 消息/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_frontend_talk_base extends voa_c_frontend_base {

	/** 当前应用唯一标识符 */
	protected $_identifier = 'talk';

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 获取派单配置
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.'.$this->_identifier.'.setting', 'oa');
		return true;
		// 应用ID
		$pluginid = $this->_p_sets['pluginid'];
		// 设置当前应用环境
		startup_env::set('pluginid', $pluginid);
		// 应用列表
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');

		// 判断应用是否存在
		if (!array_key_exists($pluginid, $plugins)) {
			//$this->_error_message('您无权使用本应用');	//debug
		}

		// 应用信息
		$this->_plugin = $plugins[$pluginid];
		startup_env::set('agentid', $this->_plugin['cp_agentid']);

		// 加载语言包
		language::load_lang($this->_plugin['cp_identifier']);

		// 注入浏览器标题名
		$this->view->set('navtitle', $this->_plugin['cp_name']);

		return true;
	}

	/**
	 * 应用配置缓存
	 * @return array
	 */
	public static function fetch_cache_talk_setting() {

		return array();
		// 应用唯一标识名
		$identifier = 'train';
		$sets = array();
		$d_train_setting = new voa_d_oa_train_setting();
		foreach ($d_train_setting->list_all() as $s) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $s['type']) {
				$sets[$s['key']] = unserialize($s['value']);
			} else {
				$sets[$s['key']] = $s['value'];
			}
		}

		if (!isset($sets['pluginid'])) {

			$serv_plugin = &service::factory('voa_s_oa_common_plugin');
			$plugin = $serv_plugin->fetch_by_identifier($identifier);
			$pluginid = array_shift($plugin);
			$sets['pluginid'] = $pluginid;
			$sets['agentid'] = $plugin['cp_agentid'];
		}

		return $sets;
	}

}
