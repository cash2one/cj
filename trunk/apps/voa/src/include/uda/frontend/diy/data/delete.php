<?php
/**
 * voa_uda_frontend_diy_data_delete
 * 统一数据访问/自定义数据表格数据/删除数据
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_data_delete extends voa_uda_frontend_diy_data_abstract {

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
			array('dr_id', self::VAR_ARR, null, null, true)
		);
		$conds = array('tid' => self::$_s_table['tid']);
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 合并外部特殊条件
		$conds = array_merge($conds, $this->_special_conds);

		// 读取数据
		$data = array();
		// 如果数据不存在, 则报成功
		if (!$data = $this->_serv_data->list_by_column_conds(self::$_s_tablecols, $conds)) {
			return true;
		}

		// 取出 dr_id
		$dr_ids = array_keys($data);

		// 删除行数据
		$serv_row = &service::factory('voa_s_oa_diy_row');
		$serv_row->delete($dr_ids);

		// 删除真实数据
		$this->_serv_data->delete_by_conds(array('dr_id' => $dr_ids));

		return true;
	}

}
