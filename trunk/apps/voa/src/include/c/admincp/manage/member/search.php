<?php
/**
 * voa_c_admincp_manage_member_search
 * 员工搜索
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_member_search extends voa_c_admincp_manage_member_base{

    //deprecated
	public function execute(){
return;
		/** 是否提交搜索 */
		$issearch = $this->request->get('issearch');

		/** 初始化查询条件 */
		$defaults = array(
			'm_mobilephone' => '',
			'm_username' => '',
			'cab_active' => '-1',
			'cj_id' => '-1',
			'cd_id' => '-1',
		);
		/** 自上次查询构造初始化查询条件 */
		if ($issearch) {
			$searchBy = array_merge($defaults, $this->request->getx(array_keys($defaults)));
		} else {
			$searchBy = $defaults;
		}

		$page = (int)$this->request->get('page');
		$total = 0;
		$multi = '';
		$memberList = array();
		$perpage = 9;
		$emptyResultTipMessage = '无指定条件的员工数据';

		/** 提交了搜索 */
		if ($issearch) {
			$uda_so = &uda::factory('voa_uda_frontend_member_get');
			//$cpurl = $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, $searchBy, true);
			$result = array();
			$uda_so->member_search($searchBy, array(), $page, $perpage, $result);
			list($page, $limit, $total, $pages, $multi, $searchBy, $memberList) = $result;
		}

		$this->view->set('issearch', $issearch);
		$this->view->set('jobList', $this->_job_list());
		$this->view->set('departmentList', $this->_department_list());
		$this->view->set('activeList', $this->active_list);
		$this->view->set('searchBy', $searchBy);
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, $searchBy, true));
		$this->view->set('memberListUrl', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('member_list', $memberList);
		$this->view->set('formDeleteActionUrl', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('emptyResultTipMessage', $emptyResultTipMessage);
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('m_uid'=>'')));
		$this->view->set('edit_url_base', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('m_uid'=>'')));

		$this->output('manage/member/search');

	}

}
