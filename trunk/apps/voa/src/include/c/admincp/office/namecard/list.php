<?php
/**
 * voa_c_admincp_office_namecard_list
 * 企业后台/微办公管理/微名片/名片列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_namecard_list extends voa_c_admincp_office_namecard_base {

	protected $_member_list = array();
	protected $_job_list = array();
	protected $_company_list = array();
	protected $_folder_list = array();

	public function execute() {

		$searchDefault = array(
				'm_username' => '',
				'nc_realname' => '',
				'nc_mobilephone' => '',
		);
		$perpage = 10;

		$issearch = $this->request->get('issearch') ? 1 : 0;
		list($total, $multi, $searchBy, $namecardList) = $this->_search_namecard($this->_module_plugin_id, $issearch, $searchDefault, $perpage);
		$urlParam = $issearch ? array() : $searchBy;

		$this->view->set('searchBy', $searchBy);
		$this->view->set('issearch', $issearch);
		$this->view->set('multi', $multi);
		$this->view->set('namecardList', $namecardList);
		$this->view->set('total', $total);
		$this->view->set('timestamp', startup_env::get('timestamp'));
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('nc_id'=>'')));
		$this->view->set('allowDelete', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('nc_id'=>'') ? true : false));
		$this->view->set('editUrlBase', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('nc_id'=>'')));
		$this->view->set('formDeleteUrl', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('searchActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		$this->output('office/namecard/list');
	}

	/**
	 * 搜索名片
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 * @return array(total, multi, searchBy, list)
	 */
	protected function _search_namecard($cp_pluginid, $issearch, $searchDefault = array(), $perpage = 10) {
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
		$total = $this->_service_single('namecard', $cp_pluginid, 'count_by_conditions', $conditions);
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
			$tmp = $this->_service_single('namecard', $cp_pluginid, 'fetch_all_by_conditions', $conditions, $pagerOptions['start'], $pagerOptions['per_page']);
			$m_uids = array();
			$ncj_ids = array();
			$ncc_ids = array();
			$nccf_ids = array();
			foreach ($tmp AS $_id => $_data) {
				if (!isset($m_uids[$_data['m_uid']])) {
					$m_uids[$_data['m_uid']] = $_data['m_uid'];
				}
				if (!isset($ncj_ids[$_data['ncj_id']])) {
					$ncj_ids[$_data['ncj_id']] = $_data['ncj_id'];
				}
				if (!isset($ncc_ids[$_data['ncc_id']])) {
					$ncc_ids[$_data['ncc_id']] = $_data['ncc_id'];
				}
				if (!isset($ncf_ids[$_data['ncf_id']])) {
					$ncf_ids[$_data['ncf_id']] = $_data['ncf_id'];
				}
			}
			$this->_member_list = $this->_get_member_by_uids($m_uids);
			$this->_job_list = $this->_get_job_by_ncj_ids($cp_pluginid, $ncj_ids);
			$this->_company_list = $this->_get_company_by_ncc_ids($cp_pluginid, $ncc_ids);
			$this->_folder_list = $this->_get_folder_by_ncf_ids($cp_pluginid, $ncf_ids);
			foreach ($tmp AS $_id => $_data) {
				$list[$_id] = $this->_format_namecard($_data);
			}
			unset($tmp);
		}
		return array($total, $multi, array_merge($searchDefault, $searchBy), $list);
	}
}
