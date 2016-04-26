<?php
/**
 * voa_c_uc_wechat_base
 * 企业用户微信登录基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_wechat_base extends voa_c_uc_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

}
