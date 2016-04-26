<?php
/**
 * voa_uda_frontend_travel_diyindex_list
 * 统一数据访问/旅游产品应用/自定义首页列表
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_diyindex_list extends voa_uda_frontend_travel_abstract {

	public function __construct() {

		parent::__construct();
	}

	public function execute($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
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
			$orderby['tiid'] = 'DESC';
		}

		$serv = new voa_s_oa_travel_diyindex();
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
