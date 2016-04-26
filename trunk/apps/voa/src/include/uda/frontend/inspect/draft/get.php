<?php
/**
 * voa_uda_frontend_inspect_draft_get
 * 统一数据访问/巡店配置/获取草稿
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_draft_get extends voa_uda_frontend_inspect_draft_abstract {

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
		// 入库的数据
		$fields = array(
			array('insd_id', self::VAR_INT, null, null, true),
			array('m_openid', self::VAR_STR, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 如果条件为空
		if (empty($conds)) {
			return false;
		}

		// 读取表格字段
		$out = $this->_serv->get_by_conds($conds);

		return true;
	}

}
