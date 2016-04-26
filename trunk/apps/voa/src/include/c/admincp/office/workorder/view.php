<?php
/**
 * view.php
 * 云工作后台/移动派单/详情查看
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_workorder_view extends voa_c_admincp_office_workorder_base {

	public function execute() {

		// 工单编号
		$woid = $this->request->get('woid');
		$woid = (int)$woid;

		// 构造请求
		$request = array(
			'woid' => $woid,
			'uid' => 0,
		);

		// 设置为管理模式
		$is_admin = true;
		// 详情结果集合
		$result = array();
		$uda_view = &uda::factory('voa_uda_frontend_workorder_action_view');
		if (!$uda_view->detail($request, $result, $is_admin)) {
			$this->message('error', $uda_view->errmsg.'[Error:'.$uda_view->errcode.']');
		}

		// 当前浏览的人可执行的操作
		$uda_get = &uda::factory('voa_uda_frontend_workorder_get');
		$all_action = array();
		$uda_get->my_action(0, $result['workorder'], $result['receiver_list'], $all_action, $is_admin);
		// 整理可用的用户信息
		foreach ($result['users'] as &$_user) {
			$_user = $this->_user_info_format($_user);
		}

		/** 格式化数据以输出 */
		// 载入格式化uda
		$uda_format = &uda::factory('voa_uda_frontend_workorder_format');
		// 设置输出时间格式
		$date_format = 'Y-m-d H:i';
		// 格式化工单主表
		$uda_format->workorder($result['workorder'], $result['workorder'], $date_format);
		// 格式化工单详情表
		$uda_format->workorder_detail($result['workorder_detail'], $result['workorder_detail'], $date_format);
		// 格式化接收人列表
		$uda_format->workorder_receiver_list($result['receiver_list'], $result['receiver_list'], $date_format);
		foreach ($result['receiver_list'] as &$_wor) {
			if (!isset($result['users'][$_wor['uid']])) {
				continue;
			}
			$_wor['user_info'] = $result['users'][$_wor['uid']];
		}
		// 格式化工单执行人
		$uda_format->workorder_receiver($result['operator'], $result['operator'], $date_format);
		// 格式化日志
		$uda_format->workorder_log_list($result['log_list'], $result['log_list'], $date_format);
		foreach ($result['log_list'] as &$_log) {
			if (!isset($result['users'][$_log['uid']])) {
				continue;
			}
			$_log['user_info'] = $result['users'][$_log['uid']];
		}
		// End

		/** 整理具体输出 */
		// 工单主要信息
		$workorder = $result['workorder'];
		// 派单人信息
		$workorder['sender_info'] = $result['users'][$workorder['uid']];
		// 执行人信息
		$workorder['operator_info'] = isset($result['users'][$workorder['operator_uid']])
			? $result['users'][$workorder['operator_uid']] : $this->_user_info_format(array());
		// 工单附件
		if (isset($result['attachment_list'][voa_d_oa_workorder_attachment::ROLE_SENDER])) {
			$workorder['attachment_list'] = $result['attachment_list'][voa_d_oa_workorder_attachment::ROLE_SENDER];
			$workorder['attachment_list'] = $this->_attachment_format_list($workorder['attachment_list']);
		} else {
			$workorder['attachment_list'] = array();
		}
		// 执行结果
		$operation_result = $result['workorder_detail'];
		// 执行结果附带的附件
		$operation_result['attachment_list'] = array();
		if (isset($result['attachment_list'][voa_d_oa_workorder_attachment::ROLE_OPERATOR])) {
			$operation_result['attachment_list'] = $result['attachment_list'][voa_d_oa_workorder_attachment::ROLE_OPERATOR];
			$operation_result['attachment_list'] = $this->_attachment_format_list($operation_result['attachment_list']);
		}
		// 输出结果
		$this->_result = array(
			// 工单详情
			'workorder' => $workorder,
			// 执行结果
			'operation_result' => $operation_result,
			// 接收人列表
			'receiver_list' => $result['receiver_list'],
			// 操作日志
			'log_list' => $result['log_list'],
			// 可执行动作
			'allow_action' => $all_action
		);

		// 注入模板变量
		$this->view->set('workorder', $workorder);
		$this->view->set('receiver_list', $result['receiver_list']);
		$this->view->set('operation_result', $operation_result);
		$this->view->set('log_list', $result['log_list']);
		$this->view->set('action', $all_action);
		$this->view->set('receiver_count', count($result['receiver_list']));

		// 模板输出
		$this->output('office/workorder/view');
	}

	/**
	 * 针对工单的个人信息输出格式化
	 * @param array $user
	 * @return array
	 */
	protected function _user_info_format($user) {

		// 避免未定义的意外
		if (empty($user) || !isset($user['m_uid'])) {
			return array(
				'uid' => 0,
				'mobilephone' => '',
				'realname' => '',
				'face' => '',
			);
		}

		return array(
			'uid' => $user['m_uid'],
			'mobilephone' => $user['m_mobilephone'],
			'realname' => $user['_realname'],
			'face' => $user['_face'],
		);
	}

	/**
	 * 格式化附件列表
	 * @param array $list
	 * @return array
	 */
	protected function _attachment_format_list($list) {
		$format = array();
		foreach ($list as $_at) {
			$format[] = $this->_attachment_format($_at);
		}

		return $format;
	}

	/**
	 * 格式化单条附件信息
	 * @param array $attachment
	 * @return array
	 */
	protected function _attachment_format($attachment) {

		return array(
			'at_id' => $attachment[0]['at_id'],
			'src' => voa_h_attach::attachment_url($attachment[0]['at_id'], 120),
			'url' => voa_h_attach::attachment_url($attachment[0]['at_id']),
		);
	}

}
