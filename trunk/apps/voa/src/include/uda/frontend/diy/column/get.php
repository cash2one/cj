<?php
/**
 * voa_uda_frontend_diy_column_get
 * 统一数据访问/自定义数据表格属性/获取属性
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_column_get extends voa_uda_frontend_diy_column_abstract {

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

		// 输入参数
		$this->_params = $in;
		// 属性id
		$conds = array(
			'tc_id' => (int)$this->get('tc_id'),
			'tid' => self::$_s_table['tid']
		);
		// 获取属性
		$column = $this->_serv_tablecol->get_by_conds($conds);

		// 如果有 fieldalias 则替换 field
		if (!empty($column['fieldalias'])) {
			unset($column['fieldalias']);
		}

		return true;
	}

}
