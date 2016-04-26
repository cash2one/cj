<?php
/**
 * voa_uda_frontend_diy_table_list
 * 统一数据访问/自定义数据表格/获取表格列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_table_list extends voa_uda_frontend_diy_table_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('tid', self::VAR_INT, null, null, true),
			array('cp_identifier', self::VAR_STR, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 读取列表
		$list = $this->_serv_table->list_by_conds($conds);

		return true;
	}

}
