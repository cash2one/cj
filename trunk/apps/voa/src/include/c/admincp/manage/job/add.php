<?php
/**
 * voa_c_admincp_manage_job_add
 * 添加职务
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_job_add extends voa_c_admincp_manage_job_base {

	public function execute() {

		/** 初始化当前操作的职务，新增=0 */
		$cj_id	=	0;

		/** 提交添加 */
		if ( $this->_is_post() ) {
			$this->_response_submit_edit($cj_id);
		}

		/** 赋值模板变量：当前操作的职务id */
		$this->view->set('cj_id', $cj_id);
		/** 赋值模板变量：当前操作的职务信息 */
		$this->view->set('job', $this->_job_detail($cj_id));
		/** 赋值模板变量：提交添加时的url */
		$this->view->set('actionSubmitUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		$this->output('manage/job/edit_form');

	}

}
