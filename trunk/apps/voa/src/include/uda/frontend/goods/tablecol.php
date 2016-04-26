<?php
/**
 * voa_uda_frontend_goods_tablecol
 * 统一数据访问/商品应用/表格列属性操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_goods_tablecol extends voa_uda_frontend_goods_abstract {

	public function __construct($ptname) {

		parent::__construct($ptname);
		if (isset($ptname['tablecols'])) {
			$this->_tablecols = $ptname['tablecols'];
		}
	}

	/**
	 * 获取表格列属性列表
	 * @param array $gp 请求数据
	 * @param array &$list 表格列属性列表
	 * @return boolean
	 */
	public function list_all($gp, &$list) {

		// 查询表格的条件
		$fields = array(
			array('tc_id', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields, $gp)) {
			return false;
		}

		$conds['tid'] = $this->_table['tid'];
		// 读取表格字段
		$t = new voa_d_oa_goods_tablecol();
		$list = $t->list_by_conds($conds, null, array('orderid' => 'asc'));

		// 遍历列表, 如果有 fieldalias 则替换 field
		foreach ($list as &$_v) {
			if (!empty($_v['fieldalias'])) {
				$_v['field'] = $_v['fieldalias'];
			}

			unset($_v['fieldalias']);
		}

		return true;
	}

	/**
	 * 根据 tc_id 获取表格列属性信息
	 * @param int $tc_id 表格id
	 * @param array &$tablecol 表格列属性信息
	 * @return boolean
	 */
	public function get_one($tc_id, &$tablecol) {

		$t = new voa_d_oa_goods_tablecol();
		$tablecol = $t->get($tc_id);

		// 如果有 fieldalias 则替换 field
		if (!empty($tablecol['fieldalias'])) {
			unset($tablecol['fieldalias']);
		}

		return true;
	}

	/**
	 * 更新当个表格列属性信息
	 * @param array $member 用户信息
	 * @param array $gp 数据
	 * @param int $tc_id 表格id
	 * @throws service_exception
	 * @return boolean
	 */
	public function update($member, $gp, $tc_id) {

		// 提取数据
		$tablecol = array();
		if (!$this->__parse_gp($gp, $tablecol, true)) {
			return false;
		}

		// 开始更新
		$tc_id = (int)$tc_id;
		$t = new voa_d_oa_goods_tablecol();

		try {
			$t->update($tc_id, $tablecol);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 新增表格列属性
	 * @param array $member 用户信息
	 * @param array $gp 数据
	 * @throws service_exception
	 * @return boolean
	 */
	public function add($member, $gp, &$tablecol) {

		// 提取数据
		$tablecol = array(
			'uid' => $member['m_uid'],
			'tid' => $this->_table['tid']
		);
		if (!$this->__parse_gp($gp, $tablecol)) {
			return false;
		}

		// 开始更新
		$t = new voa_d_oa_goods_tablecol();

		try {
			$tablecol = $t->insert($tablecol);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 删除表格列属性信息
	 * @param int $tc_id 产品属性ID
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete($tc_id) {

		// 删除条件
		$conds = array(
			'tc_id' => $tc_id,
			'tid' => $this->_table['tid']
		);
		$t = new voa_d_oa_goods_tablecol();

		// 读取字段信息
		if (!$col = $t->get_by_conds($conds)) {
			$this->set_errmsg(voa_errcode_oa_goods::GOODS_FIELD_IS_NOT_EXIST);
			return false;
		}

		if (1 == $col) {
			$this->set_errmsg(voa_errcode_oa_goods::GOODS_FIELD_IS_SYSTEM);
			return false;
		}

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
	private function __parse_gp($gp, &$tablecol, $update = false) {

		$fields = array(
			array('fieldname', self::VAR_STR, 'chk_fieldname', null, $update),
			array('tc_desc', self::VAR_STR, null, null, $update),
			array('ct_type', self::VAR_STR, 'chk_ct_type', null, $update),
			array('ftype', self::VAR_INT, null, null, $update),
			array('min', self::VAR_INT, null, null, $update),
			array('max', self::VAR_INT, null, null, $update),
			array('reg_exp', self::VAR_STR, 'chk_reg_exp', null, $update),
			array('initval', self::VAR_STR, null, null, $update),
			array('orderid', self::VAR_INT, null, null, $update),
			array('required', self::VAR_INT, null, null, $update),
			array('unit', self::VAR_STR, null, null, $update),
			array('isuse', self::VAR_INT, null, null, true)
		);
		// 提取数据
		if (!$this->extract_field($tablecol, $fields, $gp)) {
			return false;
		}

		// 规范取值
		$tablecol['required'] = 0 == $tablecol['required'] ? 0 : 1;
		$tablecol['isuse'] = empty($tablecol['isuse']) ? 1 : $tablecol['isuse'];
		// 取字段信息
		$columntype = $this->_columntypes[$tablecol['ct_type']];
		// 整理最大值/最小值
		if ($tablecol['min'] > $tablecol['max']) {
			$_tmp = $tablecol['min'];
			$tablecol['min'] = $tablecol['max'];
			$tablecol['max'] = $_tmp;
		}

		if (!empty($columntype['min'])) {
			$tablecol['min'] = $tablecol['min'] < $columntype['min'] ? $columntype['min'] : $tablecol['min'];
		}

		if (!empty($columntype['max'])) {
			$tablecol['max'] = $tablecol['max'] > $columntype['max'] ? $columntype['max'] : $tablecol['max'];
		}

		if (empty($tablecol['reg_exp'])) {
			$tablecol['reg_exp'] = $columntype['reg_exp'];
		}

		return true;
	}

	/**
	 * 检查正则格式
	 * @param string $regexp 是否必填
	 * @param string $err 错误信息
	 * @return boolean
	 */
	public function chk_reg_exp($regexp, $err = null) {

		if (!empty($regexp) && preg_match("/\/(.*?)\/\w*/", $subject)) {
			$this->set_errmsg(voa_errcode_oa_goods::GOODS_TABLECOL_REGEXP_ERR);
			return false;
		}

		return true;
	}

	/**
	 * 检查字段名称
	 * @param string $fname 字段名称
	 * @param string $err
	 * @return boolean
	 */
	public function chk_fieldname($fname, $err = null) {

		// 如果字段名称为空
		if (empty($fname)) {
			$this->set_errmsg(voa_errcode_oa_goods::GOODS_FIELDNAME_IS_EMPTY);
			return false;
		}

		return true;
	}

}

