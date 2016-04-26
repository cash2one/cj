<?php
/**
 * 套件操作相关
 * $Author$
 * $Id$
 */

abstract class voa_c_api_suite_abstract extends voa_c_api_base {

	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

}

