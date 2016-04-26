<?php
/**
 * 巡店选项操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_option_update extends voa_uda_frontend_inspect_option_abstract {

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
			array('inso_optvalue', self::VAR_STR, null, null, false),
			array('inso_id', self::VAR_INT, null, null, true),
			array('insi_id', self::VAR_INT, null, null, false),
			array('inso_state', self::VAR_INT, null, null, true)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		if (empty($data['inso_id'])) {
			$out = $this->_serv->insert($data);
		} else {
			$inso_id = (int)$data['inso_id'];
			$out = $data;
			unset($data['inso_id']);
			$this->_serv->update($inso_id, $data);
		}

		return true;
	}

}
