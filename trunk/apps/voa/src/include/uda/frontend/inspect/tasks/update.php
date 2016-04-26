<?php
/**
 * 巡店任务相关的编辑操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_tasks_update extends voa_uda_frontend_inspect_tasks_abstract {

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
			array('it_id', self::VAR_INT, null, null, true),
			array('it_title', self::VAR_STR, null, null, true),
			array('it_description', self::VAR_STR, null, null, true),
			array('it_submit_uid', self::VAR_INT, null, null, true),
			array('it_assign_uid', self::VAR_INT, null, null, true),
			array('it_csp_id_list', self::VAR_STR, null, null, true),
			array('it_finished_total', self::VAR_INT, null, null, true),
			array('it_start_date', self::VAR_INT, null, null, true),
			array('it_end_date', self::VAR_INT, null, null, true),
			array('it_repeat_frequency', self::VAR_INT, null, null, true),
			array('it_execution_status', self::VAR_INT, null, null, true),
			array('it_parent_id', self::VAR_INT, null, null, true),
			array('it_last_execution_time', self::VAR_INT, null, null, true),
			array('it_alert_time', self::VAR_STR, null, null, true)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		// 如果 insd_id 为空
		if (empty($data['it_id'])) {
			return false;
		}

		$it_id = (int)$data['it_id'];
		unset($data['it_id']);

		// 如果参数为空
		if (empty($data) || empty($it_id)) {
			return false;
		}

		// 读取表格字段
		$this->_serv->update($it_id, $data);

		return true;
	}
}
