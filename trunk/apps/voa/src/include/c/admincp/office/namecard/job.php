<?php
/**
 * voa_c_admincp_office_namecard_job
 * 企业后台/微办公管理/职务管理/列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_namecard_job extends voa_c_admincp_office_namecard_base {

	protected $_member_list = array();

	public function execute() {

		$searchDefault = array(
				'm_username' => '',
				'ncj_name' => ''
		);
		$perpage = 10;

		$issearch = $this->request->get('issearch') ? 1 : 0;
		list($total, $multi, $searchBy, $list) = $this->_search_job($this->_module_plugin_id, $issearch, $searchDefault, $perpage);
		$urlParam = $issearch ? array() : $searchBy;

		$this->view->set('searchBy', array_merge($searchDefault, $searchBy));
		$this->view->set('issearch', $issearch);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('timestamp', startup_env::get('timestamp'));
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('editActionUrl', $this->cpurl($this->_module, $this->_operation, 'jobedit', $this->_module_plugin_id));

		$this->output('office/namecard/job_list');

	}

	protected function _search_job($cp_pluginid, $issearch, $searchDefault = array(), $perpage = 10) {
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
						} else {
							$conditions[$_k] = $v;
							$searchBy[$_k] = $v;
						}
					}
				}
			}
		}

		$list = array();
		$total = $this->_service_single('namecard_job', $cp_pluginid, 'count_by_conditions', $conditions);
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
			$tmp = $this->_service_single('namecard_job', $cp_pluginid, 'fetch_all_by_conditions', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);
			$m_uids = array();
			foreach ($tmp AS $_id => $_data) {
				if (!isset($m_uids[$_data['m_uid']])) {
					$m_uids[$_data['m_uid']] = $_data['m_uid'];
				}
			}
			$this->_member_list = $this->_get_member_by_uids($m_uids);
			foreach ($tmp AS $_id => $_data) {
				$list[$_id] = $this->_format_job($_data);
			}
			unset($tmp);
		}
		return array($total, $multi, array_merge($searchDefault, $searchBy), $list);
	}

	/**
	 * 格式化职务信息
	 * @param array $job
	 * @return array
	 */
	protected function _format_job($job) {
		if (isset($this->_member_list[$job['m_uid']])) {
			$job['_username'] = $this->_member_list[$job['m_uid']]['m_username'];
		} else {
			$job['_username'] = '';
		}
		return $job;
	}

}
