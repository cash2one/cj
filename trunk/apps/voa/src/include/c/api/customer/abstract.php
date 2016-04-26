<?php
/**
 * voa_c_api_customer_abstract
 * 客户基础控制器
 * $Author$
 * $Id$
 */

abstract class voa_c_api_customer_abstract extends voa_c_api_base {
	/** 插件id */
	protected $_pluginid = 0;
	// 插件名称
	protected $_pluginname = 'customer';
	// 表格名称
	protected $_tname = 'customer';
	// uda's ptname
	protected $_ptname = array();

	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 设置当前应用的插件/表格名称
		$this->_set_ptname();

		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _set_ptname() {

		// 从参数中获取插件名称/表格名称
		/**$this->_pluginname = (string)$this->_get('pname');
		$this->_tname = (string)$this->_get('tname');

		// 判断插件/表格名称
		if (empty($this->_pluginname) || empty($this->_tname)) {
			$this->_set_errcode(voa_errcode_oa_customer::CUSTOMER_PTNAME_IS_EMPTY);
			$this->_output();
			return false;
		}*/

		/** 读取插件配置 */
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.'.$this->_pluginname.'.setting', 'oa');
		$this->_ptname = array(
			'plugin' => $this->_pluginname,
			'table' => $this->_p_sets['customer_table_name']
		);

		return true;
	}

}
