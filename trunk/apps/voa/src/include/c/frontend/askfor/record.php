<?php
/**
 * 审批记录列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_askfor_record extends voa_c_frontend_askfor_base {

	public function execute() {

		$this->redirect(config::get('voa.askfor.record'));

		return true;

		$this->_output('mobile/askfor/record');
	}

}

