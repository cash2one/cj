<?php
/**
 * voa_uda_frontend_diy_table_abstract
 * 统一数据访问/自定义数据表格/基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_uda_frontend_diy_table_abstract extends voa_uda_frontend_diy_abstract {
	// service table
	protected $_serv_table = null;

	public function __construct() {

		parent::__construct();
		$this->_serv_table = new voa_s_oa_diy_table();
	}

	/**
	 * 从 G/P 中提取数据
	 * @param array $table 数据结果
	 * @param boolean $ignore_null 忽略 null 值
	 * @return boolean
	 */
	protected function _parse_gp(&$table, $ignore_null = false) {

		$fields = array(
			array('cp_identifier', self::VAR_STR, array($this->_serv_table, 'chk_cp_identifier'), null, $ignore_null),
			array('tunique', self::VAR_STR, array($this->_serv_table, 'chk_tunique'), null, $ignore_null),
			array('tname', self::VAR_STR, array($this->_serv_table, 'chk_tname'), null, $ignore_null),
			array('t_desc', self::VAR_STR, null, null, $ignore_null)
		);
		// 提取数据
		if (!$this->extract_field($table, $fields)) {
			return false;
		}

		return true;
	}

}
