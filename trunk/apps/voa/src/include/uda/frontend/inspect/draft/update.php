<?php
/**
 * voa_uda_frontend_inspect_draft_update
 * 统一数据访问/巡店配置/更新草稿
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_draft_update extends voa_uda_frontend_inspect_draft_abstract {

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
			array('insd_a_uid', self::VAR_STR, null, null, true),
			array('insd_cc_uid', self::VAR_STR, null, null, true),
			array('insd_id', self::VAR_STR, null, null, true),
			array('m_openid', self::VAR_STR, null, null, true)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		// 如果 insd_id 为空
		if (empty($data['insd_id'])) {
			return false;
		}

		$insd_id = (int)$data['insd_id'];
		unset($data['insd_id']);

		// 如果参数为空
		if (empty($data) || empty($insd_id)) {
			return false;
		}

		// 读取表格字段
		$this->_serv->update($insd_id, $data);

		return true;
	}

}
