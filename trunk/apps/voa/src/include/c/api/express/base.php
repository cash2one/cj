<?php
/**
 * voa_c_api_express_base
 * 快递助手基础控制器
 * $Author$
 * $Id$
 */
class voa_c_api_express_base extends voa_c_api_base {

	/** 插件id */
	protected $_pluginid = 0;


	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}


}
