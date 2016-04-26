<?php
/**
 * voa_uda_frontend_diy_abstract
 * 统一数据访问/商品应用/基类
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_abstract extends voa_uda_frontend_base {
	// 字段配置
	protected static $_s_columntypes = array();
	// 当前操作的表格
	protected static $_s_table = array();
	// 表格列属性列表
	protected static $_s_tablecols = array();
	// 表格列选项
	protected static $_s_tablecolopts = array();
	// 用户信息数组
	protected static $_s_mem = array();

	public function __construct() {

		parent::__construct();
		// 取字段类型配置
		self::$_s_columntypes = voa_h_cache::get_instance()->get('columntype', 'oa');
	}

	/**
	 * 设置属性选项
	 * @param array $opts 选项
	 * @return boolean
	 */
	public function set_tablecolopts($opts) {

		self::$_s_tablecolopts = $opts;
		return true;
	}

	/**
	 * 设置属性
	 * @param array $cols 属性
	 * @return boolean
	 */
	public function set_tablecols($cols) {

		self::$_s_tablecols = $cols;
		return true;
	}

	/**
	 * 设置当前操作的表格
	 * @param int $tname 表格名称
	 */
	public function set_table($tname) {

		// 如果表格名称为空
		if (empty($tname)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::TNAME_IS_EMPTY);
			return false;
		}

		// 获取表格信息
		$tables = voa_h_cache::get_instance()->get('diytable', 'oa');
		if (!array_key_exists($tname, $tables)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::TABLE_IS_NOT_EXIST);
			return false;
		}

		// 取表格信息
		self::$_s_table = $tables[$tname];

		return true;
	}

	/**
	 * 设置用户信息
	 * @param array $mem 用户信息
	 * @return boolean
	 */
	public function set_mem($mem = array()) {

		self::$_s_mem = $mem;
		return true;
	}

}

