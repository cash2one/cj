<?php
/**
 * voa_uda_frontend_common_columntype
 * 统一数据访问/商品应用/附件操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_common_columntype extends voa_uda_frontend_common_abstract {

	public function __construct($ptname) {

		parent::__construct($ptname);
	}

	/**
	 * 获取分类列表
	 * @param array &$list 分类列表
	 * @return boolean
	 */
	public function list_all($gp, &$list) {

		// 查询表格的条件
		$fields = array(
			array('ctid', self::VAR_INT, null, null, true),
			array('ct_type', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields, $gp)) {
			return false;
		}

		// 获取分类
		$t = new voa_d_oa_common_columntype();
		$list = $t->list_by_conds($conds);

		return true;
	}

}
