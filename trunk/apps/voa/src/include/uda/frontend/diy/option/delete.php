<?php
/**
 * voa_uda_frontend_diy_option_delete
 * 删除产品选项
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_option_delete extends voa_uda_frontend_diy_option_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array $out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out = null) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('tco_id', self::VAR_ARR, null, null, true)
		);
		$conds = array('tid' => self::$_s_table['tid']);
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		$this->_serv_tablecolopt->delete_by_conds($conds);

		return true;
	}

}
