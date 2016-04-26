<?php

/**
 * voa_c_admincp_office_askfor_addtemplate
 * 企业后台 - 审批流 - 添加审批流程
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askfor_addtemplate extends voa_c_admincp_office_askfor_base {

	public function execute() {

		$this->view->set('act', 'add'); // 添加操作

		$this->view->set('aft_id', ''); // 模板id 避免js报错
		$this->view->set('copy', array()); // 抄送人默认数据 避免js报错
		$this->view->set('dep_arr', array()); // 适用部门id 避免js报错
		$this->view->set('approver_default_data', array()); // 审批人默认数据 避免js报错
		$this->view->set('templist_url', $this->cpurl($this->_module, $this->_operation, 'template', $this->_module_plugin_id));

		$this->view->set('create_id', $this->_user['ca_id']);
		$this->view->set('create_username', $this->_user['ca_username']);
		$this->view->set('form_action_url', $this->cpurl($this->_module, $this->_operation, 'addtemplate', $this->_module_plugin_id));
		$this->output('office/askfor/add_form');

	}

}
