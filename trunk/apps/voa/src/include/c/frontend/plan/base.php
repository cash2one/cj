<?php
/**
 * 日程基类
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_plan_base extends voa_c_frontend_base {
	// 配置数组
	public $settings = array();

	// 主表对象
	public $main;

	// 成员表对象
	public $member;

	// 会员表对象
	public $user;

	// 验证对象
	public $format;

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 引用主表类
		$this->main =& service::factory('voa_s_oa_plan', array('pluginid' => startup_env::get('pluginid')));

		// 引用成员表类
		$this->member =& service::factory('voa_d_oa_plan_mem', array('pluginid' => startup_env::get('pluginid')));

		// 引用会员表类
		$this->user =& service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));

		// 引用验证类
		$this->format =& uda::factory('voa_uda_frontend_plan_format');

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		// 加载插件配置
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.plan.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = $this->_p_sets['pluginid'];
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
	 * 获取日程配置
	 */
	public static function fetch_cache_plan_setting() {

		$serv = & service::factory('voa_s_oa_plan_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['pls_type']) {
				$arr[$v['pls_key']] = unserialize($v['pls_value']);
			} else {
				$arr[$v['pls_key']] = $v['pls_value'];
			}
		}

		self::_check_agentid($arr, 'plan');
		return $arr;
	}
}
