<?php
/**
 * 登录体验号基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_experience_base extends voa_c_frontend_base {

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->view->set('navtitle', '体验登录');

		return true;
	}

	
}
