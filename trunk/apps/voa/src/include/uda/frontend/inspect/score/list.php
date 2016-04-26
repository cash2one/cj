<?php
/**
 * voa_uda_frontend_inspect_score_list
 * 统一数据访问/巡店/获取打分列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_score_list extends voa_uda_frontend_inspect_score_abstract {

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
			array('ins_id', self::VAR_ARR, null, null, true),
			array('insi_id', self::VAR_ARR, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 读取表格字段
		$list = $this->_serv->list_by_conds($conds, null);
		if (empty($list)) {
			$list = array();
		}

		// 转换键值
		foreach ($list as $_v) {
			$this->_format($_v);
			$out[$_v['insi_id']] = $_v;
		}

		return true;
	}

}
