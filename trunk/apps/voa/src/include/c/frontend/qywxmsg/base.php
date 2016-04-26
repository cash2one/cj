<?php
/**
 * 企业微信消息基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_qywxmsg_base extends voa_c_frontend_base {

	protected function _before_action($action) {

		$this->_require_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}
}
