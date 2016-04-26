<?php
/**
 * voa_uda_frontend_interface_list
 * 统一数据访问/测试应用/接口列表
 *
 * gaosong
 * $Id$
 */

class voa_uda_frontend_interface_list extends voa_uda_frontend_interface_abstract {

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
			array('name', self::VAR_STR, null, null, true),
			array('cp_pluginid', self::VAR_INT, null, null, true),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		if (!empty($conds['name'])) {
			$conds['name like ?'] = "%".$conds['name']."%";
		}

		if (isset($conds['name'])) {
			unset($conds['name']);
		}
		if (empty($conds['cp_pluginid'])) {
			unset($conds['cp_pluginid']);
		}

		$option = array();
		// 分页信息
		$this->_get_page_option($option, $conds);

		$t = new voa_d_oa_interface();

		$this->_total = $t->count_by_conds($conds);
		// 读取表格字段
		$out = $t->list_by_conds($conds, $option, array('created' => 'DESC'));
		if (empty($out)) {
			$out = array();
		}

		// 判断是否需要过滤
		$this->_fmt && $this->_format($out, true);

		return true;
	}

}
