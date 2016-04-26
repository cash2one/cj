<?php
/**
 * voa_c_admincp_office_askoff_list
 * 企业后台/微办公管理/请假/列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askoff_list extends voa_c_admincp_office_askoff_base {

	public function execute() {

		/** 搜索默认值 */
		$search_default_fields = array(
				'm_uid' => 0,
				'ao_username' => '',//请假人姓名
				'ao_type' => '-1',//请假类型
				'ao_begintime' => '',//请假开始时间
				'ao_endtime' => '',//请假结束时间
				'aopc_username' => '',//审批人姓名
				'ao_status' =>'-1',//审批状态
				'ao_subject' => '',//请假关键词
		);

		$perpage = 15;
		list($total, $multi, $search_by, $list) = $this->_search_askoff($this->_module_plugin_id, $search_default_fields, $perpage);
		$this->view->set('searchBy', $search_by);
		$this->view->set('issearch', $this->request->get('issearch'));
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('askoff_status', $this->_uda_base->askoff_status);
		$this->view->set('askoff_types', isset($this->_p_sets['types']) ? $this->_p_sets['types'] : array());
		$this->view->set('timestamp', startup_env::get('timestamp'));
		$this->view->set('viewUrlBase', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('ao_id'=>'')));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('ao_id'=>'')));
		$this->view->set('formDeleteUrl', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('searchActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		$this->output('office/askoff/askoff_list');
	}

	/**
	 * 搜索请假记录
	 * @param number $cp_pluginid
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 */
	protected function _search_askoff($cp_pluginid, $search_default_fields = array(), $perpage = 10) {

		//搜索条件
		$conditions = array();
		//搜索字段
		$search_by = array();

		$uda_askoff_search = &uda::factory('voa_uda_frontend_askoff_search');
		$uda_askoff_search->askoff_conditions($search_default_fields, $search_by, $conditions, array('shard_key' => $this->_module_plugin_id));

		$list = array();
		$total = $this->_service_single('askoff', $cp_pluginid, 'count_by_conditions', $conditions);
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
			$list = $this->_service_single('askoff', $cp_pluginid, 'fetch_by_conditions', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);
			$m_uids = array();
			foreach ($list AS $_data) {
				if (!isset($m_uids[$_data['m_uid']])) {
					$m_uids[$_data['m_uid']] = $_data['m_uid'];
				}
			}
			$users = voa_h_user::get_multi($m_uids);

			$uda_askoff_format = &uda::factory('voa_uda_frontend_askoff_format');
			$uda_member_format = &uda::factory('voa_uda_frontend_member_format');
			foreach ($list as &$_data) {
				$_member = array();
				if (isset($users[$_data['m_uid']])) {
					$_member = $users[$_data['m_uid']];
					$uda_member_format->format($_member);
				}
				$uda_askoff_format->askoff($_data);
				$_data = array_merge($_member, $_data);
				$_data['_status'] = isset($this->_uda_base->askoff_status[$_data['ao_status']]) ? $this->_uda_base->askoff_status[$_data['ao_status']] : '';
				$_data['_type'] = isset($this->_p_sets['types'][$_data['ao_type']]) ? $this->_p_sets['types'][$_data['ao_type']] : '';
				unset($_member);
			}
			unset($_data);
		}

		return array($total, $multi, $search_by, $list);
	}

}
