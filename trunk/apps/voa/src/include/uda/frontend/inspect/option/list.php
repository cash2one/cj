<?php
/**
 * voa_uda_frontend_inspect_option_list
 * 统一数据访问/巡店选项/获取列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_option_list extends voa_uda_frontend_inspect_option_abstract {

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
			array('insi_id', self::VAR_ARR, null, null, true),
			array('inso_id', self::VAR_ARR, null, null, true),
			array('inso_state', self::VAR_ARR, null, null, true),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		$this->_get_page_option($option, $conds);

		$this->_total = $this->_serv->count_by_conds($conds);
		// 读取表格字段
		$out = $this->_serv->list_by_conds($conds, $option);
		if (empty($out)) {
			$out = array();
		}

		$this->_fmt && $this->_format($out, true);

		return true;
	}

}
