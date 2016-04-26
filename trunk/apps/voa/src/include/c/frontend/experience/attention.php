<?php
/**
 * 登录关注页
 * $Author$
 * $Id$
 */

class voa_c_frontend_experience_attention extends voa_c_frontend_experience_base {

	public function execute() {

		$this->view->set('navtitle', '扫码关注');
		$this->_output('experience/attention');
	}

	
}
