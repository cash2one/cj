<?php
/**
 * voa_uda_frontend_travel_turnover_list
 * 统一数据访问/旅游产品应用/销售业绩与提成列表
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_turnover_list extends voa_uda_frontend_travel_abstract {

	public function __construct() {

		parent::__construct();
	}

	public function execute($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('username', self::VAR_STR, null, null, true),
			array('cd_id', self::VAR_INT, null, null, true),
			array('start_date', self::VAR_STR, null, null, true),
			array('end_date', self::VAR_STR, null, null, true),
			array('saleuid', self::VAR_ARR, null, null, true),
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
		}

		$serv = new voa_d_oa_travel_ordergoods();
		// 读取总数
		$this->_total = $serv->count_turnover($conds);
		// 读取
		$out = $serv->list_turnover($conds, $option, $orderby);
		if (empty($out)) {
			$out = array();
		}

		return true;
	}

	/**
	 * 获取销售业绩与提成列表
	 * @param array $gp 请求数据
	 * @param mixed $page_option 分页参数
	 * @param array &$list 销售业绩与提成列表
	 * @return boolean
	 */
	public function list_all($gp, $page_option, &$list, &$total){

		// 查询的条件
		$fields = array(
				array('username', self::VAR_STR, null, null, true),
				array('cd_id', self::VAR_INT, null, null, true),
				array('start_date', self::VAR_STR, null, null, true),
				array('end_date', self::VAR_STR, null, null, true),
				array('saleuid', self::VAR_ARR, null, null, true),
				array('orderby', self::VAR_STR, null, null, true),
				array('page', self::VAR_INT, null, null, true),
				array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields, $gp)) {
			return false;
		}
		// 读取数据
		$serv = new voa_d_oa_travel_ordergoods();
		if (!$list = $serv->list_by_conds($conds, $page_option, array('updated' => 'desc'))) {
			return true;
		}
		// 取总数
		$serv->reset();
		$total = $serv->count_by_conds($conds);
		return true;
	}
}
