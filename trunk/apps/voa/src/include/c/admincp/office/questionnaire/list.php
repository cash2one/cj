<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 16/3/21
 * Time: 15:09
 */
class voa_c_admincp_office_questionnaire_list extends voa_c_admincp_office_questionnaire_base {

	protected function execute() {

		$this->view->set('list_url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		$this->view->set('edit_url', $this->cpurl($this->_module, $this->_operation, 'addedit', $this->_module_plugin_id, array('qu_id' => '')));
		$this->view->set('situation_url', $this->cpurl($this->_module, $this->_operation, 'situation', $this->_module_plugin_id, array('qu_id' => '')));
		$this->output('office/questionnaire/list');
	}


}