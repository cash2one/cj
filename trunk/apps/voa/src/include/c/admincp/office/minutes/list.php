<?php
/**
 * voa_c_admincp_office_minutes_list
 * 企业后台/微办公管理/会议记录/列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_minutes_list extends voa_c_admincp_office_minutes_base {

	public function execute() {

		/** 搜索默认值 */
		$search_default_fields = array(
				'm_uid' => 0,
				'mi_username' => '',//会议记录发起人姓名
				'begintime' => '',//发起时间范围：开始时间
				'endtime' => '',//发起时间范围：结束时间
				'mim_username' => '',//审批人姓名
				'mi_subject' => '',//会议主题关键词
		);

		$perpage = 15;
		list($total, $multi, $search_by, $list) = $this->_search_minutes($this->_module_plugin_id, $search_default_fields, $perpage);
		$this->view->set('search_by', $search_by);
		$this->view->set('issearch', $this->request->get('issearch'));
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('timestamp', startup_env::get('timestamp'));
		$this->view->set('list_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('view_url_base', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('mi_id'=>'')));
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('mi_id'=>'')));
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('form_search_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		$this->output('office/minutes/minutes_list');
	}

	/**
	 * 搜索会议记录
	 * @param number $cp_pluginid
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 */
	protected function _search_minutes($cp_pluginid, $search_default_fields = array(), $perpage = 10) {

		// 搜索条件
		$conditions = array();
		// 搜索字段
		$search_by = array();

		// 根据传递的搜索条件构造查询需要的条件
		$uda_minutes_search = &uda::factory('voa_uda_frontend_minutes_search');
		$uda_minutes_search->minutes_conditions($search_default_fields, $search_by, $conditions, array('shard_key' => $this->_module_plugin_id));

		$list = array();
		$total = $this->_service_single('minutes', $cp_pluginid, 'count_by_conditions', $conditions);
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
			$list = $this->_service_single('minutes', $cp_pluginid, 'fetch_by_conditions', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);

			// 会议记录发起人信息
			$member_list = array();
			$uda_member = &uda::factory('voa_uda_frontend_member_format');
			$uda_member->data_list($list, 'm_uid', $member_list);

			// 格式化会议记录列表
			$uda_minutes_format = &uda::factory('voa_uda_frontend_minutes_format');
			$uda_minutes_format->minutes_list($list);

			// 需要读取的用户信息
			$member_fields = array('_department', '_job', 'm_username', 'm_uid', '_gender', '_realname');
			foreach ($list as &$row) {
				foreach ($member_fields as $_k) {
					$row[$_k] = isset($member_list[$row['m_uid']][$_k]) ? $member_list[$row['m_uid']][$_k] : '';
				}
			}
		}

		return array($total, $multi, $search_by, $list);
	}

}
