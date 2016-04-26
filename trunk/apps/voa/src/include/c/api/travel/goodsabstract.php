<?php
/**
 * voa_c_api_travel_goodsabstract
 * 商品基础控制器
 * $Author$
 * $Id$
 */

abstract class voa_c_api_travel_goodsabstract extends voa_c_api_travel_abstract {
	// sig
	protected $_sig = '';

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

	protected function _init_ptname() {

		$this->_ptname = array(
			'plugin' => $this->_pluginname,
			'table' => $this->_p_sets['goods_table_name']
		);
	}

	protected function _chk_privilege() {

		if (empty($_GET['sig'])) {
			return false;
		}

		// sig 标识
		$this->_sig = (string)$_GET['sig'];
		// 产品id
		$dataid = (int)$_GET['dataid'];
		// 时间戳
		$ts = (int)$_GET['timestamp'];

		// 判断 sig 是否正确
		if ($this->_sig == voa_h_func::sig_create(array($dataid), $ts)) {
			$this->_require_login = false;
			//startup_env::set('is_share', '1');
		}

		return true;
	}

}
