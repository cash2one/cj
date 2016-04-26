<?php

/**
 * voa_c_admincp_office_askfor_list
 * 企业后台 - 活动报名 - 列表
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_activity_list extends voa_c_admincp_office_activity_base {

	public function execute() {

		/** 搜索默认值 */
		$searchDefaults = array(
			'ac_time_after' => '',
			'ac_time_before' => '',
			'm_username' => '',
			'ac_subject' => '',
			'ac_type' => '999',//全部
		);
		$issearch = $this->request->get('issearch') ? 1 : 0;


		list($total, $multi, $list, $searchBy) = $this->_activity_search($issearch, $searchDefaults);
		$this->_format_data($list);

		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('search_by', $searchBy);
		$this->view->set('issearch', $issearch);
		$this->view->set('form_search_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('acid' => '')));
		$this->view->set('view_url_base', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('acid' => '')));
		$this->view->set('edit_url_base', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('acid' => '')));

		$this->output('office/activity/list');

	}

	/**
	 * 搜索活动报名
	 * @param number $issearch
	 * @param array $searchDefaults
	 * @param array $searchBy
	 * @param number $perpage
	 * @return array(total, multi, list)
	 */
	protected function _activity_search($issearch = 0, $searchDefaults = array(), $perpage = 12) {
		/** 搜索条件 */
		$searchBy = array();
		$conds = array();
		if ($issearch) {
			//查询条件
			foreach ($searchDefaults AS $_k => $_v) {
				if (isset($_GET[$_k]) && $this->request->get($_k) != $_v) {
					if ($this->request->get($_k) != null) {
						$searchBy[$_k] = $this->request->get($_k);
					} else {
						$searchBy[$_k] = $_v;
					}
				}
			}
			$searchBy = array_merge($searchDefaults, $searchBy);
		} else {
			$searchBy = $searchDefaults;
		}

		//组合搜索条件
		if (!empty($searchBy)) {

			$this->_add_condi($conds, $searchBy);

		}
		$list = array();
		$multi = null;
		//获取数据
		$serv = &service::factory('voa_s_oa_activity');
		$total = $serv->count_by_conds($conds);
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);

			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = $perpage;
			$orderby['updated'] = 'DESC';

			$list = $serv->list_by_conds($conds, $page_option, $orderby);
		}
		return array($total, $multi, $list, $searchBy);

	}

	/**
	 * 展示数据格式化
	 * @param array $list
	 */
	protected function _format_data(&$list) {
		foreach ($list as $key => $value) {
			$list[$key]['_type'] = $this->_check_type($value['start_time'], $value['end_time']);
			$list[$key]['_created'] = rgmdate($value['created'], 'Y-m-d H:i');
		}

	}

	/**
	 *状态判断
	 * @param int conds
	 * @param array searchBy
	 */
	protected function _add_condi(&$conds, $searchBy) {
		if (!empty($searchBy['ac_time_after'])) { //发起时间
			$conds['created >= ?'] = rstrtotime($searchBy['ac_time_after']);
		}
		if (!empty($searchBy['ac_time_before'])) {//发起时间
			$conds['created <= ?'] = rstrtotime($searchBy['ac_time_before']);
		}
		if (!empty($searchBy['ac_subject'])) {//发起标题
			$conds["title like ?"] = "%" . $searchBy['ac_subject'] . "%";
		}
		if (!empty($searchBy['m_username'])) {//发起人
			$conds['uname like ?'] = "%" . $searchBy['m_username'] . "%";
		}
		//时间判断
		$time = time();//当前时间
		switch ($searchBy['ac_type']) {
			case 1://进行中
				$conds['start_time <= ?'] = $time;
				$conds['end_time >= ?'] = $time;
				break;
			case 2://未开始
				$conds['start_time > ?'] = $time;
				$conds['end_time >= ?'] = $time;
				break;
			case 3://已结束
				$conds['end_time < ?'] = $time;
				$conds['start_time < ?'] = $time;
				break;
			default:
				unset($conds);
		}
	}
}
