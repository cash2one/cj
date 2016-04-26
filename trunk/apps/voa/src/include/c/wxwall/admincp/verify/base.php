<?php
/**
 * voa_c_wxwall_admincp_verify_base
 * 微信墙前端/管理/墙内容:基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_wxwall_admincp_verify_base extends voa_c_wxwall_admincp_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

}
