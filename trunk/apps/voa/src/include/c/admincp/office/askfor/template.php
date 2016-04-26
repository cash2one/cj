<?php
/**
 * voa_c_admincp_office_askfor_template
 * 企业后台 - 审批流 - 列表
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askfor_template extends voa_c_admincp_office_askfor_base {

	public function execute() {

		$this->view->set('addBaseUrl', $this->cpurl($this->_module, $this->_operation, 'addtemplate', $this->_module_plugin_id));
		$this->view->set('editBaseUrl', $this->cpurl($this->_module, $this->_operation, 'edittemplate', $this->_module_plugin_id));

		$this->output('office/askfor/new_template');

	}

}
