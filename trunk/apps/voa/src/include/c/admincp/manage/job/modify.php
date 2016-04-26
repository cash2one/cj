<?php
/**
 * voa_c_admincp_manage_job_modify
 * 职务的编辑和修改
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_job_modify extends voa_c_admincp_manage_job_base{
	public function execute(){
		if ( $this->_is_post() ) {
			$this->_job_modify($this->request->post('update'), $this->request->post('delete'));
		} else {
			$this->message('error', '您无权进行此操作');
		}
	}
}
