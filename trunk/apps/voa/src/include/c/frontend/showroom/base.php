<?php
/**
 * base.php
 * 培训/前端基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_frontend_showroom_base extends voa_c_frontend_base {

	/** 当前应用唯一标识符 */
	protected $_identifier = 'showroom';

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_mobile_tpl = true;
		// 获取派单配置
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.'.$this->_identifier.'.setting', 'oa');
		// 应用ID
		$pluginid = $this->_p_sets['pluginid'];
		// 设置当前应用环境
		startup_env::set('pluginid', $pluginid);
		// 应用列表
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');

		// 判断应用是否存在
		if (!array_key_exists($pluginid, $plugins)) {
			$this->_error_message('您无权使用本应用');
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
	 *
	 * @return array
	 */
	public static function fetch_cache_showroom_setting() {

		// 应用唯一标识名
		$identifier = 'showroom';
		$sets = array();
		$d_showroom_setting = new voa_d_oa_showroom_setting();
		foreach ($d_showroom_setting->list_all() as $s) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $s['type']) {
				$sets[$s['key']] = unserialize($s['value']);
			} else {
				$sets[$s['key']] = $s['value'];
			}
		}

		self::_check_agentid($sets, 'showroom');
		return $sets;
	}

}
