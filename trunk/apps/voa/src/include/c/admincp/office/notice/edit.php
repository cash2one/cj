<?php
/**
 * voa_c_admincp_office_notice_edit
 * 企业后台/微办公/通知公告/编辑公告
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_notice_edit extends voa_c_admincp_office_notice_base {

	public function execute() {

		$this->_notice_edit($this->request->get('nt_id'), false);

	}

}
