<?php
/**
 * base.php
 * 移动派单/前端基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_frontend_workorder_base extends voa_c_frontend_base {

	/** 当前应用唯一标识符 */
	protected $_identifier = 'workorder';

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 注入浏览器标题名
		$this->view->set('navtitle', $this->_plugin['cp_name']);

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

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

		// 判断应用ID是否存在
		if (!isset($plugins[$pluginid])) {
			$this->_error_message('应用信息丢失，请重新开启');
		}

		// 应用信息
		$this->_plugin = $plugins[$pluginid];
		// 判断应用是否关闭
		if ($this->_plugin['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
			$this->_error_message('本应用尚未开启 或 已关闭，请联系管理员启用后使用');
		}
		startup_env::set('agentid', $this->_plugin['cp_agentid']);

		// 加载语言包
		language::load_lang($this->_plugin['cp_identifier']);

		return true;
	}

	// 显示操作菜单
	public static function show_menu($data, $plugin) {


		return '订单查询功能暂未开放';
	}

	/**
	 * 应用配置缓存
	 *
	 * @return array
	 */
	public static function fetch_cache_workorder_setting() {

		// 应用唯一标识名
		$identifier = 'workorder';
		$sets = array();
		$d_workorder_setting = new voa_d_oa_workorder_setting();
		foreach ($d_workorder_setting->list_all() as $s) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $s['type']) {
				$sets[$s['key']] = unserialize($s['value']);
			} else {
				$sets[$s['key']] = $s['value'];
			}
		}

		self::_check_agentid($sets, 'workorder');
		return $sets;
	}

}
