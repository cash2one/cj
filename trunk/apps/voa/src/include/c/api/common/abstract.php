<?php
/**
 * voa_c_api_common_abstract
 * 公共信息基类
 * $Author$
 * $Id$
 */

abstract class voa_c_api_common_abstract extends voa_c_api_base {

	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;
	}

}
