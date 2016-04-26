<?php
/**
 * 执行巡店任务
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_tasks_run extends voa_uda_frontend_inspect_tasks_abstract {

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
		$task = $this->get('task');
		if (empty($task)) {
			return true;
		}

		// 切分任务中的店铺id
		$csp_ids = explode(',', $task['it_csp_id_list']);
		if (empty($csp_ids)) {
			return true;
		}

		// 读取用户信息
		$user = voa_h_user::get($task['it_assign_uid']);

		// 组织sql
		$sqls = array();
		foreach ($csp_ids as $_id) {
			$sqls[] = array(
				'it_id' => $task['it_id'],
				'sponsor_uid' => $task['it_submit_uid'],
				'm_uid' => $task['it_assign_uid'],
				'm_username' => $user['m_username'],
				'csp_id' => $_id
			);
		}

		$serv_ins = new voa_s_oa_inspect();
		$serv_ins->insert_multi($sqls);

		return true;
	}

}
