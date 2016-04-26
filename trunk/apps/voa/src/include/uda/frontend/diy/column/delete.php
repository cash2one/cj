<?php
/**
 * voa_uda_frontend_diy_column_delete
 * 统一数据访问/自定义数据表格属性/删除属性
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_column_delete extends voa_uda_frontend_diy_column_abstract {
	// 删除系统字段标识, false: 不允许删除系统字段; true: 允许删除
	protected $_permit_del_sys = false;

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
			array('tc_id', self::VAR_ARR, null, null, true)
		);
		$conds = array('tid' => self::$_s_table['tid']);
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 读取字段信息
		$cols = array();
		if (!$cols = $this->_serv_tablecol->list_by_conds($conds)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::FIELD_IS_NOT_EXIST);
			return false;
		}

		// 如果是系统字段
		$tc_ids = array();
		foreach ($cols as $_col) {
			if (!$this->_permit_del_sys && voa_d_oa_diy_tablecol::COLTYPE_SYS == $_col['coltype']) {
				return false;
			}

			$tc_ids[] = $_col['tc_id'];
		}

		// 如果没有需要删除的字段
		if (empty($tc_ids)) {
			return true;
		}

		// 删除字段
		$this->_serv_tablecol->delete_by_conds(array('tc_id' => $tc_ids));

		return true;
	}

	/**
	 * 设置删除标识
	 * @param boolean $flag 标识
	 */
	public function set_permit_del_sys($flag = false) {

		$this->_permit_del_sys = $flag;
	}

}
