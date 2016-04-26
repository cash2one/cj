<?php
/**
 * voa_c_api_goods_abstract
 * 商品基础控制器
 * $Author$
 * $Id$
 */

abstract class voa_c_api_goods_abstract extends voa_c_api_base {
	/** 插件id */
	protected $_pluginid = 0;
	// 插件名称
	protected $_pluginname = 'goods';
	// 表格名称
	protected $_tname = 'travel';
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
		/**$pname = (string)$this->_get('pname');
		$tname = (string)$this->_get('tname');

		// 判断插件/表格名称
		if ((empty($this->_pluginname) && empty($pname)) || (empty($this->_tname) && empty($tname))) {
			$this->_set_errcode(voa_errcode_oa_goods::GOODS_PTNAME_IS_EMPTY);
			$this->_output();
			return false;
		}

		$this->_pluginname = empty($this->_pluginname) ? $pname : $this->_pluginname;
		$this->_tname = empty($this->_tname) ? $tname : $this->_tname;*/

		/** 读取插件配置 */
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.'.$this->_pluginname.'.setting', 'oa');
		$this->_ptname = array(
			'plugin' => $this->_pluginname,
			'table' => $this->_p_sets['goods_table_name']
		);

		return true;
	}

}
