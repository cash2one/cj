<?php
/**
 * voa_c_admincp_office_showroom_cgadd
 * 企业后台/微办公管理/陈列/编辑目录
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_showroom_cgedit extends voa_c_admincp_office_showroom_base {

	public function execute() {
		$tc_id = $this->request->get('tc_id');
		$this->_category_edit($tc_id, false);
	}

}
