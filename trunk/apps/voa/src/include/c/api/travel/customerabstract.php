<?php
/**
 * voa_c_api_travel_customerabstract
 * 商品基础控制器
 * $Author$
 * $Id$
 */

abstract class voa_c_api_travel_customerabstract extends voa_c_api_travel_abstract {

	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_init_ptname();
		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;
	}

	// 初始化 ptname
	protected function _init_ptname() {

		$this->_ptname = array(
			'plugin' => $this->_pluginname,
			'table' => $this->_p_sets['customer_table_name']
		);
	}

}
