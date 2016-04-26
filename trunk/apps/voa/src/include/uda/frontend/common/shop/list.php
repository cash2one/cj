<?php
/**
 * voa_uda_frontend_common_shop_list
 * 统一数据访问/门店信息/获取列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_common_shop_list extends voa_uda_frontend_common_shop_abstract {

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
			array('m_uid', self::VAR_ARR, null, null, true),
			array('csp_name', self::VAR_STR, null, null, true),
			array('csp_id', self::VAR_ARR, null, null, true),
			array('cr_id', self::VAR_ARR, null, null, true),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 分页信息
		$this->_get_page_option($option, $conds);

		// 按名字搜索
		if (!empty($conds['csp_name'])) {
			$conds["csp_name LIKE ?"] = "%".$conds['csp_name']."%";
			unset($conds['csp_name']);
		}

		$this->_total = $this->_serv->count_by_conds($conds);
		// 读取表格字段
		$out = $this->_serv->list_by_conds($conds, $option, array('csp_id' => 'DESC'));
		if (empty($out)) {
			$out = array();
		}

		return true;
	}

}
