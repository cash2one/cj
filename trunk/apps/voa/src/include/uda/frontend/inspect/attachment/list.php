<?php
/**
 * voa_uda_frontend_inspect_attachment_list
 * 统一数据访问/巡店附件/获取列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_attachment_list extends voa_uda_frontend_inspect_attachment_abstract {

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
			array('ins_id', self::VAR_ARR, null, null, true),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 分页信息
		$option = null;
		$this->_get_page_option($option, $conds);

		// 读取表格字段
		$out = $this->_serv->list_by_conds($conds, $option);
		if (empty($out)) {
			$out = array();
		}

		return true;
	}

}
