<?php
/**
 * 显示邀请函
 * $Author$
 * $Id$
 */
class voa_c_frontend_campaign_invite extends voa_c_frontend_campaign_base {

	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		$regid = intval($_GET['regid']);
		if (! $regid) {
			$this->_error_message('报名id参数错误');
		}

		$this->view->set('regid', $regid);

		$this->_output('campaign/invite');
	}
}
