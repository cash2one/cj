<?php
/**
 * voa_uda_frontend_diy_data_add
 * 统一数据访问/自定义数据表格数据/新增数据
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_data_add extends voa_uda_frontend_diy_data_abstract {

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
		// 提取数据
		$data = array(
			'uid' => self::$_s_mem['m_uid'],
			'tid' => self::$_s_table['tid']
		);
		if (!$this->_parse_gp($data, self::$_s_tablecols)) {
			return false;
		}

		// 行信息入库
		$serv_row = &service::factory('voa_s_oa_diy_row');
		$row = array(
			'tid' => self::$_s_table['tid'],
			'uid' => self::$_s_mem['m_uid']
		);
		$row = $serv_row->insert($row);

		// 开始入库
		$data['dr_id'] = $row['dr_id'];
		$out = $this->_serv_data->insert_column_data(self::$_s_tablecols, $data);

		// 数据状态
		$out['created'] = $row['created'];
		$out['status'] = $row['status'];
		$out['updated'] = $row['updated'];

		// 数据转换
		$at_ids = array();
		$this->_serv_data->translate_field($out, $at_ids, self::$_s_tablecols);

		return true;
	}

}
