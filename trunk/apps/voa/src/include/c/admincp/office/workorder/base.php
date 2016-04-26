<?php
/**
 * base.php
 * 云工作后台/移动派单/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_workorder_base extends voa_c_admincp_office_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 初始化搜索表单数据
	 * @param array $request 存在外部请求
	 */
	protected function _init_search_form($request = array()) {

		// 避免重复调用
		if (defined('_SEARCH_FORM_')) {
			return;
		}
		define('_SEARCH_FORM_', true);

		// 初始化表单搜索条件
		$search_by = array(
			'sender' => '',
			'operator' => '',
			'ordertime_start' => '',
			'ordertime_end' => '',
			'wostate' => '',
			'woid' => '',
		);
		foreach ($search_by as $_key => $_value) {
			if (isset($request[$_key])) {
				$search_by[$_key] = $request[$_key];
			}
		}

		// 获取工单状态集合
		$uda_get = &uda::factory('voa_uda_frontend_workorder_get');
		$wostate_list = $uda_get->wostate;

		// 模板注入搜索url
		$this->view->set('search_action_url', $this->cpurl($this->_module, $this->_operation, 'search'
				, $this->_module_plugin_id));
		// 模板注入搜索条件
		$this->view->set('search_by', $search_by);
		// 模板注入工单状态集合
		$this->view->set('wostate_list', $wostate_list);
	}

	/**
	 * 格式化工单列表
	 * @param array $source 原始列表数据
	 * @return multitype:
	 */
	protected function _format_workorder_list($source) {

		// 所有派单人uid
		$uids = array();
		foreach ($source as $_wo) {
			if (!isset($uids[$_wo['uid']])) {
				$uids[$_wo['uid']] = $_wo['uid'];
			}
			if ($_wo['operator_uid'] && !isset($uids[$_wo['operator_uid']])) {
				$uids[$_wo['operator_uid']] = $_wo['operator_uid'];
			}
		}

		// 查询用户信息
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch_all_by_ids($uids);
		voa_h_user::push($users);

		// 初始化输出
		$list = array();
		// 载入工单格式化
		$uda_format = &uda::factory('voa_uda_frontend_workorder_format');
		// 循环遍历列表以格式化单条数据
		foreach ($source as $_wo) {
			$uda_format->workorder($_wo, $_wo, 'Y-m-d H:i');
			$sender_info = voa_h_user::get($_wo['uid']);
			$list[] = array(
				'woid' => $_wo['woid'],
				'sender' => $sender_info ? $sender_info['m_username'] : '--',
				'wostate_name' => $_wo['wostate_name'],
				'contacter' => $_wo['contacter'],
				'phone' => $_wo['phone'],
				'address' => $_wo['address'],
				'remark' => $_wo['remark'],
				'ordertime' => $_wo['ordertime']
			);
		}

		return $list;
	}

}
