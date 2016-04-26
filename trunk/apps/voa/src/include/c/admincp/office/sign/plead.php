<?php

/**
 * voa_c_admincp_office_sign_plead
 * 企业后台/微办公管理/考勤签到/申诉记录列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sign_plead extends voa_c_admincp_office_sign_base {

	public function execute() {

		$searchDefault = array(
			'm_username' => '',
			'sp_year' => '-1',
			'sp_month' => '-1',
			'sp_message' => '',
			'sp_status' => '',
		);
		$issearch = $this->request->get('issearch');
		$perpage = 15;
		list($total, $multi, $searchBy, $list) = $this->_search_sign_plead($this->_module_plugin_id, $issearch, $searchDefault, $perpage);

		$yearSelect = array();
		list($currentYear, $currentMonth) = explode('-', rgmdate(startup_env::get('timestamp'), 'Y-n'));
		$ymin = $currentYear - 10;
		for ($i = $currentYear; $i >= $ymin; $i --) {
			$yearSelect[$i] = $i;
		}
		$monthSelect = array();
		for ($i = 1; $i <= 12; $i ++) {
			$monthSelect[$i] = $i;
		}
		if (!$issearch) {
			if ($searchBy['sp_year'] < 0) {
				$searchBy['sp_year'] = $currentYear;
			}
			if ($searchBy['sp_month']) {
				$searchBy['sp_month'] = $currentMonth;
			}
		}
		$this->view->set('total', $total);
		$this->view->set('issearch', $issearch);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('searchBy', $searchBy);
		$this->view->set('signPleadStatus', $this->_sign_plead_status);
		$this->view->set('signPleadStatusSet', $this->_sign_plead_status_set);
		$this->view->set('yearSelect', $yearSelect);
		$this->view->set('monthSelect', $monthSelect);
		$this->view->set('pleadopUrlBase', $this->cpurl($this->_module, $this->_operation, 'pleadop', $this->_module_plugin_id, array('sp_id' => '')));
		$this->view->set('searchActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		$this->output('office/sign/plead');
	}

	/**
	 * 列出指定条件的申诉记录
	 * @param unknown $issearch
	 * @param unknown $searchDefault
	 * @param number  $perpage
	 * @return array(total, multi, list)
	 */
	protected function _search_sign_plead($cp_pluginid, $issearch, $searchDefault = array(), $perpage = 10) {

		/** 搜索条件 */
		$conditions = array();
		/** 搜索字段 */
		$searchBy = array();
		/** 如果为搜索 */
		if ($issearch) {
			foreach ($searchDefault AS $_k => $_v) {
				if (isset($_GET[$_k])) {
					$v = $this->request->get($_k);
					if ($_v != $v || $_k == 'sp_month' || $_k == 'sp_year') {
						if ($_k == 'm_username') {
							if ($v && ($_m = $this->_get_member($v, false))) {
								$conditions['m_uid'] = $_m['m_uid'];
								$searchBy[$_k] = $_m['m_username'];
								unset($_m);
							}
						} elseif ($_k == 'sp_status') {
							if (isset($this->_sign_plead_status[$v]) && $v != $this->_sign_plead_status_set['remove']) {
								$conditions[$_k] = $v;
								$searchBy[$_k] = $v;
							}
						} else {
							$timestamp = startup_env::get('timestamp');
							$max = rgmdate($timestamp, 'Y');
							$min = rgmdate($timestamp - 86400 * 366 * 5, 'Y');
							if ($_k == 'sp_year' && (!validator::is_int($v) || $v > $max || $v < $min)) {
								continue;
							}
							if ($_k == 'sp_month' && ($v > 12 || $v < 1 || !validator::is_int($v))) {
								continue;
							}
							$conditions[$_k] = $v;
							$searchBy[$_k] = $v;
						}
					}
				}
			}
		}

		$list = array();
		$total = $this->_service_single('sign_plead', $cp_pluginid, 'count_all_by_conditions', $conditions);
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
			$tmp = $this->_service_single('sign_plead', $cp_pluginid, 'fetch_all_by_conditions', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);
			foreach ($tmp AS $_id => $_data) {
				$list[$_id] = $this->_format_sign_plead($_data);
			}
			unset($tmp);
		}

		return array($total, $multi, array_merge($searchDefault, $searchBy), $list);
	}

}
