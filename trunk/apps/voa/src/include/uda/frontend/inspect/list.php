<?php
/**
 * voa_uda_frontend_inspect_list
 * 统一数据访问/巡店打分项/获取列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_list extends voa_uda_frontend_inspect_abstract {

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
			array('it_id', self::VAR_ARR, null, null, true),
			array('ins_id', self::VAR_ARR, null, null, true),
			array('ins_type', self::VAR_ARR, null, null, true),
			array('csp_id', self::VAR_ARR, null, null, true),
			array('start_date', self::VAR_INT, null, null, true),
			array('end_date', self::VAR_INT, null, null, true),
			array('m_uid', self::VAR_ARR, null, null, true),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 分页信息
		$this->_get_page_option($option, $conds);

		if (!empty($conds['ins_type'])) {
			$conds['ins_type IN (?)'] = $conds['ins_type'];
			unset($conds['ins_type']);
		}

		if (!empty($conds['start_date'])) {
			$conds['ins_updated>?'] = $conds['start_date'];
		}

		if (!empty($conds['end_date'])) {
			$conds['ins_updated<?'] = $conds['end_date'];
		}

		if (isset($conds['start_date'])) {
			unset($conds['start_date']);
		}

		if (isset($conds['end_date'])) {
			unset($conds['end_date']);
		}

		// 读取总数
		$this->_total = $this->_serv->count_by_conds($conds);
		// 读取
		$out = $this->_serv->list_by_conds($conds, $option, array('ins_updated' => 'DESC'));
		if (empty($out)) {
			$out = array();
		}

		// 判断是否需要过滤
		$this->_format($out, true);

		return true;
	}

}
