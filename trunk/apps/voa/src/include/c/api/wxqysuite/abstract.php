<?php
/**
 * voa_c_api_wxqysuite_abstract
 * 基础控制器
 * $Author$
 * $Id$
 */

abstract class voa_c_api_wxqysuite_abstract extends voa_c_api_base {

	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		$this->_require_login = false;
		parent::_before_action($action);

		// 检查请求是否合法
		$sig = (string)$_GET['sig'];
		$ts = (string)$_GET['ts'];
		if ($sig != voa_h_func::sig_create($this->_params, $ts)) {
			$this->_set_errcode(voa_errcode_api_wxqysuite::SIG_ERROR);
			$this->_output();
			return true;
		}

		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);

		return true;
	}

}
