<?php
/**
 * voa_uda_frontend_inspect_setting_list
 * 统一数据访问/巡店配置/获取配置列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_setting_list extends voa_uda_frontend_inspect_setting_abstract {

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
			array('is_key', self::VAR_ARR, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 读取表格字段
		$out = $this->_serv->list_by_conds($conds, null);

		return true;
	}

}
