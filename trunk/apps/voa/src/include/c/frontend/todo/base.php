<?php
/**
 * 待办事项基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_todo_base extends voa_c_frontend_base {

	const STATUS_NORMAL = 1; /** 正常 */
	const STATUS_UPDATE = 2; /** 已更新 */
	const STATUS_REMOVE = 3; /** 已删除 */

	// 配置数组
	protected $settings = array();
	// 主表对象
	protected $main;
	// 更新对象
	protected $update;
	// 格式化对象
	protected $format;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->main =& service::factory('voa_s_oa_todo', array('pluginid' => startup_env::get('pluginid')));
		$this->format =& uda::factory('voa_uda_frontend_todo_format');
		$this->update =& uda::factory('voa_uda_frontend_todo_update');
		$this->settings = voa_h_cache::get_instance()->get('plugin.todo.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		if (array_key_exists($pluginid, $plugins)) {
			$this->_plugin = $plugins[$pluginid];
			startup_env::set('agentid', $this->_plugin['cp_agentid']);
			/** 加载提示语言 */
			language::load_lang($this->_plugin['cp_identifier']);
		}

		return true;
	}

	/**
	 * 获取待办事项配置
	 */
	public static function fetch_cache_todo_setting() {

		$serv = &service::factory('voa_s_oa_todo_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['tds_type']) {
				$arr[$v['tds_key']] = unserialize($v['tds_value']);
			} else {
				$arr[$v['tds_key']] = $v['tds_value'];
			}
		}

		self::_check_agentid($arr, 'todo');
		return $arr;
	}
}
