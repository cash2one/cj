<?php
/**
 * voa_c_admincp_office_footprint_list
 * 企业后台/微办公/销售轨迹/列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_footprint_list extends voa_c_admincp_office_footprint_base {

	public function execute() {

		$perpage = 15;

		list($total, $multi, $search_by, $list) = $this->_footprint_search($perpage);

		$this->view->set('search_by', $search_by);
		$this->view->set('list_url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		$this->view->set('form_search_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('thread_delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('fp_id' => '')));
		$this->view->set('view_url_base', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('fp_id' => '')));
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('fp_id' => '')));

		$this->view->set('footprint_type_list', $this->_sets['types']);
		$this->view->set('issearch', $this->request->get('issearch'));

		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);

		$this->output('office/footprint/footprint_list');
	}

}
