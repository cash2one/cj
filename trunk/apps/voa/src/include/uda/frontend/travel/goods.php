<?php
/**
 * voa_uda_frontend_travel_goods
 * 统一数据访问/旅游产品应用/产品操作信息
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_goods extends voa_uda_frontend_travel_abstract {

	public function __construct($pset = null) {

		parent::__construct($pset);
		$this->_tablecols = $pset['columns'];
	}

	/**
	 * 获取销售编辑数据
	 * @param array $member 用户信息
	 * @param array $gp 提交的 G/P 数据
	 * @param int $dataid 数据id
	 * @return boolean
	 */
	public function seller_edit($member, $gp, $dataid, $goods) {

		// 获取更改数据
		$fields = array(
			array('subject', self::VAR_STR, null, null),
			array('price', self::VAR_STR, null, null),
			array('feature', self::VAR_STR, null, null),
			array('recommend', self::VAR_INT, null, null)
		);
		$data = array();
		if (!$this->extract_field($data, $fields, $gp)) {
			return false;
		}

		// 取出待更新列
		$columns = array();
		foreach ($this->_tablecols as $_col) {
			if (isset($data[$_col['fieldalias']]) || isset($data[$_col['field']])) {
				$columns[] = $_col;
			}
		}

		// 更新数据
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		$updata = array();
		if (!$uda->update_by_column($data, $dataid, $updata, $columns, $goods)) {
			$this->set_errmsg($uda->errno.':'.$uda->error);
			return false;
		}

		return true;
	}

}
