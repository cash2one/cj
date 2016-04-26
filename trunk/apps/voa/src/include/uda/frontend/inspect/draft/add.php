<?php
/**
 * voa_uda_frontend_inspect_draft_add
 * 统一数据访问/巡店配置/新增草稿
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_draft_add extends voa_uda_frontend_inspect_draft_abstract {

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
			array('insd_message', self::VAR_STR, null, null, true),
			array('insd_a_uid', self::VAR_STR, null, null, false),
			array('insd_cc_uid', self::VAR_STR, null, null, true),
			array('m_openid', self::VAR_STR, null, null, false)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		// 读取表格字段
		$this->_serv->insert($data);

		return true;
	}

}
