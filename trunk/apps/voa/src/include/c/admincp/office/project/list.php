<?php
/**
 * voa_c_admincp_office_project_list
 * 企业后台/微办公管理/工作台/项目列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_project_list extends voa_c_admincp_office_project_base {

	public function execute() {

		$searchDefault = array(
				'p_subject' => '',
				'm_username' => '',
				'p_begintime' => '',
				'p_endtime' => '',
		);
		$perpage = 10;

		$issearch = $this->request->get('issearch') ? 1 : 0;
		list($total, $multi, $searchBy, $list) = $this->_search_project($this->_module_plugin_id, $issearch, $searchDefault, $perpage);
		$urlParam = $issearch ? array() : $searchBy;

		$this->view->set('searchBy', $searchBy);
		$this->view->set('issearch', $issearch);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('timestamp', startup_env::get('timestamp'));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('p_id'=>'')));
		$this->view->set('editUrlBase', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('p_id'=>'')));
		$this->view->set('viewUrlBase', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('p_id' => '')));
		$this->view->set('formDeleteUrl', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('searchActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('advancedUrlBase', $this->cpurl($this->_module, $this->_operation, 'advanced', $this->_module_plugin_id, array('p_id' => '')));

		$this->output('office/project/list');

	}

	/**
	 * 搜索项目
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 */
	protected function _search_project($cp_pluginid, $issearch, $searchDefault = array(), $perpage = 10) {

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
						} elseif ($_k == 'p_begintime' || $_k == 'p_endtime') {
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
		$total = $this->_service_single('project', $cp_pluginid, 'count_by_conditions', $conditions);
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
			$tmp = $this->_service_single('project', $cp_pluginid, 'fetch_all_by_conditions', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);
			foreach ($tmp AS $_id => $_data) {
				$list[$_id] = $this->_format_project($_data);
			}
			unset($tmp);
		}
		return array($total, $multi, array_merge($searchDefault, $searchBy), $list);

	}

}
