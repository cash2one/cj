<?php
/**
 * voa_c_admincp_office_train_atedit
 * 企业后台/微办公管理/培训/编辑文章
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_train_atedit extends voa_c_admincp_office_train_base {

	public function execute() {

		$ta_id = $this->request->get('ta_id');
		$this->_article_edit($ta_id, false);
	}

}

