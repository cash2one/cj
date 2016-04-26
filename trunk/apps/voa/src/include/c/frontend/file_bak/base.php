<?php
/**
 * 文件
 * $Author
 * $Id$
 */

class voa_c_frontend_file_base extends voa_c_frontend_base {

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->view->set('navtitle', '文件');
		return true;
	}
}
