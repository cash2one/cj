<?php
/**
 * voa_c_admincp_manage_job_list
 * 职务列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_job_list extends voa_c_admincp_manage_job_base {

	public function execute(){

		/** 所有职务列表 */
		$this->view->set('jobList', $this->_job_list());

		/** 编辑提交链接 */
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, 'modify', $this->_module_plugin_id));

		$this->output('manage/job/list');
	}

}
