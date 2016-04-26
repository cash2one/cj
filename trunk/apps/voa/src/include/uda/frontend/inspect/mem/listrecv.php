<?php
/**
 * voa_uda_frontend_inspect_mem_listrecv
 * 统一数据访问/巡店/根据 uid 获取巡店用户列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_mem_listrecv extends voa_uda_frontend_inspect_mem_abstract {

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
			array('m_uid', self::VAR_INT, null, null, true),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 分页信息
		$this->_get_page_option($option, $conds);

		// 读取表格字段
		$conds['m_uid=? AND m_uid!=insm_src_uid'] = $conds['m_uid'];
		unset($conds['m_uid']);

		// 读取总数
		$this->_total = $this->_serv->count_by_conds($conds);
		$out = $this->_serv->list_by_conds($conds, $option, array('insm_updated' => 'DESC'));
		if (empty($out)) {
			$out = array();
		}

		return true;
	}

}
