<?php

/**
 * voa_c_admincp_office_activity_base
 * 企业后台 - 活动报名 - 基本控制器
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_activity_base extends voa_c_admincp_office_base {

	/** 应用设置 */
	protected $_p_sets = array();

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		// 读取应用设置信息
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.activity.setting', 'oa');
		// 读取所有应用信息
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		// 设置当前应用id到环境变量里
		startup_env::set('pluginid', $this->_p_sets['pluginid']);
		// 设置当前应用的微信企业号应用id到环境变量里
		startup_env::set('agentid', $plugins[$this->_p_sets['pluginid']]['cp_agentid']);
		return true;
	}

	/**
	 *活动状态判断
	 * @param int start
	 * @param int end
	 *return string type
	 */
	protected function _check_type($start, $end) {

		$time = time();//当前时间
		$type = '';
		if ($start <= $time && $time <= $end) {
			$type = '已开始的';
		}
		if ($start > $time) {
			$type = '未开始的';
		}
		if ($end < $time) {
			$type = '已结束的';
		}
		return $type;
	}

}
