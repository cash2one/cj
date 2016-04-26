<?php
/**
 * voa_c_api_diy_abstract
 * diy 基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_c_api_diy_abstract extends voa_c_api_base {
	protected $_tablecol = null;
	protected $_tablecolopt = null;

	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function get_tablecol() {

		if (null === $this->_tablecol) {
			$this->_tablecol = voa_h_cache::get_instance()->get('plugin.diy.tablecol', 'oa');
		}

		return $this->_tablecol;
	}

	protected function get_tablecolopt() {

		if (null === $this->_tablecolopt) {
			$this->_tablecolopt = voa_h_cache::get_instance()->get('plugin.diy.tablecolopt', 'oa');
		}

		return $this->_tablecolopt;
	}

	protected function init_uda_for_data($uda) {

		// 设置用户
		$uda->set_mem($this->_member);
		// 设置表格信息
		$uda->set_table('test');
		// 设置表格列属性
		$uda->set_tablecols($this->get_tablecol());
		// 设置表格选项
		$uda->set_tablecolopts($this->get_tablecolopt());

		return true;
	}

}
