<?php
/**
 * voa_uda_frontend_diy_column_add
 * 统一数据访问/自定义数据表格属性/新增属性
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_column_add extends voa_uda_frontend_diy_column_abstract {

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
		// 提取数据
		$column = array(
			'uid' => self::$_s_mem['m_uid'],
			'tid' => self::$_s_table['tid']
		);
		if (!$this->_parse_gp($column)) {
			return false;
		}

		// 开始更新
		$out = $this->_serv_tablecol->insert($column);

		return true;
	}

}
