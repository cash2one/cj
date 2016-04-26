<?php
/**
 * join_success.php
 *
 * Created by zhoutao.
 * Created Time: 2015/7/20  10:42
 */

class voa_c_frontend_invite_success extends voa_c_frontend_invite_base {

	//不强制登录，允许外部访问
	protected function _before_action($action) {
		$this->_auto_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	public function execute() {

		$this->_output('mobile/invite/success');
		return true;
	}


}
