<?php
/**
 * voa_uda_frontend_diy_data_get
 * 统一数据访问/自定义数据表格数据/获取数据
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_data_get extends voa_uda_frontend_diy_data_abstract {

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
		// 查询条件
		$conds = array(
			'dr_id' => (int)$this->get('dr_id'),
			'tid' => self::$_s_table['tid']
		);
		// 合并外部特殊条件
		$conds = array_merge($conds, $this->_special_conds);
		// 如果数据不存在
		if (!$out = $this->_serv_data->get_by_column_conds(self::$_s_tablecols, $conds)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::DATA_IS_NOT_EXIST);
			return false;
		}

		// 数据转换
		$at_ids = array();
		$this->_serv_data->translate_field($out, $at_ids, self::$_s_tablecols);

		return true;
	}

}
