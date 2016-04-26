<?php
/**
 * voa_c_frontend_member_logout
 * 首页
 *
 * $Author$
 * $Id$
 */
class voa_c_frontend_member_logout extends voa_c_frontend_base {

	/**
	 * _before_action
	 *
	 * @param mixed $action
	 * @access protected
	 * @return void
	 */
	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {
		$this->session->destory();

		$this->redirect('/home'); return;
	}
}
