<?php
/**
 * voa_uda_frontend_diy_data_update
 * 统一数据访问/自定义数据表格数据/更新数据
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_data_update extends voa_uda_frontend_diy_data_abstract {

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
		// 取数据
		$conds = array(
			'dr_id' => (int)$this->get('dr_id'),
			'tid' => self::$_s_table['tid']
		);
		// 合并外部特殊条件
		$conds = array_merge($conds, $this->_special_conds);
		// 提取数据
		$data = array();
		if (!$this->_parse_gp($data, self::$_s_tablecols, true)) {
			return false;
		}

		// 按条件读取
		$list = array();
		// 如果数据不存在, 则报成功
		if (!$list = $this->_serv_data->list_by_column_conds(self::$_s_tablecols, $conds)) {
			return true;
		}

		// 获取 dr_id
		$dr_ids = array_keys($list);

		// 更新数据
		$this->_serv_data->update_by_column_conds(self::$_s_tablecols, array('dr_id' => $dr_ids), $data);

		// 更新行数据
		$serv_row = &service::factory('voa_s_oa_diy_row');
		$serv_row->update($dr_ids);

		return true;
	}

}
