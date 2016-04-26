<?php
/**
 * voa_uda_frontend_travel_material_list
 * 统一数据访问/旅游产品应用/素材列表
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_material_list extends voa_uda_frontend_travel_abstract {

	public function __construct() {

		parent::__construct();
	}

	public function execute($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('subject', self::VAR_STR, null, null, true),
			array('start_date', self::VAR_STR, null, null, true),
			array('end_date', self::VAR_STR, null, null, true),
			array('orderby', self::VAR_STR, null, null, true),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 分页信息
		$this->_get_page_option($option, $conds);

		// 排序
		$orderby = array();
		if (!empty($conds['orderby'])) {
			$orderby[$conds['orderby']] = 'DESC';
			unset($conds['orderby']);
		} else {
			$orderby['mtid'] = 'DESC';
		}

		if (!empty($conds['start_date'])) {
			$conds['updated>?'] = rstrtotime($conds['start_date']);
			unset($conds['start_date']);
		}

		if (!empty($conds['end_date'])) {
			$conds['updated<?'] = rstrtotime($conds['end_date']);
			unset($conds['end_date']);
		}

		$serv = new voa_s_oa_travel_material();
		// 读取总数
		$this->_total = $serv->count_by_conds($conds);
		// 读取
		$out = $serv->list_by_conds($conds, $option, $orderby);
		if (empty($out)) {
			$out = array();
		}

		// 格式化
		$this->_format($out, true, $serv);

		return true;
	}
}
