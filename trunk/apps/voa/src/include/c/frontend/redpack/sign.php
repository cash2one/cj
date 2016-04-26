<?php
/**
 * sign.php
 * 扫码签到操作
 * $Author$
 * $Id$
 */
class voa_c_frontend_redpack_sign extends voa_c_frontend_redpack_base {

	public function execute() {

		// 如果用户不存在
		if (empty($this->_user)) {
			$this->_output('mobile/redpack/sign_fail');
		} else {
			$this->view->set('redpack_id', $this->_p_sets['sign_redpack_id']);
			$this->_output('mobile/redpack/sign_success');
		}

		return true;
	}

	/**
	 * 重写自动登录企业号方法
	 * @see voa_c_frontend_base::_auto_login_qy()
	 */
	protected function _auto_login_qy() {

		$result = parent::_auto_login_qy();
		$this->_require_login = false;
		return $result;
	}

}
