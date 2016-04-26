<?php
/**
 * voa_c_admincp_office_notice_add
 * 企业后台/微办公/通知公告/添加
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_notice_add extends voa_c_admincp_office_notice_base {

	public function execute() {

		$this->_notice_edit(0, true);
	}

}
