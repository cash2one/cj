<?php
/**
 * voa_c_admincp_office_vote_list
 * 企业后台/应用宝/微评选/投票列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_vote_list extends voa_c_admincp_office_vote_base {

	public function execute() {

		$searchDefault = array(
				'v_subject' => '',
				'm_username' => '',
				'v_begintime' => '',
				'v_endtime' => '',
		);
		$perpage = 10;

		$issearch = $this->request->get('issearch') ? 1 : 0;
		list($total, $multi, $searchBy, $list) = $this->_search_vote($issearch, $searchDefault, $perpage);
		$urlParam = $issearch ? array() : $searchBy;

		$this->view->set('searchBy', $searchBy);
		$this->view->set('issearch', $issearch);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('timestamp', startup_env::get('timestamp'));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('v_id'=>'')));
		$this->view->set('editUrlBase', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('v_id'=>'')));
		$this->view->set('viewUrlBase', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('v_id' => '')));
		$this->view->set('formDeleteUrl', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('searchActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		$this->output('office/vote/list');

	}

	/**
	 * 搜索投票
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 */
	protected function _search_vote($issearch, $searchDefault = array(), $perpage = 10) {

		/** 搜索条件 */
		$conditions = array();
		/** 搜索字段 */
		$searchBy = array();
		/** 如果为搜索 */
		if ($issearch) {
			foreach ($searchDefault AS $_k => $_v) {
				if (isset($_GET[$_k])) {
					$v = $this->request->get($_k);
					if ($_v != $v) {
						if ($_k == 'm_username') {
							if ($v && ($_m = $this->_get_member($v, false))) {
								$conditions['m_uid'] = $_m['m_uid'];
								$searchBy[$_k] = $_m['m_username'];
								unset($_m);
							}
						} elseif ($_k == 'v_begintime' || $_k == 'v_endtime') {
							if ($v && validator::is_date($v)) {
								$conditions[$_k] = rstrtotime($v);
								$searchBy[$_k] = $v;
							}
						} else {
							$conditions[$_k] = $v;
							$searchBy[$_k] = $v;
						}
					}
				}
			}
		}

		$list = array();
		$total = $this->_service_single('vote', $this->_module_plugin_id, 'count_all_by_conditions', $conditions);
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
			$tmp = $this->_service_single('vote', $this->_module_plugin_id, 'fetch_all_by_conditions', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);
			foreach ($tmp AS $_id => $_data) {
				$list[$_id] = $this->_format_vote($_data);
			}
			unset($tmp);
		}
		return array($total, $multi, array_merge($searchDefault, $searchBy), $list);
	}

}
