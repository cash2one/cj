<?php
/**
 * voa_uda_frontend_customer_tablecol
 * 统一数据访问/客户应用/表格列属性操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_customer_tablecol extends voa_uda_frontend_customer_abstract {

	/**
	 * 构造方法
	 * @param array $ptname 插件和表格名称
	 * + string plugin 插件名称
	 * + string table 表格名称
	 * + string tablecols 表格列属性配置
	 */
	public function __construct($ptname) {

		parent::__construct($ptname);
		if (!empty($ptname['tablecols'])) {
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
		$t = new voa_d_oa_customer_tablecol();
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

		$t = new voa_d_oa_customer_tablecol();
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
		if (!$this->__parse_gp($gp, $tablecol)) {
			return false;
		}

		// 开始更新
		$tc_id = (int)$tc_id;
		$t = new voa_d_oa_customer_tablecol();
		$curcol = $t->get($tc_id);

		try {
			if (voa_d_oa_customer_tablecol::COLTYPE_SYS == $curcol['coltype']) {
				$tablecol = array('fieldname' => $tablecol['fieldname']);
			}

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
		$t = new voa_d_oa_customer_tablecol();

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
	 * @param mixed $tc_id 属性id
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete($tc_id) {

		// 数据操作类
		$t = new voa_d_oa_customer_tablecol();

		// 读取字段信息
		if (!$col = $t->get($tc_id)) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_FIELD_IS_NOT_EXIST);
			return false;
		}

		if (1 == $col) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_FIELD_IS_SYSTEM);
			return false;
		}

		try {
			$t->delete($tc_id);
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
	private function __parse_gp($gp, &$tablecol) {

		$fields = array(
			array('fieldname', self::VAR_STR, 'chk_fieldname', null),
			array('tc_desc', self::VAR_STR, null, null),
			array('ct_type', self::VAR_STR, 'chk_ct_type', null),
			array('ftype', self::VAR_INT, null, null),
			array('min', self::VAR_INT, null, null),
			array('max', self::VAR_INT, null, null),
			array('reg_exp', self::VAR_STR, 'chk_reg_exp', null),
			array('initval', self::VAR_STR, null, null),
			array('orderid', self::VAR_INT, null, null),
			array('required', self::VAR_INT, null, null)
		);
		// 提取数据
		if (!$this->extract_field($tablecol, $fields, $gp)) {
			return false;
		}

		// 规范取值
		$tablecol['required'] = 0 == $tablecol['required'] ? 0 : 1;
		// 取字段信息
		$columntype = $this->_columntypes[$tablecol['ct_type']];
		// 整理最大值/最小值
		if ($tablecol['min'] > $tablecol['max']) {
			$_tmp = $tablecol['min'];
			$tablecol['min'] = $tablecol['max'];
			$tablecol['max'] = $_tmp;
		}

		$tablecol['min'] = $tablecol['min'] < $columntype['min'] ? $columntype['min'] : $tablecol['min'];
		$tablecol['max'] = $tablecol['max'] > $columntype['max'] ? $columntype['max'] : $tablecol['max'];

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
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_TABLECOL_REGEXP_ERR);
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
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_FIELDNAME_IS_EMPTY);
			return false;
		}

		return true;
	}

}

