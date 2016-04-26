<?php
/**
 * 我的审批申请列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_askfor_list extends voa_c_frontend_askfor_base {

	public function execute() {

		$this->redirect(config::get('voa.askfor.list'));

		return true;

		$this->_output('mobile/askfor/list');
	}
}

