<?php
/**
 * voa_c_admincp_office_reimburse_base
 * 企业后台/微办公管理/报销/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_reimburse_base extends voa_c_admincp_office_base {

	/**
	 * 报销的系统变量
	 * @var array
	 */
	protected $_sets = array();

	protected function _before_action($action){

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_sets = voa_h_cache::get_instance()->get('plugin.reimburse.setting', 'oa');

		$this->view->set('plugin_setting_url', $this->cpurl('setting', 'reimburse', 'modify', $this->_module_plugin_id));
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 搜索指定条件的报销数据
	 * @param number $perpage
	 * @return array(total, multi, search_by, list)
	 */
	protected function _reimburse_search($perpage = 15) {

		$search_default_fields = array(
				'm_uid' => '',//申请人id
				'm_username' => '',//申请人用户名
				'rbpc_username' => '',//审核人名字
				'rb_subject' => '',//报销主题.
				//'rb_type' => '',//报销分类.
				'rb_time_after' => '',//申请时间范围：此时间之后.
				'rb_time_before' => '',//申请时间范围：此时间之前.
				'rb_status' => '',//审批状态
		);

		//搜索条件
		$conditions = array();
		//搜索字段
		$search_by = array();

		$uda_reimburse_search = &uda::factory('voa_uda_frontend_reimburse_search');
		$uda_reimburse_search->reimburse_conditions($search_default_fields, $search_by, $conditions, array('shard_key' => $this->_module_plugin_id));

		$list = array();
		$total = $this->_service_single('reimburse', $this->_module_plugin_id, 'count_by_conditions', $conditions);
		$multi = '';
		if ($total > 0) {
			$pagerOptions = array(
					'total_items' => $total,
					'per_page' => $perpage,
					'current_page' => $this->request->get('page'),
					'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$list = $this->_service_single('reimburse', $this->_module_plugin_id, 'fetch_by_conditions', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);
			$m_uids = array();
			foreach ($list AS $_data) {
				if (!isset($m_uids[$_data['m_uid']])) {
					$m_uids[$_data['m_uid']] = $_data['m_uid'];
				}
			}
			$users = voa_h_user::get_multi($m_uids);

			$uda_reimburse_format = &uda::factory('voa_uda_frontend_reimburse_format');
			$uda_member_format = &uda::factory('voa_uda_frontend_member_format');

			foreach ($list as &$_data) {
				$_member = array();
				if (isset($users[$_data['m_uid']])) {
					$_member = $users[$_data['m_uid']];
					$uda_member_format->format($_member);
				}
				$uda_reimburse_format->reimburse($_data);
				$_data['_realname'] = isset($_member['_realname']) ? $_member['_realname'] : $_data['m_username'];
				$_data['_department'] = isset($_member['_department']) ? $_member['_department'] : '';
				$_data['_job'] = isset($_member['_job']) ? $_member['_job'] : '';
				$_data['_expend'] = round($_data['rb_expend']/100, 2);
				unset($_member);
			}
			unset($_data);
		}

		return array($total, $multi, $search_by, $list);
	}

}
