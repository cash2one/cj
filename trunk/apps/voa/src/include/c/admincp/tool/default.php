<?php
/**
 * voa_c_admincp_tool_default
 * 企业后台/应用宝/默认页
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_tool_default extends voa_c_admincp_tool_base {

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	public function execute() {
		return false;
	}

}
