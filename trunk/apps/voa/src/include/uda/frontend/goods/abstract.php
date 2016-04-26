<?php
/**
 * voa_uda_frontend_goods_abstract
 * 统一数据访问/商品应用/基类
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_goods_abstract extends voa_uda_frontend_base {
	// 商品配置
	protected $_sets = array();
	// 字段配置
	protected $_columntypes = array();
	// 当前操作的表格
	protected $_table = array();
	// 插件名称
	protected $_ptname = array();
	// 分类id
	protected $_classes = array();
	// 表格列表
	protected $_tables = array();
	// 表格列属性列表
	protected $_tablecols = array();
	// 表格列选项
	protected $_tablecolopts = array();
	// 用户信息数组
	protected $_mem = array();

	public function __construct($ptname = array()) {

		parent::__construct();
		// 取应用配置
		if (!empty($ptname['plugin'])) {
			$this->_sets = voa_h_cache::get_instance()->get('plugin.'.$ptname['plugin'].'.setting', 'oa');
		}

		// 取字段类型配置
		$this->_columntypes = voa_h_cache::get_instance()->get('columntype', 'oa');
		// 获取表格信息
		$this->_ptname = $ptname;
		if (!empty($ptname['table'])) {
			$this->_init_plugin_table($ptname['table']);
		}
	}

	/**
	 * 设置当前操作的表格
	 * @param int $tname 表格名称
	 */
	public function set_table($tname) {

		// 如果表格名称为空
		if (empty($tname)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_goods::TNAME_IS_EMPTY);
			return false;
		}

		// 获取表格信息
		$tables = voa_h_cache::get_instance()->get('goodstable', 'oa');
		if (!array_key_exists($tname, $tables)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_goods::GOODS_TABLE_IS_NOT_EXIST);
			return false;
		}

		// 取表格信息
		$this->_table = $tables[$tname];

		return true;
	}

	/**
	 * 设置用户信息
	 * @param array $mem 用户信息
	 * @return boolean
	 */
	public function set_mem($mem = array()) {

		$this->_mem = $mem;
		return true;
	}

	// 获取表格信息
	protected function _init_tables() {

		$this->_tables = voa_h_cache::get_instance()->get('goodstable', 'oa');
		return true;
	}

	/**
	 * 根据表名设置表格信息
	 * @param string $tname 表格名称
	 * @return boolean
	 */
	protected function _init_plugin_table($tname) {

		// 如果表格名称为空
		if (empty($tname)) {
			throw new Exception('表格名称错误', 500);
			return false;
		}

		// 获取表格信息
		$this->_init_tables();
		if (!array_key_exists($tname, $this->_tables)) {
			$this->set_errmsg(voa_errcode_oa_goods::GOODS_TABLE_IS_NOT_EXIST);
			return false;
		}

		$this->_table = $this->_tables[$tname];
		return true;
	}

	/**
	 * 检查表格是否存在
	 * @param int $tid 表格id
	 * @return boolean
	 */
	public function chk_tid($tid, $err = null) {

		// 取表格缓存
		$tables = voa_h_cache::get_instance()->get('goodstable', 'oa');
		// 如果表格不存在
		if (!array_key_exists($tid, $tables['tid2tunique'])) {
			$this->set_errmsg(voa_errcode_oa_goods::GOODS_TABLE_IS_NOT_EXIST);
			return false;
		}

		return true;
	}

	/**
	 * 检查字段类型是否存在
	 * @param string $type 类型标识
	 * @param string $err
	 * @return boolean
	 */
	public function chk_ct_type($type, $err = null) {

		// 如果字段类型信息不存在
		if (!array_key_exists($type, $this->_columntypes)) {
			$this->set_errmsg(voa_errcode_oa_goods::GOODS_CT_TYPE_IS_NOT_EXIST);
			return false;
		}

		return true;
	}

}

