<?php
/**
 * voa_uda_frontend_goods_tablecolopt
 * 统一数据访问/商品应用/表格列选项操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_goods_tablecolopt extends voa_uda_frontend_goods_abstract {

	public function __construct($ptname) {

		parent::__construct($ptname);
		$this->_tablecolopts = $ptname['tablecolopts'];
	}

	/**
	 * 获取表格列选项
	 * @param array &$list 表格列选项
	 * @return boolean
	 */
	public function list_all($gp, &$list) {

		// 查询表格的条件
		$fields = array(
			array('tco_id', self::VAR_INT, null, null, true),
			array('tc_id', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields, $gp)) {
			return false;
		}

		$conds['tid'] = $this->_table['tid'];
		// 读取数据
		$t = new voa_d_oa_goods_tablecolopt();
		$list = $t->list_by_conds($conds);

		return true;
	}

	/**
	 * 根据 tco_id 获取表格信息
	 * @param int $tco_id 表格id
	 * @param array $class 表格信息
	 * @return boolean
	 */
	public function get_one($tco_id, &$tablecolopt) {

		$t = new voa_d_oa_goods_tablecolopt();
		$tablecolopt = $t->get($tco_id);

		return true;
	}

	/**
	 * 更新当个表格信息
	 * @param array $member 用户信息
	 * @param array $gp 数据
	 * @param int $tco_id 表格id
	 * @throws service_exception
	 * @return boolean
	 */
	public function update($member, $gp, $tco_id) {

		// 提取数据
		$data = array();
		if (!$this->__parse_gp($gp, $data)) {
			return false;
		}

		$tco_id = (int)$tco_id;
		$t = new voa_d_oa_goods_tablecolopt();

		try {
			$t->update($tco_id, $data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 新增表格列属性选项
	 * @param array $member 用户信息
	 * @param array $gp 数据
	 * @throws service_exception
	 * @return boolean
	 */
	public function add($member, $gp, &$tablecolopt) {

		// 提取数据
		$tablecolopt = array('tid' => $this->_table['tid']);
		if (!$this->__parse_gp($gp, $tablecolopt)) {
			return false;
		}

		// 数据处理类
		$t = new voa_d_oa_goods_tablecolopt();

		try {
			$tablecolopt = $t->insert($tablecolopt);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 删除表格列选项信息
	 * @param int $tco_id 产品选项ID
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete($tco_id) {

		// 查询表格的条件
		$conds = array(
			'tco_id' => $tco_id,
			'tid' => $this->_table['tid']
		);
		$t = new voa_d_oa_goods_tablecolopt();

		try {
			$t->delete_by_conds($conds);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 从 G/P 中提取数据
	 * @param array $gp 请求数据
	 * @param array $table 数据结果
	 * @return boolean
	 */
	private function __parse_gp($gp, &$tablecolopt) {

		$fields = array(
			array('tc_id', self::VAR_INT, null, null),
			array('attachid', self::VAR_INT, null, null),
			array('value', self::VAR_STR, null, null)
		);
		// 提取数据
		if (!$this->extract_field($tablecolopt, $fields, $gp)) {
			return false;
		}

		// 附件和文字不能同时为空
		if (empty($tablecolopt['attachid']) && empty($tablecolopt['value'])) {
			$this->set_errmsg(voa_errcode_oa_goods::GOODS_TABLECOLOPT_IS_EMPTY);
			return false;
		}

		return true;
	}

	/**
	 * 检查表格列选项
	 * @param string $value 表格列选项
	 * @param string $err 错误信息
	 * @return boolean
	 */
	public function chk_value($value, $err = null) {

		// 如果值为空
		if (empty($value)) {
			$this->set_errmsg(voa_errcode_oa_goods::GOODS_TABLECOLOPT_IS_EMPTY);
			return false;
		}

		return true;
	}
}

