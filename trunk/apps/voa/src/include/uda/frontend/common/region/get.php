<?php
/**
 * voa_uda_frontend_common_region_get
 * 统一数据访问/地区/获取地区信息
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_common_region_get extends voa_uda_frontend_common_region_abstract {

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
		// 入库的数据
		$fields = array(
			array('cr_id', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 如果条件为空
		if (empty($conds)) {
			return false;
		}

		// 读取表格字段
		$out = $this->_serv->get_by_conds($conds);
		if (empty($out)) {
			$out = array();
		}

		!empty($out) && $this->_format($out);

		return true;
	}

}
